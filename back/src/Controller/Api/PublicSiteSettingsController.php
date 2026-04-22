<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\SiteSettings;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/public/site-settings', name: 'api_public_site_settings')]
final class PublicSiteSettingsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    #[Route('', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        /** @var SiteSettings|null $settings */
        $settings = $this->entityManager->getRepository(SiteSettings::class)->findOneBy([]);

        if (!$settings instanceof SiteSettings) {
            return new JsonResponse([
                'contactPhoneDisplay'    => '06 25 29 59 52',
                'contactEmail'           => 'contact@lueur-eternite.fr',
                'serviceRadiusKm'        => 15,
                'serviceAreaText'        => 'Caen et ses alentours',
                'legalZoneNotice'        => 'Prestations limitées à 15 km autour de Caen.',
                'legalEntityName'        => 'Émilie SIMON',
                'legalStatus'            => 'Entrepreneur individuel',
                'legalAddress'           => '49 rue de Condé, 14220 Thury-Harcourt-le-Hom, France',
                'legalSiren'             => '848 739 546',
                'legalSiret'             => '848 739 546 00036',
                'legalVat'               => 'TVA non applicable, article 293B du CGI',
                'publicationDirector'    => 'Émilie SIMON',
                'hostingProviderName'    => 'OVHcloud',
                'hostingProviderAddress' => '2 rue Kellermann, 59100 Roubaix, France',
                'hostingProviderUrl'     => 'https://www.ovh.com',
                'facebookLink'           => '',
                'instagramLink'          => '',
                'linkedinLink'           => '',
                'xLink'                  => '',
                'tiktokLink'             => '',
                'youtubeLink'            => '',
                'technicalConfig'        => '{}',
                'updatedAt'              => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            ]);
        }

        return new JsonResponse([
            'contactPhoneDisplay'    => $settings->getContactPhoneDisplay(),
            'contactEmail'           => $settings->getContactEmail(),
            'serviceRadiusKm'        => $settings->getServiceRadiusKm(),
            'serviceAreaText'        => $settings->getServiceAreaText(),
            'legalZoneNotice'        => $settings->getLegalZoneNotice(),
            'legalEntityName'        => $settings->getLegalEntityName(),
            'legalStatus'            => $settings->getLegalStatus(),
            'legalAddress'           => $settings->getLegalAddress(),
            'legalSiren'             => $settings->getLegalSiren(),
            'legalSiret'             => $settings->getLegalSiret(),
            'legalVat'               => $settings->getLegalVat(),
            'publicationDirector'    => $settings->getPublicationDirector(),
            'hostingProviderName'    => $settings->getHostingProviderName(),
            'hostingProviderAddress' => $settings->getHostingProviderAddress(),
            'hostingProviderUrl'     => $settings->getHostingProviderUrl(),
            'facebookLink'           => $settings->getFacebookLink(),
            'instagramLink'          => $settings->getInstagramLink(),
            'linkedinLink'           => $settings->getLinkedinLink(),
            'xLink'                  => $settings->getXLink(),
            'tiktokLink'             => $settings->getTiktokLink(),
            'youtubeLink'            => $settings->getYoutubeLink(),
            'technicalConfig'        => $settings->getTechnicalConfig(),
            'updatedAt'              => $settings->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        ]);
    }
}
