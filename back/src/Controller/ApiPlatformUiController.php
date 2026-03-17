<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ApiPlatformUiController
{
    #[Route('/api', name: 'api_platform_ui_entry', methods: ['GET'], priority: 100)]
    public function __invoke(): RedirectResponse
    {
        return new RedirectResponse('/api/docs');
    }
}
