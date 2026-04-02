<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260402120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add social network URL fields to site_settings';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_settings ADD facebook_link VARCHAR(512) NOT NULL DEFAULT \'\'');
        $this->addSql('ALTER TABLE site_settings ADD instagram_link VARCHAR(512) NOT NULL DEFAULT \'\'');
        $this->addSql('ALTER TABLE site_settings ADD linkedin_link VARCHAR(512) NOT NULL DEFAULT \'\'');
        $this->addSql('ALTER TABLE site_settings ADD x_link VARCHAR(512) NOT NULL DEFAULT \'\'');
        $this->addSql('ALTER TABLE site_settings ADD tiktok_link VARCHAR(512) NOT NULL DEFAULT \'\'');
        $this->addSql('ALTER TABLE site_settings ADD youtube_link VARCHAR(512) NOT NULL DEFAULT \'\'');
        $this->addSql("UPDATE site_settings SET instagram_link = 'https://www.instagram.com/lueur.d.eternite?igsh=bHdvbWZobHZlaTd5' WHERE instagram_link = ''");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_settings DROP facebook_link, DROP instagram_link, DROP linkedin_link, DROP x_link, DROP tiktok_link, DROP youtube_link');
    }
}
