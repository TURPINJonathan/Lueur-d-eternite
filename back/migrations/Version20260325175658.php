<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Aligne la table service (ex-service_card) sur le mapping Doctrine : noms d'index et de clé étrangère.
 * Corrige aussi un léger décalage sur gallery_item.visible_in_gallery.
 */
final class Version20260325175658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align service FK/index names with Doctrine mapping; gallery_item tinyint default';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE gallery_item CHANGE visible_in_gallery visible_in_gallery TINYINT NOT NULL');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY `FK_D8572C3F825F4879`');
        $this->addSql('ALTER TABLE service CHANGE picture_alt picture_alt VARCHAR(255) NOT NULL');
        $this->addSql('DROP INDEX idx_d8572c3f825f4879 ON service');
        $this->addSql('CREATE INDEX IDX_E19D9AD2825F4879 ON service (picture_media_id)');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD2825F4879 FOREIGN KEY (picture_media_id) REFERENCES media (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD2825F4879');
        $this->addSql('DROP INDEX IDX_E19D9AD2825F4879 ON service');
        $this->addSql('CREATE INDEX IDX_D8572C3F825F4879 ON service (picture_media_id)');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_D8572C3F825F4879 FOREIGN KEY (picture_media_id) REFERENCES media (id)');
        $this->addSql('ALTER TABLE service CHANGE picture_alt picture_alt VARCHAR(255) NOT NULL DEFAULT \'\'');
        $this->addSql('ALTER TABLE gallery_item CHANGE visible_in_gallery visible_in_gallery TINYINT DEFAULT 1 NOT NULL');
    }
}
