<?php

namespace App\Controller\Admin;

use App\Enum\UserRole;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

#[AdminDashboard(routePath: '/backoffice', routeName: 'back_office')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // 1.1) If you have enabled the "pretty URLs" feature:
        // return $this->redirectToRoute('admin_user_index');
        //
        // 1.2) Same example but using the "ugly URLs" that were used in previous EasyAdmin versions:
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirectToRoute('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Lueur d\'Éternité');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');

        yield MenuItem::linkToRoute('Services', 'fa fa-wrench', 'back_office_services_index');

        yield MenuItem::linkToRoute('Galerie', 'fa fa-images', 'back_office_galerie_index');

        yield MenuItem::subMenu('Tarifs', 'fa fa-receipt')->setSubItems([
            MenuItem::linkToRoute('Tarifs', 'fa fa-receipt', 'back_office_tarifs_index'),
            MenuItem::linkToRoute('Promotions', 'fa fa-tags', 'back_office_promotions_index'),
            MenuItem::linkToRoute('Codes promo', 'fa fa-ticket', 'back_office_codes_promo_index'),
        ]);

        yield MenuItem::subMenu('Administration', 'fa fa-user-shield')->setSubItems([
            MenuItem::linkToRoute('Utilisateurs', 'fa fa-user', 'back_office_users_index')->setPermission(UserRole::SUPER_ADMIN->value),
            MenuItem::linkToRoute('Paramètres du site', 'fa fa-sliders-h', 'back_office_parametres_site_index'),
        ]);
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        // Usually it's better to call the parent method because that gives you a
        // user menu with some menu items already created ("sign out", "exit impersonation", etc.)
        // if you prefer to create the user menu from scratch, use: return UserMenu::new()->...
        return parent::configureUserMenu($user)
            ->displayUserAvatar(false);

        // TODO Ajouter un lien "Mon profil" qui redirige vers la page de détail de l'utilisateur connecté ou vers une page de profil dédiée
        // TODO Afficher le nom de l'utilisateur connecté dans le menu utilisateur (ex: "John Doe") au lieu de "email"
        // TODO Afficher un avatar personnalisé pour l'utilisateur connecté (ex: une image uploadée ou un avatar généré à partir de son nom) au lieu de l'avatar par défaut
        // TODO Ajouter un lien "Déconnexion" qui redirige vers la route de logout de Symfony (ex: /logout) pour permettre à l'utilisateur de se déconnecter facilement
        // TODO Ajouter un lien "Impersonner" qui permet à un admin de se faire passer pour un autre utilisateur (ex: un client) afin de voir le site comme lui et de résoudre plus facilement les problèmes rencontrés par les utilisateurs
        // TODO Ajouter un lien "Paramètres" qui redirige vers une page de paramètres du compte où l'utilisateur peut modifier ses informations personnelles, son mot de passe, etc.
        // TODO Ajouter un lien "Support" qui redirige vers une page de contact ou de FAQ pour aider les utilisateurs à trouver des réponses à leurs questions ou à contacter le support en cas de problème
    }
}
