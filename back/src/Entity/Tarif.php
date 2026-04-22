<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'tarif')]
class Tarif
{
    #[ORM\Id]
    #[ORM\Column(length: 36)]
    private string $id;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(length: 1024)]
    private string $description;

    /**
     * Prix stocké en centimes pour éviter les erreurs d'arrondi.
     */
    #[ORM\Column(type: 'integer')]
    private int $priceCents = 0;

    #[ORM\Column(type: 'boolean')]
    private bool $isQuoteOnly = false;

    #[ORM\Column(type: 'integer')]
    private int $position = 1;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        ?string $id = null,
        ?string $title = null,
        ?string $description = null,
        ?int $priceCents = null,
    ) {
        $this->id = $id ?? Uuid::v4()->toRfc4122();
        $this->title = $title ?? '';
        $this->description = $description ?? '';
        $this->priceCents = $priceCents ?? 0;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return '' !== $this->title ? $this->title : $this->id;
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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPriceCents(): int
    {
        return $this->priceCents;
    }

    /**
     * Représentation “saisie back-office” (convertit vers centimes).
     *
     * Permet à EasyAdmin de fournir une saisie simple du type `4.99` ou `4,99`.
     */
    public function getPriceText(): string
    {
        $negative = $this->priceCents < 0;
        $abs = abs($this->priceCents);

        $euros = intdiv($abs, 100);
        $cents = $abs % 100;

        if (0 === $cents) {
            return ($negative ? '-' : '') . (string) $euros;
        }

        return \sprintf(
            '%s%d,%02d',
            $negative ? '-' : '',
            $euros,
            $cents,
        );
    }

    /**
     * @throws \RuntimeException si le texte ne ressemble pas à un nombre
     */
    public function setPriceText(?string $priceText): self
    {
        $this->priceCents = self::parsePriceTextToCents($priceText);

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isQuoteOnly(): bool
    {
        return $this->isQuoteOnly;
    }

    public function setIsQuoteOnly(bool $isQuoteOnly): self
    {
        $this->isQuoteOnly = $isQuoteOnly;

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

    private static function parsePriceTextToCents(?string $priceText): int
    {
        if (null === $priceText) {
            return 0;
        }

        $raw = trim($priceText);
        if ('' === $raw) {
            return 0;
        }

        // Supprime les espaces (y compris NBSP) et enlève les symboles de monnaie / caractères inutiles.
        $raw = preg_replace('/[[:space:]]+/u', '', $raw) ?? '';
        $raw = str_replace(['€', 'EUR', 'euro'], '', $raw);
        $raw = preg_replace('/[^0-9,.\-+]/', '', $raw) ?? '';
        if ('' === $raw || '+' === $raw || '-' === $raw) {
            throw new \RuntimeException('Format de prix invalide.');
        }

        $negative = false;
        if (str_starts_with($raw, '-')) {
            $negative = true;
            $raw = substr($raw, 1);
        } elseif (str_starts_with($raw, '+')) {
            $raw = substr($raw, 1);
        }

        $lastComma = strrpos($raw, ',');
        $lastDot = strrpos($raw, '.');

        $decimalSep = null;
        $thousandSep = null;
        if (false !== $lastComma && false !== $lastDot) {
            if ($lastComma > $lastDot) {
                $decimalSep = ',';
                $thousandSep = '.';
            } else {
                $decimalSep = '.';
                $thousandSep = ',';
            }
        } elseif (false !== $lastComma) {
            $decimalSep = ',';
        } elseif (false !== $lastDot) {
            $decimalSep = '.';
        }

        if (null !== $thousandSep) {
            $raw = str_replace($thousandSep, '', $raw);
        }

        if (null !== $decimalSep) {
            $raw = str_replace($decimalSep, '.', $raw);
        }

        $parts = explode('.', $raw, 2);
        $intPart = $parts[0] ?? '';
        $decPart = $parts[1] ?? '';

        if ('' === $intPart || !ctype_digit($intPart)) {
            throw new \RuntimeException('Format de prix invalide.');
        }

        if ('' !== $decPart && !ctype_digit($decPart)) {
            throw new \RuntimeException('Format de prix invalide.');
        }

        $euros = (int) $intPart;

        if ('' === $decPart) {
            $cents = 0;
        } else {
            $decPartDigits = $decPart;
            $decLen = \strlen($decPartDigits);

            if (1 === $decLen) {
                $cents = ((int) $decPartDigits) * 10;
            } else {
                $firstTwo = substr($decPartDigits, 0, 2);
                $thirdDigit = $decLen >= 3 ? $decPartDigits[2] : '0';

                $cents = (int) $firstTwo;
                if ($decLen >= 3 && ((int) $thirdDigit) >= 5) {
                    ++$cents;
                }

                if ($cents >= 100) {
                    ++$euros;
                    $cents = 0;
                }
            }
        }

        $total = ($euros * 100) + $cents;

        if ($negative) {
            $total *= -1;
        }

        // (Optionnel) on empêche les prix négatifs.
        if ($total < 0) {
            throw new \RuntimeException('Le prix ne peut pas être négatif.');
        }

        return $total;
    }
}
