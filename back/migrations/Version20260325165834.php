<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Renomme service_card -> service et ajoute picture_alt (sans perte de données).
 */
final class Version20260325165834 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename service_card to service, add picture_alt';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('RENAME TABLE service_card TO service');
        $this->addSql('ALTER TABLE service ADD picture_alt VARCHAR(255) NOT NULL DEFAULT \'\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE service DROP COLUMN picture_alt');
        $this->addSql('RENAME TABLE service TO service_card');
    }
}
