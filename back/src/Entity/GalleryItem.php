<?php

namespace App\Entity;

use App\Enum\GalleryItemKind;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'gallery_item')]
class GalleryItem
{
    #[ORM\Id]
    #[ORM\Column(length: 36)]
    private string $id;

    #[ORM\Column(enumType: GalleryItemKind::class)]
    private GalleryItemKind $kind;

    #[ORM\Column(length: 255)]
    private string $alt;

    #[ORM\Column(type: 'integer')]
    private int $position = 0;

    // single
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Media $srcMedia = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Media $thumbMedia = null;

    // compare
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Media $beforeMedia = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Media $afterMedia = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Media $afterThumbMedia = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    // Controls whether this gallery item is returned by the public API.
    #[ORM\Column(type: 'boolean')]
    private bool $visibleInGallery = true;


    // Transient file properties used by EasyAdmin uploads (not persisted by Doctrine).
    // EasyAdmin's ImageField/FileUploadType maps to "filename" strings (not UploadedFile instances).
    private ?string $srcFile = null;
    private ?string $beforeFile = null;
    private ?string $afterFile = null;

    public function __construct(?string $id = null, ?GalleryItemKind $kind = null, ?string $alt = null)
    {
        $this->id = $id ?? Uuid::v4()->toRfc4122();
        $this->kind = $kind ?? GalleryItemKind::SINGLE;
        $this->alt = $alt ?? '';
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getKind(): GalleryItemKind
    {
        return $this->kind;
    }

    public function setKind(GalleryItemKind $kind): self
    {
        $this->kind = $kind;

        return $this;
    }

    public function getAlt(): string
    {
        return $this->alt;
    }

    public function setAlt(string $alt): self
    {
        $this->alt = $alt;

        return $this;
    }

    /**
     * Helper property for EasyAdmin forms: true => compare, false => single.
     */
    public function isCompare(): bool
    {
        return $this->kind === GalleryItemKind::COMPARE;
    }

    /**
     * Helper property for EasyAdmin forms: true => compare, false => single.
     */
    public function setIsCompare(bool $isCompare): self
    {
        $this->kind = $isCompare ? GalleryItemKind::COMPARE : GalleryItemKind::SINGLE;

        return $this;
    }

    public function getSrcFile(): ?string
    {
        return $this->srcFile;
    }

    public function setSrcFile(?string $srcFile): self
    {
        $this->srcFile = $srcFile;

        return $this;
    }

    public function getBeforeFile(): ?string
    {
        return $this->beforeFile;
    }

    public function setBeforeFile(?string $beforeFile): self
    {
        $this->beforeFile = $beforeFile;

        return $this;
    }

    public function getAfterFile(): ?string
    {
        return $this->afterFile;
    }

    public function setAfterFile(?string $afterFile): self
    {
        $this->afterFile = $afterFile;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getSrcMedia(): ?Media
    {
        return $this->srcMedia;
    }

    public function setSrcMedia(?Media $srcMedia): self
    {
        $this->srcMedia = $srcMedia;

        return $this;
    }

    public function getThumbMedia(): ?Media
    {
        return $this->thumbMedia;
    }

    public function setThumbMedia(?Media $thumbMedia): self
    {
        $this->thumbMedia = $thumbMedia;

        return $this;
    }

    public function getBeforeMedia(): ?Media
    {
        return $this->beforeMedia;
    }

    public function setBeforeMedia(?Media $beforeMedia): self
    {
        $this->beforeMedia = $beforeMedia;

        return $this;
    }

    public function getAfterMedia(): ?Media
    {
        return $this->afterMedia;
    }

    public function setAfterMedia(?Media $afterMedia): self
    {
        $this->afterMedia = $afterMedia;

        return $this;
    }

    public function getAfterThumbMedia(): ?Media
    {
        return $this->afterThumbMedia;
    }

    public function setAfterThumbMedia(?Media $afterThumbMedia): self
    {
        $this->afterThumbMedia = $afterThumbMedia;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getVisibleInGallery(): bool
    {
        return $this->visibleInGallery;
    }

    public function setVisibleInGallery(bool $visibleInGallery): self
    {
        $this->visibleInGallery = $visibleInGallery;

        return $this;
    }

    /**
     * Helper for EasyAdmin list view: URL to render the thumbnail image.
     * Route: GET /api/public/media/{id}
     */
    public function getThumbUrl(): ?string
    {
        // On compare items, the UI shows avant/après instead of a single thumb.
        if ($this->kind === GalleryItemKind::COMPARE) {
            return null;
        }

        return $this->thumbMedia ? '/api/public/media/' . $this->thumbMedia->getId() : null;
    }

    /**
     * Helper for EasyAdmin compare list view: before image URL.
     * Route: GET /api/public/media/{id}
     */
    public function getBeforeSrcUrl(): ?string
    {
        return $this->beforeMedia ? '/api/public/media/' . $this->beforeMedia->getId() : null;
    }

    /**
     * Helper for EasyAdmin compare list view: after image URL.
     * Route: GET /api/public/media/{id}
     */
    public function getAfterSrcUrl(): ?string
    {
        return $this->afterMedia ? '/api/public/media/' . $this->afterMedia->getId() : null;
    }

    public function getSrcCompressionPercent(): ?int
    {
        // When compare, src is not used in the UI.
        if ($this->kind === GalleryItemKind::COMPARE) {
            return null;
        }

        return $this->srcMedia ? $this->srcMedia->getCompressionSavingsPercent() : null;
    }

    public function getBeforeCompressionPercent(): ?int
    {
        if ($this->kind === GalleryItemKind::SINGLE) {
            return null;
        }

        return $this->beforeMedia ? $this->beforeMedia->getCompressionSavingsPercent() : null;
    }

    public function getAfterCompressionPercent(): ?int
    {
        if ($this->kind === GalleryItemKind::SINGLE) {
            return null;
        }

        return $this->afterMedia ? $this->afterMedia->getCompressionSavingsPercent() : null;
    }
}

