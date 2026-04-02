<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'service')]
class Service
{
    #[ORM\Id]
    #[ORM\Column(length: 36)]
    private string $id;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(length: 255)]
    private string $subtitle;

    /**
     * Liste de prestations, une valeur par ligne (stockée comme tableau JSON).
     *
     * @var list<string>
     */
    #[ORM\Column(type: 'json')]
    private array $items = [];

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Media $pictureMedia = null;

    #[ORM\Column(length: 255)]
    private string $pictureAlt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    // Transient file property used by EasyAdmin uploads (not persisted by Doctrine).
    // EasyAdmin's ImageField maps to "filename" strings (not UploadedFile instances).
    private ?string $imageFile = null;

    public function __construct(?string $id = null, ?string $title = null, ?string $subtitle = null, ?string $pictureAlt = null)
    {
        $this->id = $id ?? Uuid::v4()->toRfc4122();
        $this->title = $title ?? '';
        $this->subtitle = $subtitle ?? '';
        $this->pictureAlt = $pictureAlt ?? '';
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSubtitle(): string
    {
        return $this->subtitle;
    }

    public function setSubtitle(string $subtitle): self
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param list<string> $items
     */
    public function setItems(array $items): self
    {
        $this->items = array_values(array_map(
            static fn (string $v): string => trim($v),
            array_filter($items, static fn ($v): bool => \is_string($v) && '' !== trim($v)),
        ));

        return $this;
    }

    /**
     * Representation for EasyAdmin: one item per line.
     */
    public function getItemsText(): string
    {
        return implode("\n", $this->items);
    }

    public function setItemsText(?string $itemsText): self
    {
        $itemsText ??= '';
        $lines = preg_split('/\r\n|\r|\n/', $itemsText) ?: [];

        return $this->setItems($lines);
    }

    public function getPictureMedia(): ?Media
    {
        return $this->pictureMedia;
    }

    public function setPictureMedia(?Media $pictureMedia): self
    {
        $this->pictureMedia = $pictureMedia;

        return $this;
    }

    public function getPictureAlt(): string
    {
        return $this->pictureAlt;
    }

    public function setPictureAlt(string $pictureAlt): self
    {
        $this->pictureAlt = $pictureAlt;

        return $this;
    }

    public function getPictureUrl(): ?string
    {
        return $this->pictureMedia ? '/api/public/media/' . $this->pictureMedia->getId() : null;
    }

    /**
     * Setter factice : l’URL d’aperçu est calculée (getPictureUrl) ; le formulaire envoie un hidden non persisté.
     */
    public function setPictureUrl(?string $pictureUrl): void {}

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getImageFile(): ?string
    {
        return $this->imageFile;
    }

    public function setImageFile(?string $imageFile): self
    {
        $this->imageFile = $imageFile;

        return $this;
    }
}
