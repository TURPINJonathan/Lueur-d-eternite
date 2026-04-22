<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute le mode tarif "sur devis".
 */
final class Version20260422123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_quote_only column to tarif';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tarif ADD is_quote_only TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tarif DROP is_quote_only');
    }
}
