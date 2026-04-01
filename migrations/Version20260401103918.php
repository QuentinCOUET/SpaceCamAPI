<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260401103918 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cam (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, video_url VARCHAR(255) NOT NULL, ip_cam VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, owner_id INT DEFAULT NULL, INDEX IDX_FA35EB687E3C61F9 (owner_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE photo (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, image_url VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, cam_id INT DEFAULT NULL, INDEX IDX_14B78418CBBCB156 (cam_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, prenom VARCHAR(50) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE cam ADD CONSTRAINT FK_FA35EB687E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B78418CBBCB156 FOREIGN KEY (cam_id) REFERENCES cam (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cam DROP FOREIGN KEY FK_FA35EB687E3C61F9');
        $this->addSql('ALTER TABLE photo DROP FOREIGN KEY FK_14B78418CBBCB156');
        $this->addSql('DROP TABLE cam');
        $this->addSql('DROP TABLE photo');
        $this->addSql('DROP TABLE users');
    }
}
