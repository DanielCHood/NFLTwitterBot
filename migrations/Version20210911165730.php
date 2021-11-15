<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210911165730 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tweet (id INT AUTO_INCREMENT NOT NULL, play_id_id INT NOT NULL, in_reply_to_id INT DEFAULT NULL, twitter_id BIGINT NOT NULL, time DATETIME NOT NULL, INDEX IDX_3D660A3B8E9B79EB (play_id_id), INDEX IDX_3D660A3BDD92DAB8 (in_reply_to_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tweet ADD CONSTRAINT FK_3D660A3B8E9B79EB FOREIGN KEY (play_id_id) REFERENCES play (id)');
        $this->addSql('ALTER TABLE tweet ADD CONSTRAINT FK_3D660A3BDD92DAB8 FOREIGN KEY (in_reply_to_id) REFERENCES tweet (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tweet DROP FOREIGN KEY FK_3D660A3BDD92DAB8');
        $this->addSql('DROP TABLE tweet');
    }
}
