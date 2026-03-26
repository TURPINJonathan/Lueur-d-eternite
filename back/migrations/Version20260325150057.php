<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260325150057 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE service_card (id VARCHAR(36) NOT NULL, title VARCHAR(255) NOT NULL, subtitle VARCHAR(255) NOT NULL, items JSON NOT NULL, created_at DATETIME NOT NULL, picture_media_id VARCHAR(36) DEFAULT NULL, INDEX IDX_D8572C3F825F4879 (picture_media_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE service_card ADD CONSTRAINT FK_D8572C3F825F4879 FOREIGN KEY (picture_media_id) REFERENCES media (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE service_card DROP FOREIGN KEY FK_D8572C3F825F4879');
        $this->addSql('DROP TABLE service_card');
    }
}
