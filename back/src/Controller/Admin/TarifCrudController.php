<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Tarif;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

#[AdminRoute(path: 'tarifs', name: 'tarifs')]
final class TarifCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public static function getEntityFqcn(): string
    {
        return Tarif::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Tarif')
            ->setEntityLabelInPlural('Tarifs')
            ->setPageTitle(Crud::PAGE_INDEX, 'Tarifs')
            ->setDefaultSort(['position' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_NEW === $pageName || Crud::PAGE_EDIT === $pageName) {
            return [
                IdField::new('id')->hideOnForm(),

                TextField::new('title', 'Nom')
                    ->setRequired(true)
                    ->setColumns(4),

                TextField::new('priceText', 'Prix (€)')
                    ->setRequired(true)
                    ->setColumns(4)
                    ->setHelp('Exemples : 4,99 ou 4.99'),

                ChoiceField::new('position', 'Position')
                    ->setRequired(true)
                    ->setColumns(4)
                    ->setChoices($this->buildPositionChoices())
                    ->setHelp('Ordre d’affichage des tarifs sur le site'),

                TextField::new('description', 'Description')
                    ->setRequired(true)
                    ->setHelp('Doit être courte')
                    ->setColumns(12),
            ];
        }

        return [
            IntegerField::new('position', 'Position'),
            TextField::new('title', 'Nom'),
            TextField::new('priceText', 'Prix (€)'),
            TextField::new('description', 'Description'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Tarif) {
            parent::persistEntity($entityManager, $entityInstance);

            return;
        }

        $count = (int) $entityManager->getRepository(Tarif::class)->count([]);
        $targetPosition = max(1, min($entityInstance->getPosition(), $count + 1));

        // Décale les tarifs existants pour libérer la position demandée.
        $entityManager->createQueryBuilder()
            ->update(Tarif::class, 't')
            ->set('t.position', 't.position + 1')
            ->where('t.position >= :target')
            ->setParameter('target', $targetPosition)
            ->getQuery()
            ->execute();

        $entityInstance->setPosition($targetPosition);

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Tarif) {
            parent::updateEntity($entityManager, $entityInstance);

            return;
        }

        $uow = $entityManager->getUnitOfWork();
        $originalData = $uow->getOriginalEntityData($entityInstance);
        $oldPosition = (int) ($originalData['position'] ?? $entityInstance->getPosition());

        $count = (int) $entityManager->getRepository(Tarif::class)->count([]);
        $newPosition = max(1, min($entityInstance->getPosition(), $count));

        if ($newPosition !== $oldPosition) {
            // IMPORTANT (index unique sur position) :
            // on place d'abord l'élément déplacé à une position temporaire
            // pour éviter les collisions transitoires pendant les décalages.
            $entityManager->createQueryBuilder()
                ->update(Tarif::class, 't')
                ->set('t.position', ':tempPosition')
                ->where('t.id = :entityId')
                ->setParameter('tempPosition', 0)
                ->setParameter('entityId', $entityInstance->getId())
                ->getQuery()
                ->execute();
        }

        if ($newPosition < $oldPosition) {
            // Remonte l'élément: on décale les éléments intermédiaires vers le bas.
            $entityManager->createQueryBuilder()
                ->update(Tarif::class, 't')
                ->set('t.position', 't.position + 1')
                ->where('t.position >= :newPosition')
                ->andWhere('t.position < :oldPosition')
                ->setParameter('newPosition', $newPosition)
                ->setParameter('oldPosition', $oldPosition)
                ->getQuery()
                ->execute();
        } elseif ($newPosition > $oldPosition) {
            // Descend l'élément: on remonte les éléments intermédiaires d'un cran.
            $entityManager->createQueryBuilder()
                ->update(Tarif::class, 't')
                ->set('t.position', 't.position - 1')
                ->where('t.position > :oldPosition')
                ->andWhere('t.position <= :newPosition')
                ->setParameter('oldPosition', $oldPosition)
                ->setParameter('newPosition', $newPosition)
                ->getQuery()
                ->execute();
        }

        $entityInstance->setPosition($newPosition);

        parent::updateEntity($entityManager, $entityInstance);
    }

    /**
     * Construit le select de position avec les positions autorisées uniquement.
     */
    private function buildPositionChoices(): array
    {
        $count = (int) $this->entityManager->getRepository(Tarif::class)->count([]);

        $context = $this->getContext();
        $isEdit = Crud::PAGE_EDIT === $context?->getCrud()->getCurrentPage();

        // En édition : 1..N (N = nombre actuel d'items)
        // En création : 1..N+1
        $max = $isEdit ? max(1, $count) : ($count + 1);

        $choices = [];
        for ($position = 1; $position <= $max; ++$position) {
            $choices[(string) $position] = $position;
        }

        return $choices;
    }
}
