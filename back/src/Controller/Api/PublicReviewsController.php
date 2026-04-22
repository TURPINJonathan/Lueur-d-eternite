<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Review;
use App\Entity\SiteSettings;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;
use Twig\Error\Error as TwigError;

#[Route('/api/public/reviews', name: 'api_public_reviews')]
final class PublicReviewsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ReviewRepository $reviewRepository,
        private readonly ValidatorInterface $validator,
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {}

    #[Route('', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $reviews = $this->reviewRepository->findApprovedOrderedByNewest();

        $payload = array_map(
            static fn (Review $review): array => [
                'id'         => $review->getId(),
                'author'     => $review->getAuthor(),
                'title'      => $review->getTitle(),
                'comment'    => $review->getComment(),
                'rate'       => $review->getRate(),
                'created_at' => $review->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            ],
            $reviews,
        );

        return new JsonResponse($payload);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        /** @var mixed $payload */
        $payload = json_decode($request->getContent(), true);
        if (!\is_array($payload)) {
            return new JsonResponse(['message' => 'Payload JSON invalide.'], 400);
        }

        $review = (new Review())
            ->setAuthor(trim((string) ($payload['author'] ?? '')))
            ->setEmail(trim((string) ($payload['email'] ?? '')))
            ->setTitle($this->nullableTrim($payload['title'] ?? null))
            ->setComment(trim((string) ($payload['comment'] ?? '')))
            ->setRate((int) ($payload['rate'] ?? 0))
            ->setCreatedAt(new \DateTimeImmutable());

        $errors = $this->validator->validate($review);
        if (\count($errors) > 0) {
            $formatted = [];
            foreach ($errors as $error) {
                $formatted[$error->getPropertyPath()] = $error->getMessage();
            }

            return new JsonResponse([
                'message' => 'Veuillez corriger les champs en erreur.',
                'errors'  => $formatted,
            ], 422);
        }

        $this->entityManager->persist($review);
        $this->entityManager->flush();

        try {
            $this->sendReviewEmails($review);
        } catch (TwigError) {
            return new JsonResponse([
                'message' => 'Avis enregistré mais erreur de rendu des templates email.',
            ], 500);
        } catch (TransportExceptionInterface) {
            return new JsonResponse([
                'message' => "Avis enregistré mais l'envoi des emails a échoué.",
            ], 500);
        }

        return new JsonResponse([
            'message' => 'Votre avis a bien été envoyé et sera publié après validation.',
        ], 201);
    }

    private function nullableTrim(mixed $value): ?string
    {
        $value = trim((string) $value);

        return '' === $value ? null : $value;
    }

    /**
     * @throws TwigError
     * @throws TransportExceptionInterface
     */
    private function sendReviewEmails(Review $review): void
    {
        /** @var SiteSettings|null $siteSettings */
        $siteSettings = $this->entityManager->getRepository(SiteSettings::class)->findOneBy([]);
        $defaultRecipientEmail = 'contact@lueur-eternite.fr';
        $configuredRecipient = $siteSettings?->getContactFormRecipientEmail()
            ?: $siteSettings?->getContactEmail()
            ?: $defaultRecipientEmail;
        $recipientEmail = filter_var($configuredRecipient, \FILTER_VALIDATE_EMAIL) ? $configuredRecipient : $defaultRecipientEmail;
        $siteName = trim((string) ($siteSettings?->getContactFormSenderName() ?? '')) ?: "Lueur d'Éternité";

        $context = [
            'siteName'       => $siteName,
            'recipientEmail' => $recipientEmail,
            'author'         => $review->getAuthor(),
            'title'          => $review->getTitle(),
            'senderEmail'    => $review->getEmail(),
            'comment'        => $review->getComment(),
            'rate'           => $review->getRate(),
            'createdAt'      => $review->getCreatedAt(),
        ];

        $adminHtml = $this->renderTemplate(
            $siteSettings?->getReviewFormTemplateAdmin() ?? '',
            'emails/review_posted_pending_admin.html.twig',
            $context,
        );

        $adminEmail = (new Email())
            ->from(new Address($recipientEmail, $siteName))
            ->to(new Address($recipientEmail, $siteName))
            ->replyTo(new Address((string) $review->getEmail(), (string) $review->getAuthor()))
            ->subject('Nouvel avis en attente de validation')
            ->html($adminHtml);

        $this->mailer->send($adminEmail);

        if (($siteSettings?->isReviewFormSendConfirmation() ?? true) && null !== $review->getEmail()) {
            $userHtml = $this->renderTemplate(
                $siteSettings?->getReviewFormTemplateUser() ?? '',
                'emails/review_posted_pending_user_confirmation.html.twig',
                $context,
            );

            $confirmationEmail = (new Email())
                ->from(new Address($recipientEmail, $siteName))
                ->to(new Address($review->getEmail(), (string) $review->getAuthor()))
                ->replyTo(new Address($recipientEmail, $siteName))
                ->subject('Votre avis a bien été reçu')
                ->html($userHtml);

            $this->mailer->send($confirmationEmail);
        }
    }

    /**
     * @param array<string, mixed> $context
     */
    private function renderTemplate(string $dbTemplate, string $fallbackTemplateFile, array $context): string
    {
        $templateSource = trim($dbTemplate);
        if ('' === $templateSource) {
            $path = $this->projectDir . '/templates/' . ltrim($fallbackTemplateFile, '/');
            $loaded = @file_get_contents($path);
            $templateSource = \is_string($loaded) ? $loaded : '';
        }

        return $this->twig->createTemplate($templateSource)->render($context);
    }
}
