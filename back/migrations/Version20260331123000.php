<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260331123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove contact_phone_href from site_settings (computed from display phone)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_settings DROP contact_phone_href');
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE site_settings ADD contact_phone_href VARCHAR(24) NOT NULL DEFAULT '+33625295952'");
    }
}
