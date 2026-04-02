<?php

namespace App\Service\Media;

use Symfony\Component\HttpFoundation\File\File;

final class MediaGzipStorage
{
    public function __construct(
        private readonly string $storageDir,
        private readonly int $gzipLevel = 9,
    ) {}

    public function ensureStorageDirExists(): void
    {
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0o775, true);
        }
    }

    /**
     * Compress the uploaded file to gzip on disk.
     *
     * @return array{storageFilename: string, sizeCompressed: int, sha256: string, originalSize: int}
     */
    public function compressToGzip(string $mediaId, File $file, string $extension): array
    {
        $realPath = $file->getRealPath();
        if (false === $realPath) {
            throw new \RuntimeException('Impossible de lire le fichier uploadé.');
        }

        return $this->compressFileToGzip($mediaId, $realPath, $extension);
    }

    /**
     * Compress a local file path to gzip on disk.
     *
     * @return array{storageFilename: string, sizeCompressed: int, sha256: string, originalSize: int}
     */
    public function compressFileToGzip(string $mediaId, string $sourcePath, string $extension): array
    {
        $this->ensureStorageDirExists();

        $storageFilename = $mediaId . '.' . $extension . '.gz';
        $destPath = rtrim($this->storageDir, '/\\') . \DIRECTORY_SEPARATOR . $storageFilename;

        $source = fopen($sourcePath, 'r');
        if (false === $source) {
            throw new \RuntimeException('Impossible d’ouvrir le fichier source.');
        }

        $gz = gzopen($destPath, 'wb' . $this->gzipLevel);
        if (false === $gz) {
            fclose($source);

            throw new \RuntimeException('Impossible de créer le fichier gzip.');
        }

        $hash = hash_init('sha256');
        $originalSize = 0;

        try {
            while (!feof($source)) {
                $chunk = fread($source, 1024 * 64);
                if (false === $chunk) {
                    throw new \RuntimeException('Erreur de lecture du fichier.');
                }
                if ('' === $chunk) {
                    continue;
                }

                $originalSize += \strlen($chunk);
                hash_update($hash, $chunk);

                $written = gzwrite($gz, $chunk);
                if (false === $written) {
                    throw new \RuntimeException('Erreur d’écriture gzip.');
                }
            }
        } finally {
            gzclose($gz);
            fclose($source);
        }

        $sizeCompressed = filesize($destPath);
        if (false === $sizeCompressed) {
            throw new \RuntimeException('Impossible de déterminer la taille du gzip.');
        }

        $sha256 = hash_final($hash);

        return [
            'storageFilename' => $storageFilename,
            'sizeCompressed'  => $sizeCompressed,
            'sha256'          => $sha256,
            'originalSize'    => $originalSize,
        ];
    }

    public function getGzipFilePath(string $storageFilename): string
    {
        return rtrim($this->storageDir, '/\\') . \DIRECTORY_SEPARATOR . $storageFilename;
    }

    public function streamDecompressed(string $gzipPath, callable $onChunk): void
    {
        $gz = gzopen($gzipPath, 'rb');
        if (false === $gz) {
            throw new \RuntimeException('Fichier compressé introuvable ou illisible.');
        }

        try {
            while (!gzeof($gz)) {
                $chunk = gzread($gz, 1024 * 64);
                if (false === $chunk || '' === $chunk) {
                    continue;
                }
                $onChunk($chunk);
            }
        } finally {
            gzclose($gz);
        }
    }
}
