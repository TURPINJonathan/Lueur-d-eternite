<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute promotions et codes promo, avec liens optionnels vers tarifs.
 */
final class Version20260326120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add promotion and promo_code entities with optional tarif links';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE promotion (id VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, discount_type VARCHAR(255) NOT NULL, discount_value INT NOT NULL, starts_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ends_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE promo_code (id VARCHAR(36) NOT NULL, code VARCHAR(100) NOT NULL, name VARCHAR(255) NOT NULL, is_unique_code TINYINT(1) NOT NULL, discount_type VARCHAR(255) NOT NULL, discount_value INT NOT NULL, starts_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ends_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_7FCA9AA477153098 (code), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE promotion_tarif (promotion_id VARCHAR(36) NOT NULL, tarif_id VARCHAR(36) NOT NULL, INDEX IDX_69BE695317139B5E (promotion_id), INDEX IDX_69BE6953A29EB5D0 (tarif_id), PRIMARY KEY(promotion_id, tarif_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE promo_code_tarif (promo_code_id VARCHAR(36) NOT NULL, tarif_id VARCHAR(36) NOT NULL, INDEX IDX_DA0CADDC19E8658A (promo_code_id), INDEX IDX_DA0CADDCA29EB5D0 (tarif_id), PRIMARY KEY(promo_code_id, tarif_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE promotion_tarif ADD CONSTRAINT FK_69BE695317139B5E FOREIGN KEY (promotion_id) REFERENCES promotion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE promotion_tarif ADD CONSTRAINT FK_69BE6953A29EB5D0 FOREIGN KEY (tarif_id) REFERENCES tarif (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE promo_code_tarif ADD CONSTRAINT FK_DA0CADDC19E8658A FOREIGN KEY (promo_code_id) REFERENCES promo_code (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE promo_code_tarif ADD CONSTRAINT FK_DA0CADDCA29EB5D0 FOREIGN KEY (tarif_id) REFERENCES tarif (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE promotion_tarif DROP FOREIGN KEY FK_69BE695317139B5E');
        $this->addSql('ALTER TABLE promotion_tarif DROP FOREIGN KEY FK_69BE6953A29EB5D0');
        $this->addSql('ALTER TABLE promo_code_tarif DROP FOREIGN KEY FK_DA0CADDC19E8658A');
        $this->addSql('ALTER TABLE promo_code_tarif DROP FOREIGN KEY FK_DA0CADDCA29EB5D0');
        $this->addSql('DROP TABLE promotion');
        $this->addSql('DROP TABLE promo_code');
        $this->addSql('DROP TABLE promotion_tarif');
        $this->addSql('DROP TABLE promo_code_tarif');
    }
}
