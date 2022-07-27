<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200509055816 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE conclusion_paragraph_certificates (paragraph_id INT NOT NULL, certificate_id INT NOT NULL, PRIMARY KEY(paragraph_id, certificate_id))');
        $this->addSql('CREATE INDEX IDX_B3204A628B50597F ON conclusion_paragraph_certificates (paragraph_id)');
        $this->addSql('CREATE INDEX IDX_B3204A6299223FFD ON conclusion_paragraph_certificates (certificate_id)');
        $this->addSql('COMMENT ON COLUMN conclusion_paragraph_certificates.paragraph_id IS \'(DC2Type:conclusion_paragraph_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_paragraph_certificates.certificate_id IS \'(DC2Type:user_certificate_id)\'');
        $this->addSql('ALTER TABLE conclusion_paragraph_certificates ADD CONSTRAINT FK_B3204A628B50597F FOREIGN KEY (paragraph_id) REFERENCES conclusion_conclusion_paragraph (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conclusion_paragraph_certificates ADD CONSTRAINT FK_B3204A6299223FFD FOREIGN KEY (certificate_id) REFERENCES conclusion_user_certificates (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE conclusion_paragraph_certificates');
    }
}
