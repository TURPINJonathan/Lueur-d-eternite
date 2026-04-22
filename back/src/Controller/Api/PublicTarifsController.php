<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\PromoCode;
use App\Entity\Promotion;
use App\Entity\Tarif;
use App\Enum\DiscountType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/public/tarifs', name: 'api_public_tarifs')]
final class PublicTarifsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    #[Route('', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $now = new \DateTimeImmutable();

        /** @var list<Tarif> $tarifs */
        $tarifs = $this->entityManager
            ->getRepository(Tarif::class)
            ->findBy([], ['position' => 'ASC', 'createdAt' => 'ASC']);

        /** @var list<Promotion> $promotions */
        $promotions = $this->entityManager
            ->createQueryBuilder()
            ->select('p', 't')
            ->from(Promotion::class, 'p')
            ->leftJoin('p.tarifs', 't')
            ->where('p.isActive = :isActive')
            ->andWhere('p.startsAt <= :now')
            ->andWhere('p.endsAt >= :now')
            ->setParameter('isActive', true)
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();

        /** @var list<PromoCode> $promoCodes */
        $promoCodes = $this->entityManager
            ->createQueryBuilder()
            ->select('pc', 't')
            ->from(PromoCode::class, 'pc')
            ->leftJoin('pc.tarifs', 't')
            ->where('pc.isActive = :isActive')
            ->andWhere('pc.startsAt <= :now')
            ->andWhere('pc.endsAt >= :now')
            ->setParameter('isActive', true)
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();

        $payload = array_map(static function (Tarif $tarif): array {
            return [
                'id'                 => $tarif->getId(),
                'title'              => $tarif->getTitle(),
                'details'            => $tarif->getDescription(),
                'priceCents'         => $tarif->getPriceCents(),
                'originalPriceCents' => $tarif->getPriceCents(),
                'hasDiscount'        => false,
                'discountLabel'      => null,
                'offerType'          => null,
                'offerName'          => null,
                'offerCode'          => null,
                'position'           => $tarif->getPosition(),
                'isQuoteOnly'        => $tarif->isQuoteOnly(),
            ];
        }, $tarifs);

        $computeDiscountedPrice = static function (int $basePriceCents, DiscountType $type, int $value): int {
            $discounted = $basePriceCents;

            if (DiscountType::PERCENT === $type) {
                $ratio = $value / 10000; // centi-points -> %
                $discounted = (int) round($basePriceCents * (1 - $ratio));
            } else {
                $discounted = $basePriceCents - $value;
            }

            return max(0, $discounted);
        };

        $buildDiscountLabel = static function (DiscountType $type, int $value): string {
            if (DiscountType::PERCENT === $type) {
                $whole = intdiv($value, 100);
                $dec = $value % 100;
                $percent = 0 === $dec ? (string) $whole : \sprintf('%d,%02d', $whole, $dec);

                return '-' . $percent . '%';
            }

            $euros = intdiv($value, 100);
            $cents = $value % 100;
            $amount = 0 === $cents ? (string) $euros : \sprintf('%d,%02d', $euros, $cents);

            return '-' . $amount . ' €';
        };

        $genericNotices = [];

        foreach ($promotions as $promotion) {
            if ($promotion->getTarifs()->isEmpty()) {
                $genericNotices[] = [
                    'kind'  => 'promotion',
                    'title' => $promotion->getName(),
                    'label' => $buildDiscountLabel($promotion->getDiscountType(), $promotion->getDiscountValue()),
                    'code'  => null,
                ];
            }
        }

        foreach ($promoCodes as $promoCode) {
            if ($promoCode->getTarifs()->isEmpty()) {
                $genericNotices[] = [
                    'kind'  => 'promo_code',
                    'title' => $promoCode->getName(),
                    'label' => $buildDiscountLabel($promoCode->getDiscountType(), $promoCode->getDiscountValue()),
                    'code'  => $promoCode->getCode(),
                ];
            }
        }

        // Applique la meilleure offre active ciblée (promotion ou code) sur chaque tarif.
        // Les offres génériques (sans tarifs liés) sont affichées uniquement dans genericNotices.
        foreach ($payload as $index => $item) {
            if (true === $item['isQuoteOnly']) {
                continue;
            }

            $basePrice = (int) $item['originalPriceCents'];
            $best = $basePrice;
            $bestOffer = null;

            foreach ($promotions as $promotion) {
                $targets = $promotion->getTarifs();
                if ($targets->isEmpty()) {
                    continue;
                }

                $applies = false;

                foreach ($targets as $targetTarif) {
                    if ($targetTarif->getId() === $item['id']) {
                        $applies = true;

                        break;
                    }
                }

                if (!$applies) {
                    continue;
                }

                $candidate = $computeDiscountedPrice($basePrice, $promotion->getDiscountType(), $promotion->getDiscountValue());
                if ($candidate < $best) {
                    $best = $candidate;
                    $bestOffer = [
                        'offerType'     => 'promotion',
                        'offerName'     => $promotion->getName(),
                        'offerCode'     => null,
                        'discountLabel' => $buildDiscountLabel($promotion->getDiscountType(), $promotion->getDiscountValue()),
                    ];
                }
            }

            foreach ($promoCodes as $promoCode) {
                $targets = $promoCode->getTarifs();
                if ($targets->isEmpty()) {
                    continue;
                }

                $applies = false;

                foreach ($targets as $targetTarif) {
                    if ($targetTarif->getId() === $item['id']) {
                        $applies = true;

                        break;
                    }
                }

                if (!$applies) {
                    continue;
                }

                $candidate = $computeDiscountedPrice($basePrice, $promoCode->getDiscountType(), $promoCode->getDiscountValue());
                if ($candidate < $best) {
                    $best = $candidate;
                    $bestOffer = [
                        'offerType'     => 'promo_code',
                        'offerName'     => $promoCode->getName(),
                        'offerCode'     => $promoCode->getCode(),
                        'discountLabel' => $buildDiscountLabel($promoCode->getDiscountType(), $promoCode->getDiscountValue()),
                    ];
                }
            }

            $payload[$index]['priceCents'] = $best;
            $payload[$index]['hasDiscount'] = $best < $basePrice;
            if (null !== $bestOffer) {
                $payload[$index]['offerType'] = $bestOffer['offerType'];
                $payload[$index]['offerName'] = $bestOffer['offerName'];
                $payload[$index]['offerCode'] = $bestOffer['offerCode'];
                $payload[$index]['discountLabel'] = $bestOffer['discountLabel'];
            }
        }

        return new JsonResponse([
            'items'          => $payload,
            'genericNotices' => $genericNotices,
        ]);
    }
}
