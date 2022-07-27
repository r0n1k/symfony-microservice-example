<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200427012546 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE conclusion_dictionary (id INT NOT NULL, project_id UUID NOT NULL, block_id INT DEFAULT NULL, key VARCHAR(255) NOT NULL, value VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_71F0B3C6166D1F9C ON conclusion_dictionary (project_id)');
        $this->addSql('CREATE INDEX IDX_71F0B3C6E9ED820C ON conclusion_dictionary (block_id)');
        $this->addSql('COMMENT ON COLUMN conclusion_dictionary.id IS \'(DC2Type:conclusion_paragraph_block_dictionary_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_dictionary.project_id IS \'(DC2Type:project_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_dictionary.block_id IS \'(DC2Type:conclusion_paragraph_block_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_dictionary.key IS \'(DC2Type:conclusion_paragraph_block_dictionary_path)\'');
        $this->addSql('ALTER TABLE conclusion_dictionary ADD CONSTRAINT FK_71F0B3C6166D1F9C FOREIGN KEY (project_id) REFERENCES conclusion_project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conclusion_dictionary ADD CONSTRAINT FK_71F0B3C6E9ED820C FOREIGN KEY (block_id) REFERENCES conclusion_conclusion_paragraph_block (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE conclusion_conclusion_paragraph_block_dictionary');
        $this->addSql('DROP TABLE conclusion_project_dictionary_items');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE TABLE conclusion_conclusion_paragraph_block_dictionary (id INT NOT NULL, block_id INT DEFAULT NULL, path VARCHAR(255) NOT NULL, value VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_ab817ab3e9ed820c ON conclusion_conclusion_paragraph_block_dictionary (block_id)');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusion_paragraph_block_dictionary.id IS \'(DC2Type:conclusion_paragraph_block_dictionary_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusion_paragraph_block_dictionary.block_id IS \'(DC2Type:conclusion_paragraph_block_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusion_paragraph_block_dictionary.path IS \'(DC2Type:conclusion_paragraph_block_dictionary_path)\'');
        $this->addSql('CREATE TABLE conclusion_project_dictionary_items (id INT NOT NULL, project_id UUID DEFAULT NULL, key VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_256bfa21166d1f9c ON conclusion_project_dictionary_items (project_id)');
        $this->addSql('COMMENT ON COLUMN conclusion_project_dictionary_items.project_id IS \'(DC2Type:project_id)\'');
        $this->addSql('ALTER TABLE conclusion_conclusion_paragraph_block_dictionary ADD CONSTRAINT fk_ab817ab3e9ed820c FOREIGN KEY (block_id) REFERENCES conclusion_conclusion_paragraph_block (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conclusion_project_dictionary_items ADD CONSTRAINT fk_256bfa21166d1f9c FOREIGN KEY (project_id) REFERENCES conclusion_project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE conclusion_dictionary');
    }
}
