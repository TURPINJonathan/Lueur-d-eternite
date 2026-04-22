<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\SiteSettings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Twig\Environment;
use Twig\Error\Error as TwigError;

#[Route('/backoffice/parametres-site/email-template-preview', name: 'back_office_parametres_site_email_template_preview')]
#[IsGranted('ROLE_ADMIN')]
final class AdminEmailTemplatePreviewController extends AbstractController
{
    public function __construct(
        private readonly Environment $twig,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    #[Route('', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        /** @var mixed $payload */
        $payload = json_decode($request->getContent(), true);
        if (!\is_array($payload)) {
            return new JsonResponse(['message' => 'Payload JSON invalide.'], 400);
        }

        $kind = (string) ($payload['kind'] ?? 'admin');
        $template = (string) ($payload['template'] ?? '');

        /** @var SiteSettings|null $settings */
        $settings = $this->entityManager->getRepository(SiteSettings::class)->findOneBy([]);
        $siteName = trim((string) ($settings?->getContactFormSenderName() ?? "Lueur d'Éternité"));
        $recipientEmail = trim((string) ($settings?->getContactFormRecipientEmail() ?? $settings?->getContactEmail() ?? 'contact@lueur-eternite.fr'));

        if ('' === trim($template) && $settings instanceof SiteSettings) {
            $template = match ($kind) {
                'user'         => $settings->getContactFormTemplateUser(),
                'review_admin' => $settings->getReviewFormTemplateAdmin(),
                'review_user'  => $settings->getReviewFormTemplateUser(),
                default        => $settings->getContactFormTemplateAdmin(),
            };
        }

        if ('' === trim($template)) {
            return new JsonResponse(['message' => 'Template vide.'], 422);
        }

        try {
            $html = $this->twig->createTemplate($template)->render([
                'siteName'       => $siteName,
                'recipientEmail' => $recipientEmail,
                'fullName'       => 'Marie Dupont',
                'senderEmail'    => 'marie.dupont@email.fr',
                'phone'          => '06 12 34 56 78',
                'message'        => "Bonjour,\nJe souhaite un devis pour un entretien mensuel.\nMerci.",
                'author'         => 'Marie Dupont',
                'title'          => 'Un accompagnement bienveillant',
                'comment'        => 'Une expérience très apaisante, merci pour votre écoute et votre professionnalisme.',
                'rate'           => 5,
                'createdAt'      => new \DateTimeImmutable(),
            ]);
        } catch (TwigError $e) {
            return new JsonResponse([
                'message' => 'Erreur de template Twig',
                'detail'  => $e->getMessage(),
            ], 422);
        }

        return new JsonResponse(['html' => $html]);
    }
}
