<?php

namespace App\Security;

use App\Entity\User;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager,
        private RefreshTokenManagerInterface $refreshTokenManager,
        private string $appEnv,
    ) {}

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['message' => 'Invalid user'], 400);
        }

        // JWT (court)
        $jwt = $this->jwtManager->create($user);

        // Refresh token (long) - stocké en DB
        $refreshToken = $this->refreshTokenManager->create();
        $refreshToken->setUsername($user->getUserIdentifier());
        $refreshToken->setRefreshToken(bin2hex(random_bytes(64))); // 128 chars
        $refreshToken->setValid((new \DateTime())->modify('+30 days'));

        $this->refreshTokenManager->save($refreshToken);

        $response = new JsonResponse(['token' => $jwt]);

        $response->headers->setCookie(
            Cookie::create('refresh_token')
                ->withValue($refreshToken->getRefreshToken())
                ->withExpires((new \DateTime())->modify('+30 days'))
                ->withPath('/')
                ->withHttpOnly(true)
                ->withSecure('prod' === $this->appEnv)
                ->withSameSite('prod' === $this->appEnv ? Cookie::SAMESITE_NONE : Cookie::SAMESITE_LAX),
        );

        return $response;
    }
}
