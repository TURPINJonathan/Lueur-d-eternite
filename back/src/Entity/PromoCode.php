<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\DiscountType;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'promo_code')]
class PromoCode
{
    #[ORM\Id]
    #[ORM\Column(length: 36)]
    private string $id;

    #[ORM\Column(length: 100, unique: true)]
    private string $code;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(type: 'boolean')]
    private bool $isUniqueCode = false;

    #[ORM\Column(enumType: DiscountType::class)]
    private DiscountType $discountType = DiscountType::PERCENT;

    /**
     * percent => centi-points (10,5% => 1050)
     * fixed_amount => centimes (4,99€ => 499)
     */
    #[ORM\Column(type: 'integer')]
    private int $discountValue = 0;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $startsAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $endsAt;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    /** @var Collection<int, Tarif> */
    #[ORM\ManyToMany(targetEntity: Tarif::class)]
    #[ORM\JoinTable(name: 'promo_code_tarif')]
    private Collection $tarifs;

    public function __construct(?string $id = null, ?string $code = null, ?string $name = null)
    {
        $this->id = $id ?? Uuid::v4()->toRfc4122();
        $this->code = strtoupper($code ?? '');
        $this->name = $name ?? '';
        $this->startsAt = new DateTimeImmutable();
        $this->endsAt = $this->startsAt->modify('+1 month');
        $this->createdAt = new DateTimeImmutable();
        $this->tarifs = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->code !== '' ? $this->code : $this->id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = strtoupper(trim((string) $code));

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function isUniqueCode(): bool
    {
        return $this->isUniqueCode;
    }

    public function setIsUniqueCode(bool $isUniqueCode): self
    {
        $this->isUniqueCode = $isUniqueCode;

        return $this;
    }

    public function getDiscountType(): DiscountType
    {
        return $this->discountType;
    }

    public function setDiscountType(DiscountType $discountType): self
    {
        $this->discountType = $discountType;

        return $this;
    }

    public function getDiscountValue(): int
    {
        return $this->discountValue;
    }

    public function setDiscountValue(int $discountValue): self
    {
        $this->discountValue = max(0, $discountValue);

        return $this;
    }

    public function getDiscountValueText(): string
    {
        if ($this->discountType === DiscountType::PERCENT) {
            $whole = intdiv($this->discountValue, 100);
            $dec = $this->discountValue % 100;

            return $dec === 0 ? (string) $whole : sprintf('%d,%02d', $whole, $dec);
        }

        $euros = intdiv($this->discountValue, 100);
        $cents = $this->discountValue % 100;

        return $cents === 0 ? (string) $euros : sprintf('%d,%02d', $euros, $cents);
    }

    public function setDiscountValueText(?string $raw): self
    {
        $this->discountValue = $this->parseTextValue($raw);

        return $this;
    }

    public function getStartsAt(): DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function setStartsAt(DateTimeImmutable $startsAt): self
    {
        $this->startsAt = $startsAt;

        return $this;
    }

    public function getEndsAt(): DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function setEndsAt(DateTimeImmutable $endsAt): self
    {
        $this->endsAt = $endsAt;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /** @return Collection<int, Tarif> */
    public function getTarifs(): Collection
    {
        return $this->tarifs;
    }

    public function addTarif(Tarif $tarif): self
    {
        if (!$this->tarifs->contains($tarif)) {
            $this->tarifs->add($tarif);
        }

        return $this;
    }

    public function removeTarif(Tarif $tarif): self
    {
        $this->tarifs->removeElement($tarif);

        return $this;
    }

    private function parseTextValue(?string $raw): int
    {
        $value = trim((string) $raw);
        if ($value === '') {
            return 0;
        }

        $value = str_replace([' ', "\xc2\xa0", '%', '€', 'EUR', 'euro'], '', $value);
        $value = str_replace(',', '.', $value);

        if (!is_numeric($value)) {
            throw new \RuntimeException('Valeur de réduction invalide.');
        }

        if ($this->discountType === DiscountType::PERCENT) {
            $percent = (float) $value;
            if ($percent < 0) {
                throw new \RuntimeException('Le pourcentage ne peut pas être négatif.');
            }

            return (int) round($percent * 100);
        }

        $amount = (float) $value;
        if ($amount < 0) {
            throw new \RuntimeException('Le montant ne peut pas être négatif.');
        }

        return (int) round($amount * 100);
    }
}

