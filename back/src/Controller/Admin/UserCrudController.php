<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[AdminRoute(path: 'users', name: 'users')]
class UserCrudController extends AbstractCrudController
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setPageTitle(Crud::PAGE_INDEX, 'Utilisateurs')
            ->setSearchFields(['email', 'firstname', 'lastname'])
            ->setDefaultSort(['id' => 'DESC'])
            ->setDefaultRowAction(Action::DETAIL);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            return [
                IdField::new('id')->hideOnForm()->hideOnIndex()->hideOnDetail(),
                EmailField::new('email', 'Email'),
                TextField::new('lastname', 'Nom'),
                TextField::new('firstname', 'Prénom'),
                ChoiceField::new('roles', 'Rôles')
                    ->setChoices([
                        UserRole::USER->label()        => UserRole::USER->value,
                        UserRole::ADMIN->label()       => UserRole::ADMIN->value,
                        UserRole::SUPER_ADMIN->label() => UserRole::SUPER_ADMIN->value,
                    ])
                    ->allowMultipleChoices(),
            ];
        }

        if (Crud::PAGE_DETAIL === $pageName) {
            return [
                FormField::addColumn(6),
                FormField::addPanel('Informations personnelles')->setIcon('fa fa-user'),
                EmailField::new('email', 'Email')->setColumns(12),
                TextField::new('firstname', 'Prénom')->setColumns(6),
                TextField::new('lastname', 'Nom')->setColumns(6),

                FormField::addColumn(6),
                FormField::addPanel('Sécurité')->setIcon('fa fa-shield'),
                ChoiceField::new('roles', 'Rôles')
                    ->setChoices([
                        UserRole::USER->label()        => UserRole::USER->value,
                        UserRole::ADMIN->label()       => UserRole::ADMIN->value,
                        UserRole::SUPER_ADMIN->label() => UserRole::SUPER_ADMIN->value,
                    ])
                    ->allowMultipleChoices()
                    ->setColumns(12),
            ];
        }

        // PAGE_EDIT / PAGE_NEW
        $choices = [
            UserRole::USER->label()  => UserRole::USER->value,
            UserRole::ADMIN->label() => UserRole::ADMIN->value,
        ];

        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            $choices[UserRole::SUPER_ADMIN->label()] = UserRole::SUPER_ADMIN->value;
        }

        return [
            IdField::new('id')->hideOnForm()->hideOnIndex()->hideOnDetail(),

            FormField::addColumn(12),
            FormField::addPanel('Informations personnelles')->setIcon('fa fa-user'),
            EmailField::new('email', 'Email')
                ->setFormTypeOption('attr', ['placeholder' => 'utilisateur@example.com'])
                ->setColumns(12),
            TextField::new('lastname', 'Nom')
                ->setFormTypeOption('attr', ['placeholder' => 'Dupont'])
                ->setColumns(6),
            TextField::new('firstname', 'Prénom')
                ->setFormTypeOption('attr', ['placeholder' => 'Jean'])
                ->setColumns(6),

            FormField::addPanel('Sécurité')->setIcon('fa fa-shield'),
            TextField::new('plainPassword', 'Mot de passe')
                ->setFormType(PasswordType::class)
                ->setHelp(Crud::PAGE_NEW === $pageName ? 'Saisissez un mot de passe sécurisé' : 'Laissez vide pour conserver le mot de passe actuel')
                ->setRequired(Crud::PAGE_NEW === $pageName)
                ->setColumns(12),
            ChoiceField::new('roles', 'Rôles')
                ->setChoices($choices)
                ->allowMultipleChoices()
                ->setHelp('Sélectionnez un ou plusieurs rôles')
                ->setColumns(12),

            DateTimeField::new('createdAt', 'Créé le')->hideOnForm(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            $this->assertCanManageUser($entityInstance);

            // created_at NOT NULL
            if (null === $entityInstance->getCreatedAt()) {
                $entityInstance->setCreatedAt(new \DateTimeImmutable());
            }

            // password NOT NULL
            $this->hashPasswordOrFailOnCreate($entityInstance);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            $this->assertCanManageUser($entityInstance);

            // En édition: on ne change le password que si l’admin a saisi un nouveau mot de passe
            if ($entityInstance->getPlainPassword()) {
                $entityInstance->setPassword(
                    $this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPlainPassword()),
                );
                $entityInstance->setPlainPassword(null);
            }
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function hashPasswordOrFailOnCreate(User $user): void
    {
        $plain = $user->getPlainPassword();
        if (!$plain) {
            // Si tu arrives ici, c’est que le formulaire a laissé passer un mot de passe vide.
            // On bloque proprement plutôt que d’insérer NULL.
            throw new \RuntimeException('Mot de passe obligatoire à la création.');
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $plain));
        $user->setPlainPassword(null);
    }

    private function assertCanManageUser(User $user): void
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Vous n\'avez pas les droits pour créer/modifier des utilisateurs.');
        }

        $validRoles = [UserRole::USER->value, UserRole::ADMIN->value, UserRole::SUPER_ADMIN->value];
        $roles = array_values(array_filter(array_unique($user->getRoles()), static fn (string $role): bool => \in_array($role, $validRoles, true)));

        if (\in_array(UserRole::SUPER_ADMIN->value, $roles, true) && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedException('Seul un super admin peut attribuer ROLE_SUPER_ADMIN.');
        }

        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            foreach ($roles as $role) {
                if (!\in_array($role, [UserRole::USER->value, UserRole::ADMIN->value], true)) {
                    throw new AccessDeniedException('Un admin ne peut attribuer que ROLE_ADMIN ou ROLE_USER.');
                }
            }
        }

        $user->setRoles($roles);
    }
}
