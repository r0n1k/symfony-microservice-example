<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200526061800 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE conclusion_pdf_signature_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE conclusion_pdf_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE conclusion_conclusion_pdf_signature (id BIGINT NOT NULL, pdf_id BIGINT DEFAULT NULL, path VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_905BED9D511FC912 ON conclusion_conclusion_pdf_signature (pdf_id)');
        $this->addSql('CREATE TABLE conclusion_conclusion_pdf (id BIGINT NOT NULL, conclusion_id UUID DEFAULT NULL, path VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5E4F05A82BFE04C8 ON conclusion_conclusion_pdf (conclusion_id)');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusion_pdf.conclusion_id IS \'(DC2Type:conclusion_id)\'');
        $this->addSql('ALTER TABLE conclusion_conclusion_pdf_signature ADD CONSTRAINT FK_905BED9D511FC912 FOREIGN KEY (pdf_id) REFERENCES conclusion_conclusion_pdf (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conclusion_conclusion_pdf ADD CONSTRAINT FK_5E4F05A82BFE04C8 FOREIGN KEY (conclusion_id) REFERENCES conclusion_conclusions (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conclusion_conclusions ADD state VARCHAR(255) DEFAULT \'default\' NOT NULL');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusions.state IS \'(DC2Type:conclusion_state)\'');
        $this->addSql('ALTER TABLE conclusion_template_paragraph ALTER sort_order SET DEFAULT 0');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE conclusion_conclusion_pdf_signature DROP CONSTRAINT FK_905BED9D511FC912');
        $this->addSql('DROP SEQUENCE conclusion_pdf_signature_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE conclusion_pdf_id_seq CASCADE');
        $this->addSql('DROP TABLE conclusion_conclusion_pdf_signature');
        $this->addSql('DROP TABLE conclusion_conclusion_pdf');
        $this->addSql('ALTER TABLE conclusion_template_paragraph ALTER sort_order DROP DEFAULT');
        $this->addSql('ALTER TABLE conclusion_conclusions DROP state');
    }
}
