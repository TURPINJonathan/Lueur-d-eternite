<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Review;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\RedirectResponse;

#[AdminRoute(path: 'reviews', name: 'reviews')]
final class ReviewCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Review::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Avis')
            ->setEntityLabelInPlural('Avis')
            ->setPageTitle(Crud::PAGE_INDEX, 'Gestion des avis')
            ->addFormTheme('admin/form_themes/review_readonly_dates.html.twig')
            ->setDefaultSort(['created_at' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $approve = Action::new('approve', 'Approuver')
            ->linkToCrudAction('approveReview')
            ->displayIf(static fn (Review $review): bool => null === $review->getApprouvedAt())
            ->setCssClass('btn btn-success');

        $reject = Action::new('reject', 'Refuser')
            ->linkToCrudAction('rejectReview')
            ->displayIf(static fn (Review $review): bool => null !== $review->getApprouvedAt())
            ->setCssClass('btn btn-warning');

        $configuredActions = $actions
            ->disable(Action::NEW)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $approve)
            ->add(Crud::PAGE_DETAIL, $reject)
            ->add(Crud::PAGE_EDIT, $approve)
            ->add(Crud::PAGE_EDIT, $reject);

        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            $configuredActions = $configuredActions
                ->disable(Action::EDIT);
        }

        return $configuredActions;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IdField::new('id')->hideOnDetail()->hideOnIndex(),
            TextField::new('author', 'Auteur')->setColumns(4),
            EmailField::new('email', 'Email')->setColumns(4),
            IntegerField::new('rate', 'Note')
                ->setColumns(4)
                ->formatValue(static function ($value): string {
                    if (null === $value) {
                        return '-';
                    }

                    return \sprintf('%d/5', (int) $value);
                }),
            TextField::new('title', 'Titre')->setRequired(false)->setColumns(12),
            TextareaField::new('comment', 'Commentaire')->setColumns(12),
            DateTimeField::new('created_at', 'Posté le')->hideOnForm(),
            DateTimeField::new('approuved_at', 'Approuvé le')->hideOnForm()->setRequired(false),
            TextField::new('createdAtDisplay', 'Posté le')
                ->onlyOnForms()
                ->setFormTypeOption('mapped', false)
                ->setFormTypeOption('disabled', true)
                ->setFormTypeOption('block_name', 'review_created_at_display')
                ->setColumns(6),
            TextField::new('approuvedAtDisplay', 'Approuvé le')
                ->onlyOnForms()
                ->setFormTypeOption('mapped', false)
                ->setFormTypeOption('disabled', true)
                ->setFormTypeOption('block_name', 'review_approuved_at_display')
                ->setColumns(6),
        ];

        if (!$this->isGranted('ROLE_SUPER_ADMIN') && \in_array($pageName, [Crud::PAGE_NEW, Crud::PAGE_EDIT], true)) {
            foreach ($fields as $field) {
                $field->setFormTypeOption('disabled', true);
            }
        }

        return $fields;
    }

    public function approveReview(AdminContext $context, EntityManagerInterface $entityManager): RedirectResponse
    {
        $review = $this->getReviewFromContext($context, $entityManager);
        if ($review instanceof Review) {
            $review->setApprouvedAt(new \DateTimeImmutable());
            $entityManager->flush();
        }

        return $this->redirect($context->getReferrer() ?? $this->generateUrl('back_office_reviews_index'));
    }

    public function rejectReview(AdminContext $context, EntityManagerInterface $entityManager): RedirectResponse
    {
        $review = $this->getReviewFromContext($context, $entityManager);
        if ($review instanceof Review) {
            $review->setApprouvedAt(null);
            $entityManager->flush();
        }

        return $this->redirect($context->getReferrer() ?? $this->generateUrl('back_office_reviews_index'));
    }

    private function getReviewFromContext(AdminContext $context, EntityManagerInterface $entityManager): ?Review
    {
        $request = $context->getRequest();
        $entityId = $request->query->getInt('entityId');
        if ($entityId <= 0) {
            $entityId = (int) $request->attributes->get('entityId', 0);
        }
        if ($entityId <= 0) {
            return null;
        }

        /** @var Review|null $review */
        $review = $entityManager->getRepository(Review::class)->find($entityId);

        return $review;
    }
}
