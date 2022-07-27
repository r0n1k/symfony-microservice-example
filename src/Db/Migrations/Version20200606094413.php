<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200606094413 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE ext_log_entries_id_seq CASCADE');
        $this->addSql('ALTER TABLE conclusion_conclusions ADD print_form_key VARCHAR(255) DEFAULT NULL');
        $this->addSql('DROP INDEX log_date_lookup_idx');
        $this->addSql('DROP INDEX log_version_lookup_idx');
        $this->addSql('DROP INDEX log_user_lookup_idx');
        $this->addSql('DROP INDEX log_class_lookup_idx');
        $this->addSql('ALTER TABLE conclusion_ext_log_entries ALTER action TYPE VARCHAR(32)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE ext_log_entries_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE conclusion_conclusions DROP print_form_key');
        $this->addSql('ALTER TABLE conclusion_ext_log_entries ALTER action TYPE VARCHAR(8)');
        $this->addSql('CREATE INDEX log_date_lookup_idx ON conclusion_ext_log_entries (logged_at)');
        $this->addSql('CREATE INDEX log_version_lookup_idx ON conclusion_ext_log_entries (object_id, object_class, version)');
        $this->addSql('CREATE INDEX log_user_lookup_idx ON conclusion_ext_log_entries (username)');
        $this->addSql('CREATE INDEX log_class_lookup_idx ON conclusion_ext_log_entries (object_class)');
    }
}
