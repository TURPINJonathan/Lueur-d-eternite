<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\SiteSettings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;
use Twig\Error\Error as TwigError;

#[Route('/api/public/contact', name: 'api_public_contact')]
final class PublicContactController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        #[Autowire(service: 'limiter.contact_form')]
        private readonly RateLimiterFactory $contactFormLimiter,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {}

    #[Route('', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $clientIp = (string) ($request->getClientIp() ?? 'unknown');
        $rateLimit = $this->contactFormLimiter->create($clientIp)->consume(1);
        if (!$rateLimit->isAccepted()) {
            $retryAfter = $rateLimit->getRetryAfter();
            $retryAfterSeconds = $retryAfter ? max(1, $retryAfter->getTimestamp() - time()) : 60;

            return new JsonResponse([
                'message' => 'Trop de demandes envoyées. Merci de réessayer dans quelques minutes.',
            ], 429, [
                'Retry-After'           => (string) $retryAfterSeconds,
                'X-RateLimit-Remaining' => (string) $rateLimit->getRemainingTokens(),
            ]);
        }

        /** @var mixed $payload */
        $payload = json_decode($request->getContent(), true);
        if (!\is_array($payload)) {
            return new JsonResponse(['message' => 'Payload JSON invalide.'], 400);
        }

        $fullName = trim((string) ($payload['fullName'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));
        $phone = trim((string) ($payload['phone'] ?? ''));
        $message = trim((string) ($payload['message'] ?? ''));
        $website = trim((string) ($payload['website'] ?? '')); // honeypot anti-bot

        $errors = [];
        if (mb_strlen($fullName) < 2 || mb_strlen($fullName) > 120) {
            $errors['fullName'] = 'Le nom complet doit contenir entre 2 et 120 caractères.';
        }
        if (!filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Adresse email invalide.';
        }
        $phoneDigits = preg_replace('/\D+/', '', $phone) ?? '';
        if (\strlen($phoneDigits) < 10 || \strlen($phoneDigits) > 15) {
            $errors['phone'] = 'Numéro de téléphone invalide.';
        }
        if (mb_strlen($message) < 10 || mb_strlen($message) > 4000) {
            $errors['message'] = 'Le message doit contenir entre 10 et 4000 caractères.';
        }
        if ('' !== $website) {
            return new JsonResponse(['message' => 'Demande rejetée.'], 400);
        }

        if ([] !== $errors) {
            return new JsonResponse([
                'message' => 'Veuillez corriger les champs en erreur.',
                'errors'  => $errors,
            ], 422);
        }

        /** @var SiteSettings|null $siteSettings */
        $siteSettings = $this->entityManager->getRepository(SiteSettings::class)->findOneBy([]);
        $defaultRecipientEmail = 'contact@lueur-eternite.fr';
        $configuredRecipient = $siteSettings?->getContactFormRecipientEmail()
            ?: $siteSettings?->getContactEmail()
            ?: $defaultRecipientEmail;
        $recipientEmail = filter_var($configuredRecipient, \FILTER_VALIDATE_EMAIL) ? $configuredRecipient : $defaultRecipientEmail;

        $defaultSiteName = "Lueur d'Éternité";
        $siteName = trim((string) ($siteSettings?->getContactFormSenderName() ?? '')) ?: $defaultSiteName;
        $sendConfirmation = $siteSettings?->isContactFormSendConfirmation() ?? true;

        try {
            $adminHtml = $this->renderTemplate(
                $siteSettings?->getContactFormTemplateAdmin() ?? '',
                'emails/contact_request_admin.html.twig',
                [
                    'siteName'       => $siteName,
                    'recipientEmail' => $recipientEmail,
                    'fullName'       => $fullName,
                    'senderEmail'    => $email,
                    'phone'          => $phone,
                    'message'        => $message,
                ],
            );

            $adminEmail = (new Email())
                ->from(new Address($recipientEmail, $siteName))
                ->to(new Address($recipientEmail, $siteName))
                ->replyTo(new Address($email, $fullName))
                ->subject('Nouvelle demande de contact - ' . $fullName)
                ->html($adminHtml);

            $this->mailer->send($adminEmail);
            if ($sendConfirmation) {
                $userHtml = $this->renderTemplate(
                    $siteSettings?->getContactFormTemplateUser() ?? '',
                    'emails/contact_request_user_confirmation.html.twig',
                    [
                        'siteName'       => $siteName,
                        'recipientEmail' => $recipientEmail,
                        'fullName'       => $fullName,
                        'senderEmail'    => $email,
                        'phone'          => $phone,
                        'message'        => $message,
                    ],
                );

                $confirmationEmail = (new Email())
                    ->from(new Address($recipientEmail, $siteName))
                    ->to(new Address($email, $fullName))
                    ->replyTo(new Address($recipientEmail, $siteName))
                    ->subject('Nous avons bien reçu votre demande')
                    ->html($userHtml);

                $this->mailer->send($confirmationEmail);
            }
        } catch (TwigError) {
            return new JsonResponse([
                'message' => 'Erreur de rendu des templates email.',
            ], 500);
        } catch (TransportExceptionInterface) {
            return new JsonResponse([
                'message' => "L'envoi des emails a échoué.",
            ], 500);
        }

        return new JsonResponse([
            'message' => 'Votre demande a bien été envoyée.',
        ], 201);
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
