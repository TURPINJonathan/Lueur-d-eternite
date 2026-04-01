<?php

namespace App\Controller\Api;

use App\Entity\Media;
use App\Service\Media\MediaGzipStorage;
use App\Service\Media\ImageThumbnailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

#[Route('/api/admin/media', name: 'api_admin_media_upload')]
final class AdminMediaUploadController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MediaGzipStorage $mediaGzipStorage,
        private readonly ImageThumbnailer $imageThumbnailer,
    ) {
    }

    #[Route('', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile) {
            $uploadedFile = $request->files->get('image');
        }

        if (!$uploadedFile) {
            return new JsonResponse(['error' => 'Champ fichier manquant (file)'], Response::HTTP_BAD_REQUEST);
        }

        if (!($uploadedFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile)) {
            return new JsonResponse(['error' => 'Type de fichier invalide'], Response::HTTP_BAD_REQUEST);
        }

        $alt = $request->request->get('alt');
        $metadataRaw = $request->request->get('metadata');

        $metadata = null;
        if (is_string($metadataRaw) && $metadataRaw !== '') {
            try {
                $metadata = json_decode($metadataRaw, true, 512, JSON_THROW_ON_ERROR);
            } catch (\Throwable) {
                return new JsonResponse(['error' => 'metadata doit être un JSON valide'], Response::HTTP_BAD_REQUEST);
            }
        }

        $originalFilename = $uploadedFile->getClientOriginalName() ?? 'upload';
        $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION) ?: 'bin');

        // Prefer the client mime if possible; fall back to Symfony guessing.
        $mimeType = $uploadedFile->getClientMimeType() ?: $uploadedFile->getMimeType() ?: 'application/octet-stream';

        $mediaId = Uuid::v4()->toRfc4122();
        $computed = $this->mediaGzipStorage->compressToGzip($mediaId, $uploadedFile, $extension);

        $media = new Media($mediaId);
        $media
            ->setOriginalFilename($originalFilename)
            ->setStorageFilename($computed['storageFilename'])
            ->setMimeType($mimeType)
            ->setExtension($extension)
            ->setSizeOriginal($computed['originalSize'])
            ->setSizeCompressed($computed['sizeCompressed'])
            ->setSha256($computed['sha256'])
            ->setAlt(is_string($alt) && $alt !== '' ? $alt : null)
            ->setMetadata($metadata);

        $this->entityManager->persist($media);

        $thumbMedia = null;
        $thumbUrl = null;
        $thumbId = null;

        // Generate an optimized thumbnail for the front grid if possible.
        if (is_string($mimeType) && str_starts_with($mimeType, 'image/')) {
            try {
                $realPath = $uploadedFile->getRealPath();
                if ($realPath !== false) {
                    $thumb = $this->imageThumbnailer->generateWebpThumbnail($realPath, $mimeType);
                    if ($thumb) {
                        $thumbMediaId = Uuid::v4()->toRfc4122();
                        $thumbComputed = $this->mediaGzipStorage->compressFileToGzip(
                            $thumbMediaId,
                            $thumb['path'],
                            $thumb['extension'],
                        );

                        $thumbMedia = new Media($thumbMediaId);
                        $thumbMedia
                            ->setOriginalFilename($originalFilename)
                            ->setStorageFilename($thumbComputed['storageFilename'])
                            ->setMimeType($thumb['mimeType'])
                            ->setExtension($thumb['extension'])
                            ->setSizeOriginal($thumbComputed['originalSize'])
                            ->setSizeCompressed($thumbComputed['sizeCompressed'])
                            ->setSha256($thumbComputed['sha256'])
                            ->setAlt(null)
                            ->setMetadata($metadata);

                        $this->entityManager->persist($thumbMedia);
                        $thumbId = $thumbMedia->getId();
                        $thumbUrl = $this->generateUrl(
                            'api_public_media_get',
                            ['id' => $thumbId],
                            UrlGeneratorInterface::ABSOLUTE_PATH
                        );
                    }

                    @unlink($thumb['path']);
                }
            } catch (\Throwable) {
                // Thumbnail is a performance improvement, not a hard requirement.
                // If it fails, we simply won't return thumbId.
            }
        }

        $this->entityManager->flush();

        $publicUrl = $this->generateUrl(
            'api_public_media_get',
            ['id' => $media->getId()],
            UrlGeneratorInterface::ABSOLUTE_PATH
        );

        return new JsonResponse([
            'id' => $media->getId(),
            'thumbId' => $thumbId,
            'alt' => $media->getAlt(),
            'metadata' => $media->getMetadata(),
            'mimeType' => $media->getMimeType(),
            'sizeOriginal' => $media->getSizeOriginal(),
            'url' => $publicUrl,
            'thumbUrl' => $thumbUrl,
        ], Response::HTTP_CREATED);
    }
}

