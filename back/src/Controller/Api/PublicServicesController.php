<?php

namespace App\Controller\Api;

use App\Entity\Media;
use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/api/public/services', name: 'api_public_services')]
final class PublicServicesController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    #[Route('', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        /** @var list<Service> $services */
        $services = $this->entityManager
            ->getRepository(Service::class)
            ->findBy([], ['createdAt' => 'DESC']);

        $mapMediaToUrl = function (?Media $media): ?string {
            if (!$media) {
                return null;
            }

            return $this->generateUrl(
                'api_public_media_get',
                ['id' => $media->getId()],
                UrlGeneratorInterface::ABSOLUTE_PATH,
            );
        };

        $payload = array_map(static function (Service $service) use ($mapMediaToUrl): array {
            return [
                'id'         => $service->getId(),
                'title'      => $service->getTitle(),
                'subtitle'   => $service->getSubtitle(),
                'items'      => $service->getItems(),
                'picture'    => $mapMediaToUrl($service->getPictureMedia()),
                'pictureAlt' => $service->getPictureAlt(),
            ];
        }, $services);

        return new JsonResponse($payload);
    }
}
