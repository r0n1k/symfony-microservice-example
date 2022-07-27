<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200504072937 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE conclusion_custom_dictionary_value (key VARCHAR(255) NOT NULL, block_id INT DEFAULT NULL, value VARCHAR(255) NOT NULL, PRIMARY KEY(key))');
        $this->addSql('CREATE INDEX IDX_1BB4CD75E9ED820C ON conclusion_custom_dictionary_value (block_id)');
        $this->addSql('COMMENT ON COLUMN conclusion_custom_dictionary_value.block_id IS \'(DC2Type:conclusion_paragraph_block_id)\'');
        $this->addSql('ALTER TABLE conclusion_custom_dictionary_value ADD CONSTRAINT FK_1BB4CD75E9ED820C FOREIGN KEY (block_id) REFERENCES conclusion_conclusion_paragraph_block (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE conclusion_custom_dictionary_value');
    }
}
