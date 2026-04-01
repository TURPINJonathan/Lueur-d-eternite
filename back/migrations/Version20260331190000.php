<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260331190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add editable email templates in site_settings';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE site_settings ADD contact_form_template_admin LONGTEXT NOT NULL, ADD contact_form_template_user LONGTEXT NOT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_settings DROP contact_form_template_admin, DROP contact_form_template_user');
    }
}
