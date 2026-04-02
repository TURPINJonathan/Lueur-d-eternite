<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Promotion;
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

#[AdminRoute(path: 'promotions', name: 'promotions')]
final class PromotionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Promotion::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Promotion')
            ->setEntityLabelInPlural('Promotions')
            ->setPageTitle(Crud::PAGE_INDEX, 'Promotions')
            ->setDefaultSort(['createdAt' => 'DESC']);
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
                TextField::new('name', 'Nom')->setRequired(true)->setColumns(6),
                AssociationField::new('tarifs', 'Tarifs ciblés (vide = tous)')
                    ->setColumns(4)
                    ->setFormTypeOption('by_reference', false),
                BooleanField::new('isActive', 'Active')->renderAsSwitch()->setColumns(2),

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
            ];
        }

        return [
            TextField::new('name', 'Nom'),
            ChoiceField::new('discountType', 'Type')->setChoices($discountTypeChoices),
            TextField::new('discountValueText', 'Valeur'),
            DateTimeField::new('startsAt', 'Début'),
            DateTimeField::new('endsAt', 'Fin'),
            BooleanField::new('isActive', 'Active'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Promotion) {
            $this->assertDateRange($entityInstance);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Promotion) {
            $this->assertDateRange($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function assertDateRange(Promotion $promotion): void
    {
        if ($promotion->getEndsAt() < $promotion->getStartsAt()) {
            throw new \RuntimeException('La date de fin de validité doit être postérieure à la date de début.');
        }
    }
}
