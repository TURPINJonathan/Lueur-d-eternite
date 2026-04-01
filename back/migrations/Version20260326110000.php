<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute la table `tarif` pour gérer les tarifs depuis EasyAdmin.
 */
final class Version20260326110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add tarif table (name/description/price cents)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tarif (id VARCHAR(36) NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(1024) NOT NULL, price_cents INT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tarif');
    }
}

