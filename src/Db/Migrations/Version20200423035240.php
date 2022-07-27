<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200423035240 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE conclusion_project_dictionary_item_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE conclusion_project_dictionary_items (id INT NOT NULL, project_id UUID DEFAULT NULL, key VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_256BFA21166D1F9C ON conclusion_project_dictionary_items (project_id)');
        $this->addSql('COMMENT ON COLUMN conclusion_project_dictionary_items.project_id IS \'(DC2Type:project_id)\'');
        $this->addSql('ALTER TABLE conclusion_project_dictionary_items ADD CONSTRAINT FK_256BFA21166D1F9C FOREIGN KEY (project_id) REFERENCES conclusion_project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE conclusion_project_dictionary_item_id_seq CASCADE');
        $this->addSql('DROP TABLE conclusion_project_dictionary_items');
    }
}
