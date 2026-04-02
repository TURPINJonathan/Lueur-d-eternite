<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute la position des tarifs et garantit son unicité.
 */
final class Version20260326113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add position column to tarif and unique index';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tarif ADD position INT NOT NULL DEFAULT 1');
        $this->addSql('SET @tarif_pos := 0');
        $this->addSql('UPDATE tarif t SET t.position = (@tarif_pos := @tarif_pos + 1) ORDER BY t.created_at ASC, t.id ASC');
        $this->addSql('ALTER TABLE tarif ADD UNIQUE INDEX UNIQ_2F2C04F7A7E86B86 (position)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tarif DROP INDEX UNIQ_2F2C04F7A7E86B86');
        $this->addSql('ALTER TABLE tarif DROP position');
    }
}
