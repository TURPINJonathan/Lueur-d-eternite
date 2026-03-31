<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\SiteSettings;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

#[AdminRoute(path: 'parametres-site', name: 'parametres_site')]
final class SiteSettingsCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

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
        return [
            FormField::addPanel('Coordonnées publiques')->setIcon('fa fa-phone'),
            TextField::new('contactPhoneDisplay', 'Téléphone (affiché)')
                ->setHelp('Exemple: 06 25 29 59 52')
                ->setColumns(12),
            TextField::new('contactEmail', 'Email de contact')
                ->setHelp('Exemple: contact@domaine.fr')
                ->setColumns(12),

            FormField::addPanel('Zone d’intervention')->setIcon('fa fa-map-marker-alt'),
            IntegerField::new('serviceRadiusKm', 'Rayon (km)')
                ->setHelp('Utilisé pour la carte et les textes dynamiques')
                ->setColumns(4),
            TextField::new('serviceAreaText', 'Libellé de zone')
                ->setHelp('Exemple: Caen et ses alentours')
                ->setColumns(8),
            TextField::new('legalZoneNotice', 'Texte zone (CGV)')
                ->setColumns(12),

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
            return $settings;
        }

        $settings = new SiteSettings();
        $this->entityManager->persist($settings);
        $this->entityManager->flush();

        return $settings;
    }
}
