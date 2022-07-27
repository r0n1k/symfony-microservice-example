<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200506092519 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE custom_dictionary_value_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE conclusion_dictionary ALTER value TYPE TEXT');
        $this->addSql('ALTER TABLE conclusion_dictionary ALTER value DROP DEFAULT');
        $this->addSql('ALTER TABLE conclusion_dictionary ALTER name TYPE TEXT');
        $this->addSql('ALTER TABLE conclusion_dictionary ALTER name DROP DEFAULT');
        $this->addSql('ALTER TABLE conclusion_custom_dictionary_value ADD id INT NOT NULL');
        $this->addSql('ALTER TABLE conclusion_custom_dictionary_value ALTER key TYPE TEXT');
        $this->addSql('ALTER TABLE conclusion_custom_dictionary_value ALTER key DROP DEFAULT');
        $this->addSql('ALTER TABLE conclusion_custom_dictionary_value ALTER value TYPE TEXT');
        $this->addSql('ALTER TABLE conclusion_custom_dictionary_value ALTER value DROP DEFAULT');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1BB4CD758A90ABA9E9ED820C ON conclusion_custom_dictionary_value (key, block_id)');
       $this->addSql('ALTER TABLE conclusion_custom_dictionary_value DROP CONSTRAINT conclusion_custom_dictionary_value_pkey');
       $this->addSql('ALTER TABLE conclusion_custom_dictionary_value ADD PRIMARY KEY (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE custom_dictionary_value_id_seq CASCADE');
        $this->addSql('DROP INDEX UNIQ_1BB4CD758A90ABA9E9ED820C');
        $this->addSql('DROP INDEX conclusion_custom_dictionary_value_pkey');
        $this->addSql('ALTER TABLE conclusion_custom_dictionary_value DROP id');
        $this->addSql('ALTER TABLE conclusion_custom_dictionary_value ALTER key TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE conclusion_custom_dictionary_value ALTER key DROP DEFAULT');
        $this->addSql('ALTER TABLE conclusion_custom_dictionary_value ALTER value TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE conclusion_custom_dictionary_value ALTER value DROP DEFAULT');
        $this->addSql('ALTER TABLE conclusion_custom_dictionary_value ADD PRIMARY KEY (key)');
        $this->addSql('ALTER TABLE conclusion_dictionary ALTER value TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE conclusion_dictionary ALTER value DROP DEFAULT');
        $this->addSql('ALTER TABLE conclusion_dictionary ALTER name TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE conclusion_dictionary ALTER name DROP DEFAULT');
    }
}
