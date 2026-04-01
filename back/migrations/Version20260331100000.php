<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260331100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add singleton table for global site settings';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE site_settings (id INT AUTO_INCREMENT NOT NULL, contact_phone_display VARCHAR(32) NOT NULL, contact_phone_href VARCHAR(24) NOT NULL, service_radius_km INT NOT NULL, service_area_text VARCHAR(255) NOT NULL, legal_zone_notice VARCHAR(255) NOT NULL, technical_config LONGTEXT NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql("INSERT INTO site_settings (contact_phone_display, contact_phone_href, service_radius_km, service_area_text, legal_zone_notice, technical_config, updated_at) VALUES ('06 25 29 59 52', '+33625295952', 15, 'Caen et ses alentours', 'Prestations limitées à 15 km autour de Caen.', '{}', NOW())");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE site_settings');
    }
}
