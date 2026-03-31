<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260331113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Extend site_settings with legal and contact fields';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE site_settings ADD contact_email VARCHAR(255) NOT NULL DEFAULT 'contact@lueur-eternite.fr', ADD legal_entity_name VARCHAR(255) NOT NULL DEFAULT 'Émilie SIMON', ADD legal_status VARCHAR(255) NOT NULL DEFAULT 'Entrepreneur individuel', ADD legal_address VARCHAR(255) NOT NULL DEFAULT '49 rue de Condé, 14220 Thury-Harcourt-le-Hom, France', ADD legal_siren VARCHAR(64) NOT NULL DEFAULT '848 739 546', ADD legal_siret VARCHAR(64) NOT NULL DEFAULT '848 739 546 00036', ADD legal_vat VARCHAR(255) NOT NULL DEFAULT 'TVA non applicable, article 293B du CGI', ADD publication_director VARCHAR(255) NOT NULL DEFAULT 'Émilie SIMON', ADD hosting_provider_name VARCHAR(255) NOT NULL DEFAULT 'OVHcloud', ADD hosting_provider_address VARCHAR(255) NOT NULL DEFAULT '2 rue Kellermann, 59100 Roubaix, France', ADD hosting_provider_url VARCHAR(255) NOT NULL DEFAULT 'https://www.ovh.com'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_settings DROP contact_email, DROP legal_entity_name, DROP legal_status, DROP legal_address, DROP legal_siren, DROP legal_siret, DROP legal_vat, DROP publication_director, DROP hosting_provider_name, DROP hosting_provider_address, DROP hosting_provider_url');
    }
}
