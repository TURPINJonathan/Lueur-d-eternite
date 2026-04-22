<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260421122809 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, author VARCHAR(255) NOT NULL, title VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, comment LONGTEXT NOT NULL, rate INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', approuved_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE site_settings ADD review_form_send_confirmation TINYINT(1) NOT NULL DEFAULT 1, ADD review_form_template_admin LONGTEXT NOT NULL, ADD review_form_template_user LONGTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE review');
        $this->addSql('ALTER TABLE site_settings DROP review_form_send_confirmation, DROP review_form_template_admin, DROP review_form_template_user');
    }
}
