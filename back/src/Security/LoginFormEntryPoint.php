<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

final class LoginFormEntryPoint implements AuthenticationEntryPointInterface
{
    use TargetPathTrait;

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        // Save target path under 'main' so form_login redirects back after login
        if ($request->hasSession()) {
            $this->saveTargetPath($request->getSession(), 'main', $request->getUri());
        }

        return new RedirectResponse('/login');
    }
}
