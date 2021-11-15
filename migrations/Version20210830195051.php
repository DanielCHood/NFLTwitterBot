<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210830195051 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE play (id INT AUTO_INCREMENT NOT NULL, team_id INT NOT NULL, game_id INT NOT NULL, espn_id INT NOT NULL, yards INT NOT NULL, down INT NOT NULL, turnover TINYINT(1) NOT NULL, scoring_play TINYINT(1) NOT NULL, play_type VARCHAR(255) NOT NULL, text LONGTEXT NOT NULL, INDEX IDX_5E89DEBA296CD8AE (team_id), INDEX IDX_5E89DEBAE48FD905 (game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE play ADD CONSTRAINT FK_5E89DEBA296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE play ADD CONSTRAINT FK_5E89DEBAE48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE play');
    }
}
