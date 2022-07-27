<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200514132231 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE conclusion_template_dictionary_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE conclusion_template_certificate_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE conclusion_template_dictionaries (id INT NOT NULL, paragraph_id INT NOT NULL, dictionary_key VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F11F7E9A8B50597F ON conclusion_template_dictionaries (paragraph_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F11F7E9A8B50597FCE84E1E6 ON conclusion_template_dictionaries (paragraph_id, dictionary_key)');
        $this->addSql('COMMENT ON COLUMN conclusion_template_dictionaries.paragraph_id IS \'(DC2Type:template_paragraph_id)\'');
        $this->addSql('ALTER TABLE conclusion_template_dictionaries ADD CONSTRAINT FK_F11F7E9A8B50597F FOREIGN KEY (paragraph_id) REFERENCES conclusion_template_paragraph (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE conclusion_template_dictionary_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE conclusion_template_certificate_id_seq CASCADE');
        $this->addSql('DROP TABLE conclusion_template_dictionaries');
    }
}
