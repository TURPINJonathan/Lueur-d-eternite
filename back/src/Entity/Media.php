<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'media')]
class Media
{
    #[ORM\Id]
    #[ORM\Column(length: 36)]
    private string $id;

    #[ORM\Column(length: 255)]
    private string $originalFilename;

    #[ORM\Column(length: 255)]
    private string $storageFilename; // gzip filename on disk

    #[ORM\Column(length: 100)]
    private string $mimeType;

    #[ORM\Column(length: 20)]
    private string $extension;

    #[ORM\Column]
    private int $sizeOriginal;

    #[ORM\Column]
    private int $sizeCompressed;

    #[ORM\Column(length: 64)]
    private string $sha256;

    #[ORM\Column(nullable: true, length: 255)]
    private ?string $alt = null;

    #[ORM\Column(nullable: true, type: 'json')]
    private ?array $metadata = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(?string $id = null)
    {
        $this->id = $id ?? Uuid::v4()->toRfc4122();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOriginalFilename(): string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(string $originalFilename): self
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function getStorageFilename(): string
    {
        return $this->storageFilename;
    }

    public function setStorageFilename(string $storageFilename): self
    {
        $this->storageFilename = $storageFilename;

        return $this;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): self
    {
        $this->extension = $extension;

        return $this;
    }

    public function getSizeOriginal(): int
    {
        return $this->sizeOriginal;
    }

    public function setSizeOriginal(int $sizeOriginal): self
    {
        $this->sizeOriginal = $sizeOriginal;

        return $this;
    }

    public function getSizeCompressed(): int
    {
        return $this->sizeCompressed;
    }

    public function setSizeCompressed(int $sizeCompressed): self
    {
        $this->sizeCompressed = $sizeCompressed;

        return $this;
    }

    public function getSha256(): string
    {
        return $this->sha256;
    }

    public function setSha256(string $sha256): self
    {
        $this->sha256 = $sha256;

        return $this;
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function setAlt(?string $alt): self
    {
        $this->alt = $alt;

        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Compression savings in percent based on stored original vs compressed sizes.
     * Example: original=1000, compressed=300 => savings=70 (%).
     */
    public function getCompressionSavingsPercent(): ?int
    {
        if ($this->sizeOriginal <= 0) {
            return null;
        }

        // savings = (1 - compressed/original) * 100
        $savings = 1 - ($this->sizeCompressed / $this->sizeOriginal);
        $percent = (int) round($savings * 100);

        return $percent;
    }
}

