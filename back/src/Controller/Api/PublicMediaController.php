<?php

namespace App\Controller\Api;

use App\Entity\Media;
use App\Service\Media\MediaGzipStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/public/media')]
final class PublicMediaController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MediaGzipStorage $mediaGzipStorage,
    ) {}

    #[Route('/{id}', name: 'api_public_media_get', methods: ['GET'])]
    public function __invoke(string $id): StreamedResponse
    {
        $media = $this->entityManager->getRepository(Media::class)->find($id);
        if (!$media) {
            throw $this->createNotFoundException('Media introuvable.');
        }

        $gzipPath = $this->mediaGzipStorage->getGzipFilePath($media->getStorageFilename());

        $response = new StreamedResponse(function () use ($gzipPath): void {
            $this->mediaGzipStorage->streamDecompressed($gzipPath, static function (string $chunk): void {
                echo $chunk;
            });
        });

        $response->setStatusCode(200);
        $response->headers->set('Content-Type', $media->getMimeType());
        $response->headers->set('Content-Length', (string) $media->getSizeOriginal());
        $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');

        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Keep headers consistent with a binary response.
        $response->headers->set(
            'Content-Disposition',
            (new ResponseHeaderBag())->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $media->getOriginalFilename()),
        );

        return $response;
    }
}
