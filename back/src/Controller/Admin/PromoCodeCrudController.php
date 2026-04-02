<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\PromoCode;
use App\Enum\DiscountType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminRoute(path: 'codes-promo', name: 'codes_promo')]
final class PromoCodeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PromoCode::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Code promo')
            ->setEntityLabelInPlural('Codes promo')
            ->setPageTitle(Crud::PAGE_INDEX, 'Codes promo')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->addFormTheme('admin/form_themes/promo_code_masking.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        $discountTypeChoices = [
            'Pourcentage'  => DiscountType::PERCENT,
            'Montant fixe' => DiscountType::FIXED_AMOUNT,
        ];

        if (Crud::PAGE_NEW === $pageName || Crud::PAGE_EDIT === $pageName) {
            return [
                IdField::new('id')->hideOnForm(),
                TextField::new('name', 'Nom interne')->setRequired(true)->setColumns(6),
                AssociationField::new('tarifs', 'Tarifs ciblés (vide = tous)')
                    ->setColumns(4)
                    ->setFormTypeOption('by_reference', false),
                BooleanField::new('isActive', 'Actif')->renderAsSwitch()->setColumns(2),

                DateTimeField::new('startsAt', 'Début de validité')->setRequired(true)->setColumns(3),
                DateTimeField::new('endsAt', 'Fin de validité')->setRequired(true)->setColumns(3),
                ChoiceField::new('discountType', 'Type de réduction')
                    ->setChoices($discountTypeChoices)
                    ->setRequired(true)
                    ->setColumns(3),
                TextField::new('discountValueText', 'Valeur')
                    ->setRequired(true)
                    ->setColumns(3)
                    ->setHelp('Si % : 10 ou 10,5. Si montant : 4,99'),

                BooleanField::new('isUniqueCode', 'Code unique')->renderAsSwitch()->setColumns(2),
                TextField::new('code', 'Code')
                    ->setRequired(false)
                    ->setColumns(10)
                    ->setHelp('Si "Code unique" est activé, ce champ est ignoré et un code est généré automatiquement.'),
            ];
        }

        return [
            TextField::new('code', 'Code'),
            TextField::new('name', 'Nom interne'),
            ChoiceField::new('discountType', 'Type')->setChoices($discountTypeChoices),
            TextField::new('discountValueText', 'Valeur'),
            DateTimeField::new('startsAt', 'Début'),
            DateTimeField::new('endsAt', 'Fin'),
            BooleanField::new('isActive', 'Actif'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof PromoCode) {
            $this->assertDateRange($entityInstance);
            $this->prepareCodeForCreate($entityManager, $entityInstance);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof PromoCode) {
            $this->assertDateRange($entityInstance);
            $this->prepareCodeForUpdate($entityManager, $entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function assertDateRange(PromoCode $promoCode): void
    {
        if ($promoCode->getEndsAt() < $promoCode->getStartsAt()) {
            throw new \RuntimeException('La date de fin de validité doit être postérieure à la date de début.');
        }
    }

    private function prepareCodeForCreate(EntityManagerInterface $entityManager, PromoCode $promoCode): void
    {
        if ($promoCode->isUniqueCode()) {
            // En création, un code unique est TOUJOURS généré côté serveur.
            $promoCode->setCode($this->generateUniqueCode($entityManager, $promoCode->getName()));

            return;
        }

        $code = trim($promoCode->getCode());
        if ('' === $code) {
            throw new \RuntimeException('Le champ "Code" est obligatoire si "Code unique" est désactivé.');
        }

        $promoCode->setCode($code);
    }

    private function prepareCodeForUpdate(EntityManagerInterface $entityManager, PromoCode $promoCode): void
    {
        $uow = $entityManager->getUnitOfWork();
        $originalData = $uow->getOriginalEntityData($promoCode);
        $originalCode = strtoupper(trim((string) ($originalData['code'] ?? '')));
        $originalIsUnique = (bool) ($originalData['isUniqueCode'] ?? false);

        if ($promoCode->isUniqueCode()) {
            // Champ "code" ignoré : on conserve le code historique s'il existait déjà en unique.
            // Si on bascule depuis non-unique -> unique, on génère un nouveau code serveur.
            if ($originalIsUnique && '' !== $originalCode) {
                $promoCode->setCode($originalCode);
            } else {
                $promoCode->setCode($this->generateUniqueCode($entityManager, $promoCode->getName()));
            }

            return;
        }

        $code = trim($promoCode->getCode());
        if ('' === $code) {
            throw new \RuntimeException('Le champ "Code" est obligatoire si "Code unique" est désactivé.');
        }

        $promoCode->setCode($code);
    }

    private function generateUniqueCode(EntityManagerInterface $entityManager, string $internalName): string
    {
        $prefix = $this->buildCodePrefix($internalName);

        for ($i = 0; $i < 20; ++$i) {
            $candidate = $prefix . '-' . strtoupper(bin2hex(random_bytes(6)));

            $exists = $entityManager->getRepository(PromoCode::class)->findOneBy(['code' => $candidate]);
            if (!$exists) {
                return $candidate;
            }
        }

        throw new \RuntimeException('Impossible de générer un code promo unique. Merci de réessayer.');
    }

    private function buildCodePrefix(string $internalName): string
    {
        $normalized = trim($internalName);
        if ('' === $normalized) {
            return 'PROMO';
        }

        $normalized = strtoupper($normalized);
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized) ?: $normalized;
        $normalized = preg_replace('/[^A-Z0-9]+/', '-', $normalized) ?? '';
        $normalized = trim($normalized, '-');
        $normalized = preg_replace('/-+/', '-', $normalized) ?? '';

        if ('' === $normalized) {
            return 'PROMO';
        }

        return substr($normalized, 0, 20);
    }
}
