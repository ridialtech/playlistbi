<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250621143857 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $isSqlite = $this->connection->getDatabasePlatform()->getName() === 'sqlite';
        $auto     = $isSqlite ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'SERIAL PRIMARY KEY';
        $clob     = $isSqlite ? 'CLOB' : 'TEXT';
        $datetime = $isSqlite ? 'DATETIME' : 'TIMESTAMP(0) WITHOUT TIME ZONE';

        $this->addSql(<<<SQL
            CREATE TABLE "user" (
                id $auto NOT NULL,
                email VARCHAR(180) NOT NULL,
                roles $clob NOT NULL,
                password VARCHAR(255) NOT NULL,
                name VARCHAR(255) NOT NULL
            )
        SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)');

        $this->addSql(<<<SQL
            CREATE TABLE playlist (
                id $auto NOT NULL,
                owner_id INTEGER NOT NULL,
                title VARCHAR(255) NOT NULL,
                description $clob DEFAULT NULL,
                created_at $datetime NOT NULL,

                CONSTRAINT FK_D782112D7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id)
            )
        SQL);
        $this->addSql('CREATE INDEX IDX_D782112D7E3C61F9 ON playlist (owner_id)');

        $this->addSql(<<<SQL
            CREATE TABLE media (
                id $auto NOT NULL,
                owner_id INTEGER NOT NULL,
                playlist_id INTEGER NOT NULL,
                title VARCHAR(255) NOT NULL,
                created_at $datetime NOT NULL,
                CONSTRAINT FK_6A2CA10C7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id),
                CONSTRAINT FK_6A2CA10C6BBD148 FOREIGN KEY (playlist_id) REFERENCES playlist (id)
            )
        SQL);
        $this->addSql('CREATE INDEX IDX_6A2CA10C7E3C61F9 ON media (owner_id)');
        $this->addSql('CREATE INDEX IDX_6A2CA10C6BBD148 ON media (playlist_id)');


        $this->addSql(<<<SQL
            CREATE TABLE messenger_messages (
                id $auto NOT NULL,
                body $clob NOT NULL,
                headers $clob NOT NULL,
                queue_name VARCHAR(190) NOT NULL,
                created_at $datetime NOT NULL,
                available_at $datetime NOT NULL,
                delivered_at $datetime DEFAULT NULL

            )
        SQL);
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE media
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE playlist
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE "user"
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
