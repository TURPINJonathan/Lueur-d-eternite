<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260331171000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add configurable contact form email settings';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE site_settings ADD contact_form_recipient_email VARCHAR(255) NOT NULL DEFAULT 'contact@lueur-eternite.fr', ADD contact_form_sender_name VARCHAR(255) NOT NULL DEFAULT 'Lueur d''Éternité', ADD contact_form_send_confirmation TINYINT(1) NOT NULL DEFAULT 1");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_settings DROP contact_form_recipient_email, DROP contact_form_sender_name, DROP contact_form_send_confirmation');
    }
}
