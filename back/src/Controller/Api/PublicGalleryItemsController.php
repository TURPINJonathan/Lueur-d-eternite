<?php

namespace App\Controller\Api;

use App\Entity\GalleryItem;
use App\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/api/public/gallery-items', name: 'api_public_gallery_items')]
final class PublicGalleryItemsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        /** @var list<GalleryItem> $items */
        $items = $this->entityManager
            ->getRepository(GalleryItem::class)
            ->findBy(['visibleInGallery' => true], ['createdAt' => 'DESC']);

        $mapMediaToUrl = function (?Media $media): ?string {
            if (!$media) {
                return null;
            }

            return $this->generateUrl(
                'api_public_media_get',
                ['id' => $media->getId()],
                UrlGeneratorInterface::ABSOLUTE_PATH
            );
        };

        $payload = array_map(static function (GalleryItem $item) use ($mapMediaToUrl): array {
            if ($item->getKind()->value === 'single') {
                $src = $mapMediaToUrl($item->getSrcMedia());
                $thumb = $mapMediaToUrl($item->getThumbMedia()) ?? $src;

                return [
                    'id' => $item->getId(),
                    'kind' => 'single',
                    'src' => $src,
                    'thumb' => $thumb,
                    'alt' => $item->getAlt(),
                ];
            }

            $before = $mapMediaToUrl($item->getBeforeMedia());
            $after = $mapMediaToUrl($item->getAfterMedia());
            $thumb = $mapMediaToUrl($item->getThumbMedia()) ?? $before;
            $afterThumb = $mapMediaToUrl($item->getAfterThumbMedia()) ?? $after;

            return [
                'id' => $item->getId(),
                'kind' => 'compare',
                'beforeSrc' => $before,
                'afterSrc' => $after,
                'thumb' => $thumb,
                'afterThumb' => $afterThumb,
                'alt' => $item->getAlt(),
            ];
        }, $items);

        return new JsonResponse($payload);
    }
}

