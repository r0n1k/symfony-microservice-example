<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200514124807 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('alter sequence conclusion_paragraph_block_dictionary_id_seq rename to paragraph_block_dictionary_id_seq');
        $this->addSql('alter sequence conclusion_paragraph_id_seq rename to paragraph_id_seq');
        $this->addSql('alter sequence conclusion_paragraph_block_id_seq rename to paragraph_block_id_seq');
        $this->addSql('alter sequence conclusion_project_dictionary_item_id_seq rename to project_dictionary_item_id_seq');

        $this->addSql('CREATE TABLE conclusion_template_certificate (id BIGINT NOT NULL, paragraph_id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_92603FDD8B50597F ON conclusion_template_certificate (paragraph_id)');
        $this->addSql('COMMENT ON COLUMN conclusion_template_certificate.paragraph_id IS \'(DC2Type:template_paragraph_id)\'');
        $this->addSql('ALTER TABLE conclusion_template_certificate ADD CONSTRAINT FK_92603FDD8B50597F FOREIGN KEY (paragraph_id) REFERENCES conclusion_template_paragraph (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
       $this->addSql('alter sequence paragraph_block_dictionary_id_seq rename to conclusion_paragraph_block_dictionary_id_seq');
       $this->addSql('alter sequence paragraph_id_seq rename to conclusion_paragraph_id_seq');
       $this->addSql('alter sequence paragraph_block_id_seq rename to conclusion_paragraph_block_id_seq');
       $this->addSql('alter sequence project_dictionary_item_id_seq rename to conclusion_project_dictionary_item_id_seq');
        $this->addSql('DROP TABLE conclusion_template_certificate');
    }
}
