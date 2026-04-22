<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class SiteSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    private string $contactPhoneDisplay = '06 25 29 59 52';

    #[ORM\Column(length: 255)]
    private string $contactEmail = 'contact@lueur-eternite.fr';

    #[ORM\Column(length: 255)]
    private string $contactFormRecipientEmail = 'contact@lueur-eternite.fr';

    #[ORM\Column(length: 255)]
    private string $contactFormSenderName = "Lueur d'Éternité";

    #[ORM\Column]
    private bool $contactFormSendConfirmation = true;

    #[ORM\Column(type: 'text')]
    private string $contactFormTemplateAdmin = '';

    #[ORM\Column(type: 'text')]
    private string $contactFormTemplateUser = '';

    #[ORM\Column]
    private bool $reviewFormSendConfirmation = true;

    #[ORM\Column(type: 'text')]
    private string $reviewFormTemplateAdmin = '';

    #[ORM\Column(type: 'text')]
    private string $reviewFormTemplateUser = '';

    #[ORM\Column]
    private int $serviceRadiusKm = 15;

    #[ORM\Column(length: 255)]
    private string $serviceAreaText = 'Caen et ses alentours';

    #[ORM\Column(length: 255)]
    private string $legalZoneNotice = 'Prestations limitées à 15 km autour de Caen.';

    #[ORM\Column(length: 255)]
    private string $legalEntityName = 'Émilie SIMON';

    #[ORM\Column(length: 255)]
    private string $legalStatus = 'Entrepreneur individuel';

    #[ORM\Column(length: 255)]
    private string $legalAddress = '49 rue de Condé, 14220 Thury-Harcourt-le-Hom, France';

    #[ORM\Column(length: 64)]
    private string $legalSiren = '848 739 546';

    #[ORM\Column(length: 64)]
    private string $legalSiret = '848 739 546 00036';

    #[ORM\Column(length: 255)]
    private string $legalVat = 'TVA non applicable, article 293B du CGI';

    #[ORM\Column(length: 255)]
    private string $publicationDirector = 'Émilie SIMON';

    #[ORM\Column(length: 255)]
    private string $hostingProviderName = 'OVHcloud';

    #[ORM\Column(length: 255)]
    private string $hostingProviderAddress = '2 rue Kellermann, 59100 Roubaix, France';

    #[ORM\Column(length: 255)]
    private string $hostingProviderUrl = 'https://www.ovh.com';

    #[ORM\Column(length: 512)]
    private string $facebookLink = '';

    #[ORM\Column(length: 512)]
    private string $instagramLink = '';

    #[ORM\Column(length: 512)]
    private string $linkedinLink = '';

    #[ORM\Column(length: 512)]
    private string $xLink = '';

    #[ORM\Column(length: 512)]
    private string $tiktokLink = '';

    #[ORM\Column(length: 512)]
    private string $youtubeLink = '';

    #[ORM\Column(type: 'text')]
    private string $technicalConfig = '{}';

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContactPhoneDisplay(): string
    {
        return $this->contactPhoneDisplay;
    }

    public function setContactPhoneDisplay(string $contactPhoneDisplay): self
    {
        $this->contactPhoneDisplay = $contactPhoneDisplay;

        return $this;
    }

    public function getServiceRadiusKm(): int
    {
        return $this->serviceRadiusKm;
    }

    public function setServiceRadiusKm(int $serviceRadiusKm): self
    {
        $this->serviceRadiusKm = $serviceRadiusKm;

        return $this;
    }

    public function getServiceAreaText(): string
    {
        return $this->serviceAreaText;
    }

    public function setServiceAreaText(string $serviceAreaText): self
    {
        $this->serviceAreaText = $serviceAreaText;

        return $this;
    }

    public function getLegalZoneNotice(): string
    {
        return $this->legalZoneNotice;
    }

    public function setLegalZoneNotice(string $legalZoneNotice): self
    {
        $this->legalZoneNotice = $legalZoneNotice;

        return $this;
    }

    public function getContactEmail(): string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(string $contactEmail): self
    {
        $this->contactEmail = $contactEmail;

        return $this;
    }

    public function getContactFormRecipientEmail(): string
    {
        return $this->contactFormRecipientEmail;
    }

    public function setContactFormRecipientEmail(string $contactFormRecipientEmail): self
    {
        $this->contactFormRecipientEmail = $contactFormRecipientEmail;

        return $this;
    }

    public function getContactFormSenderName(): string
    {
        return $this->contactFormSenderName;
    }

    public function setContactFormSenderName(string $contactFormSenderName): self
    {
        $this->contactFormSenderName = $contactFormSenderName;

        return $this;
    }

    public function isContactFormSendConfirmation(): bool
    {
        return $this->contactFormSendConfirmation;
    }

    public function setContactFormSendConfirmation(bool $contactFormSendConfirmation): self
    {
        $this->contactFormSendConfirmation = $contactFormSendConfirmation;

        return $this;
    }

    public function getContactFormTemplateAdmin(): string
    {
        return $this->contactFormTemplateAdmin;
    }

    public function setContactFormTemplateAdmin(string $contactFormTemplateAdmin): self
    {
        $this->contactFormTemplateAdmin = $contactFormTemplateAdmin;

        return $this;
    }

    public function getContactFormTemplateUser(): string
    {
        return $this->contactFormTemplateUser;
    }

    public function setContactFormTemplateUser(string $contactFormTemplateUser): self
    {
        $this->contactFormTemplateUser = $contactFormTemplateUser;

        return $this;
    }

    public function isReviewFormSendConfirmation(): bool
    {
        return $this->reviewFormSendConfirmation;
    }

    public function setReviewFormSendConfirmation(bool $reviewFormSendConfirmation): self
    {
        $this->reviewFormSendConfirmation = $reviewFormSendConfirmation;

        return $this;
    }

    public function getReviewFormTemplateAdmin(): string
    {
        return $this->reviewFormTemplateAdmin;
    }

    public function setReviewFormTemplateAdmin(string $reviewFormTemplateAdmin): self
    {
        $this->reviewFormTemplateAdmin = $reviewFormTemplateAdmin;

        return $this;
    }

    public function getReviewFormTemplateUser(): string
    {
        return $this->reviewFormTemplateUser;
    }

    public function setReviewFormTemplateUser(string $reviewFormTemplateUser): self
    {
        $this->reviewFormTemplateUser = $reviewFormTemplateUser;

        return $this;
    }

    public function getLegalEntityName(): string
    {
        return $this->legalEntityName;
    }

    public function setLegalEntityName(string $legalEntityName): self
    {
        $this->legalEntityName = $legalEntityName;

        return $this;
    }

    public function getLegalStatus(): string
    {
        return $this->legalStatus;
    }

    public function setLegalStatus(string $legalStatus): self
    {
        $this->legalStatus = $legalStatus;

        return $this;
    }

    public function getLegalAddress(): string
    {
        return $this->legalAddress;
    }

    public function setLegalAddress(string $legalAddress): self
    {
        $this->legalAddress = $legalAddress;

        return $this;
    }

    public function getLegalSiren(): string
    {
        return $this->legalSiren;
    }

    public function setLegalSiren(string $legalSiren): self
    {
        $this->legalSiren = $legalSiren;

        return $this;
    }

    public function getLegalSiret(): string
    {
        return $this->legalSiret;
    }

    public function setLegalSiret(string $legalSiret): self
    {
        $this->legalSiret = $legalSiret;

        return $this;
    }

    public function getLegalVat(): string
    {
        return $this->legalVat;
    }

    public function setLegalVat(string $legalVat): self
    {
        $this->legalVat = $legalVat;

        return $this;
    }

    public function getPublicationDirector(): string
    {
        return $this->publicationDirector;
    }

    public function setPublicationDirector(string $publicationDirector): self
    {
        $this->publicationDirector = $publicationDirector;

        return $this;
    }

    public function getHostingProviderName(): string
    {
        return $this->hostingProviderName;
    }

    public function setHostingProviderName(string $hostingProviderName): self
    {
        $this->hostingProviderName = $hostingProviderName;

        return $this;
    }

    public function getHostingProviderAddress(): string
    {
        return $this->hostingProviderAddress;
    }

    public function setHostingProviderAddress(string $hostingProviderAddress): self
    {
        $this->hostingProviderAddress = $hostingProviderAddress;

        return $this;
    }

    public function getHostingProviderUrl(): string
    {
        return $this->hostingProviderUrl;
    }

    public function setHostingProviderUrl(string $hostingProviderUrl): self
    {
        $this->hostingProviderUrl = $hostingProviderUrl;

        return $this;
    }

    public function getFacebookLink(): string
    {
        return $this->facebookLink;
    }

    public function setFacebookLink(?string $facebookLink): self
    {
        $this->facebookLink = $facebookLink ?? '';

        return $this;
    }

    public function getInstagramLink(): string
    {
        return $this->instagramLink;
    }

    public function setInstagramLink(?string $instagramLink): self
    {
        $this->instagramLink = $instagramLink ?? '';

        return $this;
    }

    public function getLinkedinLink(): string
    {
        return $this->linkedinLink;
    }

    public function setLinkedinLink(?string $linkedinLink): self
    {
        $this->linkedinLink = $linkedinLink ?? '';

        return $this;
    }

    public function getXLink(): string
    {
        return $this->xLink;
    }

    public function setXLink(?string $xLink): self
    {
        $this->xLink = $xLink ?? '';

        return $this;
    }

    public function getTiktokLink(): string
    {
        return $this->tiktokLink;
    }

    public function setTiktokLink(?string $tiktokLink): self
    {
        $this->tiktokLink = $tiktokLink ?? '';

        return $this;
    }

    public function getYoutubeLink(): string
    {
        return $this->youtubeLink;
    }

    public function setYoutubeLink(?string $youtubeLink): self
    {
        $this->youtubeLink = $youtubeLink ?? '';

        return $this;
    }

    public function getTechnicalConfig(): string
    {
        return $this->technicalConfig;
    }

    public function setTechnicalConfig(string $technicalConfig): self
    {
        $this->technicalConfig = $technicalConfig;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function touch(): self
    {
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }
}
