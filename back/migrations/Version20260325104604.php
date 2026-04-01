<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260325104604 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE gallery_item (id VARCHAR(36) NOT NULL, kind VARCHAR(255) NOT NULL, alt VARCHAR(255) NOT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, src_media_id VARCHAR(36) DEFAULT NULL, thumb_media_id VARCHAR(36) DEFAULT NULL, before_media_id VARCHAR(36) DEFAULT NULL, after_media_id VARCHAR(36) DEFAULT NULL, INDEX IDX_8C040D9296C94D91 (src_media_id), INDEX IDX_8C040D92A6653987 (thumb_media_id), INDEX IDX_8C040D9272134896 (before_media_id), INDEX IDX_8C040D92B85A4BF4 (after_media_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE media (id VARCHAR(36) NOT NULL, original_filename VARCHAR(255) NOT NULL, storage_filename VARCHAR(255) NOT NULL, mime_type VARCHAR(100) NOT NULL, extension VARCHAR(20) NOT NULL, size_original INT NOT NULL, size_compressed INT NOT NULL, sha256 VARCHAR(64) NOT NULL, alt VARCHAR(255) DEFAULT NULL, metadata JSON DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE gallery_item ADD CONSTRAINT FK_8C040D9296C94D91 FOREIGN KEY (src_media_id) REFERENCES media (id)');
        $this->addSql('ALTER TABLE gallery_item ADD CONSTRAINT FK_8C040D92A6653987 FOREIGN KEY (thumb_media_id) REFERENCES media (id)');
        $this->addSql('ALTER TABLE gallery_item ADD CONSTRAINT FK_8C040D9272134896 FOREIGN KEY (before_media_id) REFERENCES media (id)');
        $this->addSql('ALTER TABLE gallery_item ADD CONSTRAINT FK_8C040D92B85A4BF4 FOREIGN KEY (after_media_id) REFERENCES media (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gallery_item DROP FOREIGN KEY FK_8C040D9296C94D91');
        $this->addSql('ALTER TABLE gallery_item DROP FOREIGN KEY FK_8C040D92A6653987');
        $this->addSql('ALTER TABLE gallery_item DROP FOREIGN KEY FK_8C040D9272134896');
        $this->addSql('ALTER TABLE gallery_item DROP FOREIGN KEY FK_8C040D92B85A4BF4');
        $this->addSql('DROP TABLE gallery_item');
        $this->addSql('DROP TABLE media');
    }
}
