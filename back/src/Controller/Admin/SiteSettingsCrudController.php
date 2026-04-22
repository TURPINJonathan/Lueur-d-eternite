<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\SiteSettings;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

#[AdminRoute(path: 'parametres-site', name: 'parametres_site')]
final class SiteSettingsCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {}

    public static function getEntityFqcn(): string
    {
        return SiteSettings::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Paramètre du site')
            ->setEntityLabelInPlural('Paramètres du site')
            ->setPageTitle(Crud::PAGE_EDIT, 'Paramètres du site');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::DELETE, Action::BATCH_DELETE);
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addCssFile('/assets/admin/site-settings-email-editor.css')
            ->addJsFile('/assets/admin/site-settings-email-editor.js');
    }

    public function index(AdminContext $context): Response
    {
        $settings = $this->getOrCreateSingleton();

        $url = $this->adminUrlGenerator
            ->unsetAll()
            ->setController(self::class)
            ->setAction(Action::EDIT)
            ->setEntityId((string) $settings->getId())
            ->generateUrl();

        return new RedirectResponse($url);
    }

    public function configureFields(string $pageName): iterable
    {
        $this->ensureEmailTemplatesInitialized();

        return [
            FormField::addTab('Général')->setIcon('fa fa-sliders-h'),
            FormField::addPanel('Coordonnées publiques')->setIcon('fa fa-phone'),
            TextField::new('contactPhoneDisplay', 'Téléphone (affiché)')
                ->setHelp('Exemple: 06 25 29 59 52')
                ->setColumns(6),
            TextField::new('contactEmail', 'Email de contact')
                ->setHelp('Exemple: contact@domaine.fr')
                ->setColumns(6),

            FormField::addPanel('Zone d’intervention')->setIcon('fa fa-map-marker-alt'),
            IntegerField::new('serviceRadiusKm', 'Rayon (km)')
                ->setHelp('Utilisé pour la carte et les textes dynamiques')
                ->setColumns(4),
            TextField::new('serviceAreaText', 'Libellé de zone')
                ->setHelp('Exemple: Caen et ses alentours')
                ->setColumns(8),
            TextField::new('legalZoneNotice', 'Texte zone (CGV)')
                ->setColumns(12),

            FormField::addPanel('Réseaux sociaux')->setIcon('fa fa-link'),
            TextField::new('facebookLink', 'Lien Facebook')
                ->setHelp('Exemple: https://www.facebook.com/monentreprise')
                ->setRequired(false)
                ->setColumns(6),
            TextField::new('instagramLink', 'Lien Instagram')
                ->setHelp('Exemple: https://www.instagram.com/monentreprise')
                ->setRequired(false)
                ->setColumns(6),
            TextField::new('linkedinLink', 'Lien LinkedIn')
                ->setHelp('Exemple: https://www.linkedin.com/company/monentreprise')
                ->setRequired(false)
                ->setColumns(6),
            TextField::new('xLink', 'Lien X (ex Twitter)')
                ->setHelp('Exemple: https://www.x.com/monentreprise')
                ->setRequired(false)
                ->setColumns(6),
            TextField::new('tiktokLink', 'Lien TikTok')
                ->setHelp('Exemple: https://www.tiktok.com/@monentreprise')
                ->setRequired(false)
                ->setColumns(6),
            TextField::new('youtubeLink', 'Lien YouTube')
                ->setHelp('Exemple: https://www.youtube.com/channel/monentreprise')
                ->setRequired(false)
                ->setColumns(6),

            FormField::addTab('Emails')->setIcon('fa fa-envelope'),
            FormField::addPanel('Formulaire de contact (emails)')->setIcon('fa fa-paper-plane'),
            TextField::new('contactFormRecipientEmail', 'Destinataire formulaire')
                ->setHelp('Email qui reçoit la demande du formulaire')
                ->setColumns(6),
            TextField::new('contactFormSenderName', 'Nom expéditeur des emails')
                ->setHelp('Nom affiché dans les emails envoyés')
                ->setColumns(6),
            BooleanField::new('contactFormSendConfirmation', 'Envoyer un email de confirmation au client')
                ->setColumns(12),
            TextareaField::new('contactFormTemplateAdmin', 'Template email (demande reçue)')
                ->setHelp('Code Twig/HTML complet. Aperçu live sous le champ.')
                ->setNumOfRows(18)
                ->setFormTypeOption('attr', [
                    'class'             => 'js-email-template-editor',
                    'data-preview-kind' => 'admin',
                    'data-preview-url'  => '/backoffice/parametres-site/email-template-preview',
                ])
                ->setColumns(12),
            TextareaField::new('contactFormTemplateUser', 'Template email (confirmation client)')
                ->setHelp('Code Twig/HTML complet. Aperçu live sous le champ.')
                ->setNumOfRows(18)
                ->setFormTypeOption('attr', [
                    'class'             => 'js-email-template-editor',
                    'data-preview-kind' => 'user',
                    'data-preview-url'  => '/backoffice/parametres-site/email-template-preview',
                ])
                ->setColumns(12),

            FormField::addPanel('Avis clients (emails)')->setIcon('fa fa-star'),
            BooleanField::new('reviewFormSendConfirmation', 'Envoyer un email de confirmation pour les avis')
                ->setColumns(12),
            TextareaField::new('reviewFormTemplateAdmin', 'Template email (nouvel avis en attente)')
                ->setHelp('Code Twig/HTML complet. Aperçu live sous le champ.')
                ->setNumOfRows(18)
                ->setFormTypeOption('attr', [
                    'class'             => 'js-email-template-editor',
                    'data-preview-kind' => 'review_admin',
                    'data-preview-url'  => '/backoffice/parametres-site/email-template-preview',
                ])
                ->setColumns(12),
            TextareaField::new('reviewFormTemplateUser', 'Template email (confirmation dépôt d\'avis)')
                ->setHelp('Code Twig/HTML complet. Aperçu live sous le champ.')
                ->setNumOfRows(18)
                ->setFormTypeOption('attr', [
                    'class'             => 'js-email-template-editor',
                    'data-preview-kind' => 'review_user',
                    'data-preview-url'  => '/backoffice/parametres-site/email-template-preview',
                ])
                ->setColumns(12),

            FormField::addTab('Juridique')->setIcon('fa fa-balance-scale'),
            FormField::addPanel('Mentions légales')->setIcon('fa fa-balance-scale'),
            TextField::new('legalEntityName', 'Nom / raison sociale')->setColumns(6),
            TextField::new('legalStatus', 'Statut')->setColumns(6),
            TextField::new('legalAddress', 'Adresse légale')->setColumns(12),
            TextField::new('legalSiren', 'SIREN')->setColumns(4),
            TextField::new('legalSiret', 'SIRET')->setColumns(4),
            TextField::new('legalVat', 'TVA')->setColumns(4),
            TextField::new('publicationDirector', 'Directeur de publication')->setColumns(12),

            FormField::addPanel('Hébergement')->setIcon('fa fa-server'),
            TextField::new('hostingProviderName', 'Nom hébergeur')->setColumns(4),
            TextField::new('hostingProviderUrl', 'URL hébergeur')->setColumns(8),
            TextField::new('hostingProviderAddress', 'Adresse hébergeur')->setColumns(12),

            FormField::addTab('Technique')->setIcon('fa fa-cogs'),
            FormField::addPanel('Configuration technique')->setIcon('fa fa-cogs'),
            TextareaField::new('technicalConfig', 'Configuration JSON')
                ->setHelp('Clés techniques globales (hors secrets).')
                ->setNumOfRows(12)
                ->setColumns(12),

            DateTimeField::new('updatedAt', 'Dernière modification')->hideOnForm(),
        ];
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof SiteSettings) {
            $entityInstance->touch();
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof SiteSettings) {
            $entityInstance->touch();
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    private function getOrCreateSingleton(): SiteSettings
    {
        $settings = $this->entityManager->getRepository(SiteSettings::class)->findOneBy([]);
        if ($settings instanceof SiteSettings) {
            $this->hydrateDefaultTemplatesIfNeeded($settings);

            return $settings;
        }

        $settings = new SiteSettings();
        $settings
            ->setContactFormTemplateAdmin($this->loadTemplateFile('emails/contact_request_admin.html.twig'))
            ->setContactFormTemplateUser($this->loadTemplateFile('emails/contact_request_user_confirmation.html.twig'))
            ->setReviewFormTemplateAdmin($this->loadTemplateFile('emails/review_posted_pending_admin.html.twig'))
            ->setReviewFormTemplateUser($this->loadTemplateFile('emails/review_posted_pending_user_confirmation.html.twig'))
            ->touch();
        $this->entityManager->persist($settings);
        $this->entityManager->flush();

        return $settings;
    }

    private function loadTemplateFile(string $relativePath): string
    {
        $path = $this->projectDir . '/templates/' . ltrim($relativePath, '/');

        $content = @file_get_contents($path);

        return \is_string($content) ? $content : '';
    }

    private function ensureEmailTemplatesInitialized(): void
    {
        $settings = $this->entityManager->getRepository(SiteSettings::class)->findOneBy([]);
        if (!$settings instanceof SiteSettings) {
            return;
        }

        $this->hydrateDefaultTemplatesIfNeeded($settings);
    }

    private function hydrateDefaultTemplatesIfNeeded(SiteSettings $settings): void
    {
        $changed = false;
        if ('' === trim($settings->getContactFormTemplateAdmin())) {
            $settings->setContactFormTemplateAdmin($this->loadTemplateFile('emails/contact_request_admin.html.twig'));
            $changed = true;
        }
        if ('' === trim($settings->getContactFormTemplateUser())) {
            $settings->setContactFormTemplateUser($this->loadTemplateFile('emails/contact_request_user_confirmation.html.twig'));
            $changed = true;
        }
        if ('' === trim($settings->getReviewFormTemplateAdmin())) {
            $settings->setReviewFormTemplateAdmin($this->loadTemplateFile('emails/review_posted_pending_admin.html.twig'));
            $changed = true;
        }
        if ('' === trim($settings->getReviewFormTemplateUser())) {
            $settings->setReviewFormTemplateUser($this->loadTemplateFile('emails/review_posted_pending_user_confirmation.html.twig'));
            $changed = true;
        }
        if ($changed) {
            $settings->touch();
            $this->entityManager->flush();
        }
    }
}
