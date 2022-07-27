<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200422033217 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE conclusion_conclusion_paragraph_block ADD html VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE conclusion_conclusion_paragraph_block ADD file_path_key VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE conclusion_conclusion_paragraph_block ALTER file_path TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE conclusion_conclusion_paragraph_block ALTER file_path DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusion_paragraph_block.file_path IS NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE conclusion_conclusion_paragraph_block DROP html');
        $this->addSql('ALTER TABLE conclusion_conclusion_paragraph_block DROP file_path_key');
        $this->addSql('ALTER TABLE conclusion_conclusion_paragraph_block ALTER file_path TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE conclusion_conclusion_paragraph_block ALTER file_path DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusion_paragraph_block.file_path IS \'(DC2Type:conclusion_paragraph_block_filepath)\'');
    }
}
