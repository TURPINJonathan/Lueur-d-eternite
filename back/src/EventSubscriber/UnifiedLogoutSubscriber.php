<?php

namespace App\EventSubscriber;

use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;

final class UnifiedLogoutSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RefreshTokenManagerInterface $refreshTokenManager,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogout',
        ];
    }

    public function onLogout(LogoutEvent $event): void
    {
        $request = $event->getRequest();

        $token = $request->cookies->get('refresh_token');
        if (!$token) {
            $data = json_decode($request->getContent() ?: '', true);
            $token = \is_array($data) ? ($data['refresh_token'] ?? null) : null;
        }

        if (\is_string($token) && '' !== $token) {
            $refreshToken = $this->refreshTokenManager->get($token);
            if ($refreshToken) {
                $this->refreshTokenManager->delete($refreshToken);
            }
        }

        $isApi = str_starts_with($request->getPathInfo(), '/api/');
        $response = $isApi
            ? new JsonResponse(['ok' => true])
            : new RedirectResponse('/login');

        $response->headers->setCookie(
            Cookie::create('refresh_token')
                ->withValue('')
                ->withExpires(1)
                ->withPath('/')
                ->withHttpOnly(true)
                ->withSecure(false)
                ->withSameSite(Cookie::SAMESITE_LAX),
        );

        $response->headers->setCookie(
            Cookie::create('refresh_token')
                ->withValue('')
                ->withExpires(1)
                ->withPath('/')
                ->withHttpOnly(true)
                ->withSecure(true)
                ->withSameSite(Cookie::SAMESITE_LAX),
        );

        $event->setResponse($response);
    }
}
