<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260325140330 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gallery_item ADD after_thumb_media_id VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE gallery_item ADD CONSTRAINT FK_8C040D92364C8A0 FOREIGN KEY (after_thumb_media_id) REFERENCES media (id)');
        $this->addSql('CREATE INDEX IDX_8C040D92364C8A0 ON gallery_item (after_thumb_media_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gallery_item DROP FOREIGN KEY FK_8C040D92364C8A0');
        $this->addSql('DROP INDEX IDX_8C040D92364C8A0 ON gallery_item');
        $this->addSql('ALTER TABLE gallery_item DROP after_thumb_media_id');
    }
}
