<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260421141000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rattrapage schema review/site_settings (colonnes manquantes)';
    }

    public function up(Schema $schema): void
    {
        $schemaManager = $this->connection->createSchemaManager();

        $siteSettingsColumns = [];
        if ($schemaManager->tablesExist(['site_settings'])) {
            $siteSettingsColumns = $schemaManager->listTableColumns('site_settings');
        }

        if (!isset($siteSettingsColumns['review_form_send_confirmation'])) {
            $this->addSql('ALTER TABLE site_settings ADD review_form_send_confirmation TINYINT(1) NOT NULL DEFAULT 1');
        }
        if (!isset($siteSettingsColumns['review_form_template_admin'])) {
            $this->addSql('ALTER TABLE site_settings ADD review_form_template_admin LONGTEXT NOT NULL');
        }
        if (!isset($siteSettingsColumns['review_form_template_user'])) {
            $this->addSql('ALTER TABLE site_settings ADD review_form_template_user LONGTEXT NOT NULL');
        }

        $reviewColumns = [];
        if ($schemaManager->tablesExist(['review'])) {
            $reviewColumns = $schemaManager->listTableColumns('review');
        }

        if (isset($reviewColumns['id']) && !isset($reviewColumns['email'])) {
            $this->addSql('ALTER TABLE review ADD email VARCHAR(255) NOT NULL DEFAULT \'\'');
        }
    }

    public function down(Schema $schema): void
    {
        $schemaManager = $this->connection->createSchemaManager();

        if ($schemaManager->tablesExist(['site_settings'])) {
            $siteSettingsColumns = $schemaManager->listTableColumns('site_settings');
            if (isset($siteSettingsColumns['review_form_send_confirmation'])) {
                $this->addSql('ALTER TABLE site_settings DROP review_form_send_confirmation');
            }
            if (isset($siteSettingsColumns['review_form_template_admin'])) {
                $this->addSql('ALTER TABLE site_settings DROP review_form_template_admin');
            }
            if (isset($siteSettingsColumns['review_form_template_user'])) {
                $this->addSql('ALTER TABLE site_settings DROP review_form_template_user');
            }
        }

        if ($schemaManager->tablesExist(['review'])) {
            $reviewColumns = $schemaManager->listTableColumns('review');
            if (isset($reviewColumns['email'])) {
                $this->addSql('ALTER TABLE review DROP email');
            }
        }
    }
}
