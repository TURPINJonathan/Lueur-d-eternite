<?php

namespace App\Service\Media;

final class ImageThumbnailer
{
    public function __construct(
        private readonly int $thumbWidth = 800,
        private readonly int $thumbHeight = 500,
        private readonly int $webpQuality = 80,
    ) {}

    /**
     * @return array{path: string, extension: string, mimeType: string}|null
     */
    public function generateWebpThumbnail(string $sourcePath, string $sourceMimeType): ?array
    {
        $tmp = tempnam(sys_get_temp_dir(), 'thumb_');
        if (false === $tmp) {
            throw new \RuntimeException('Impossible de créer un fichier temporaire.');
        }
        // Ensure it has a .webp extension for image libraries that rely on it
        $thumbPath = $tmp . '.webp';
        @unlink($tmp);

        try {
            if (\extension_loaded('imagick') && class_exists('Imagick')) {
                return $this->generateWithImagick($sourcePath, $thumbPath);
            }

            if (\function_exists('imagecreatefromstring') && \function_exists('imagewebp')) {
                return $this->generateWithGd($sourcePath, $thumbPath);
            }

            return null;
        } finally {
            // keep temp file if something went wrong handled below
        }
    }

    /**
     * @return array{path: string, extension: string, mimeType: string}|null
     */
    private function generateWithImagick(string $sourcePath, string $thumbPath): ?array
    {
        $imagickClass = 'Imagick';
        $image = new $imagickClass();
        $image->readImage($sourcePath);

        $origW = $image->getImageWidth();
        $origH = $image->getImageHeight();
        if ($origW <= 0 || $origH <= 0) {
            return null;
        }

        // "cover" strategy: resize so that it fully covers target, then center-crop.
        $scale = max($this->thumbWidth / $origW, $this->thumbHeight / $origH);
        $newW = (int) max(1, round($origW * $scale));
        $newH = (int) max(1, round($origH * $scale));

        // Avoid referencing Imagick::COLORSPACE_* and Imagick::FILTER_* constants for static analysis.
        $image->resizeImage($newW, $newH, 0, 1, true);

        $x = (int) max(0, floor(($newW - $this->thumbWidth) / 2));
        $y = (int) max(0, floor(($newH - $this->thumbHeight) / 2));
        $image->cropImage($this->thumbWidth, $this->thumbHeight, $x, $y);
        $image->setImagePage(0, 0, 0, 0);

        $image->setImageFormat('webp');
        $image->setImageCompressionQuality($this->webpQuality);
        $ok = $image->writeImage($thumbPath);
        $image->clear();
        $image->destroy();

        if (!$ok || !is_file($thumbPath)) {
            return null;
        }

        return [
            'path'      => $thumbPath,
            'extension' => 'webp',
            'mimeType'  => 'image/webp',
        ];
    }

    /**
     * @return array{path: string, extension: string, mimeType: string}|null
     */
    private function generateWithGd(string $sourcePath, string $thumbPath): ?array
    {
        $raw = file_get_contents($sourcePath);
        if (false === $raw) {
            return null;
        }

        $src = imagecreatefromstring($raw);
        if (!$src) {
            return null;
        }

        $origW = imagesx($src);
        $origH = imagesy($src);
        if ($origW <= 0 || $origH <= 0) {
            imagedestroy($src);

            return null;
        }

        $scale = max($this->thumbWidth / $origW, $this->thumbHeight / $origH);
        $newW = (int) max(1, round($origW * $scale));
        $newH = (int) max(1, round($origH * $scale));

        $resized = imagecreatetruecolor($newW, $newH);
        imagealphablending($resized, true);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        $x = (int) max(0, floor(($newW - $this->thumbWidth) / 2));
        $y = (int) max(0, floor(($newH - $this->thumbHeight) / 2));

        $thumb = imagecreatetruecolor($this->thumbWidth, $this->thumbHeight);
        imagecopyresampled(
            $thumb,
            $resized,
            0,
            0,
            $x,
            $y,
            $this->thumbWidth,
            $this->thumbHeight,
            $this->thumbWidth,
            $this->thumbHeight,
        );

        $ok = imagewebp($thumb, $thumbPath, $this->webpQuality);
        imagedestroy($src);
        imagedestroy($resized);
        imagedestroy($thumb);

        if (!$ok || !is_file($thumbPath)) {
            return null;
        }

        return [
            'path'      => $thumbPath,
            'extension' => 'webp',
            'mimeType'  => 'image/webp',
        ];
    }
}
