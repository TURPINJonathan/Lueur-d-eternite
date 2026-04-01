<?php

namespace App\Controller\Api;

use App\Entity\GalleryItem;
use App\Entity\Media;
use App\Enum\GalleryItemKind;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

#[Route('/api/admin/gallery-items', name: 'api_admin_gallery_items')]
final class AdminGalleryItemsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var list<GalleryItem> $items */
        $items = $this->entityManager->getRepository(GalleryItem::class)->findBy([], ['createdAt' => 'DESC']);

        $toUrl = function (?Media $media): ?string {
            if (!$media) {
                return null;
            }

            return $this->generateUrl(
                'api_public_media_get',
                ['id' => $media->getId()],
                UrlGeneratorInterface::ABSOLUTE_PATH
            );
        };

        $payload = array_map(static function (GalleryItem $item) use ($toUrl): array {
            if ($item->getKind() === GalleryItemKind::SINGLE) {
                $src = $toUrl($item->getSrcMedia());
                $thumb = $toUrl($item->getThumbMedia()) ?? $src;

                return [
                    'id' => $item->getId(),
                    'kind' => 'single',
                    'src' => $src,
                    'thumb' => $thumb,
                    'alt' => $item->getAlt(),
                    'position' => $item->getPosition(),
                ];
            }

            return [
                'id' => $item->getId(),
                'kind' => 'compare',
                'beforeSrc' => $toUrl($item->getBeforeMedia()),
                'afterSrc' => $toUrl($item->getAfterMedia()),
                'thumb' => $toUrl($item->getThumbMedia()) ?? $toUrl($item->getBeforeMedia()),
                'alt' => $item->getAlt(),
                'position' => $item->getPosition(),
            ];
        }, $items);

        return new JsonResponse($payload);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode((string) $request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $kindRaw = $data['kind'] ?? null;
        $alt = $data['alt'] ?? null;
        if (!is_string($kindRaw) || !is_string($alt) || $alt === '') {
            return new JsonResponse(['error' => 'kind (single|compare) et alt sont requis.'], Response::HTTP_BAD_REQUEST);
        }

        $kind = match ($kindRaw) {
            'single' => GalleryItemKind::SINGLE,
            'compare' => GalleryItemKind::COMPARE,
            default => null,
        };

        if ($kind === null) {
            return new JsonResponse(['error' => 'kind invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $position = is_int($data['position'] ?? null) ? $data['position'] : 0;

        $itemId = Uuid::v4()->toRfc4122();
        $item = new GalleryItem($itemId, $kind, $alt);
        $item->setPosition($position);

        $getMedia = function (?string $id): ?Media {
            if (!$id) {
                return null;
            }

            $media = $this->entityManager->getRepository(Media::class)->find($id);
            if (!$media) {
                throw new \RuntimeException(sprintf('Media introuvable: %s', $id));
            }

            return $media;
        };

        try {
            if ($kind === GalleryItemKind::SINGLE) {
                $srcMedia = $getMedia($data['srcMediaId'] ?? null);
                if (!$srcMedia) {
                    return new JsonResponse(['error' => 'srcMediaId est requis pour kind=single.'], Response::HTTP_BAD_REQUEST);
                }

                $item->setSrcMedia($srcMedia);
                $item->setThumbMedia($getMedia($data['thumbMediaId'] ?? null));
            } else {
                $beforeMedia = $getMedia($data['beforeMediaId'] ?? null);
                $afterMedia = $getMedia($data['afterMediaId'] ?? null);
                if (!$beforeMedia || !$afterMedia) {
                    return new JsonResponse(['error' => 'beforeMediaId et afterMediaId sont requis pour kind=compare.'], Response::HTTP_BAD_REQUEST);
                }

                $item->setBeforeMedia($beforeMedia);
                $item->setAfterMedia($afterMedia);
                $item->setThumbMedia($getMedia($data['thumbMediaId'] ?? null));
            }
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return new JsonResponse(['id' => $item->getId()], Response::HTTP_CREATED);
    }
}

