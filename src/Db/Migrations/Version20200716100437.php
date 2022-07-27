<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200716100437 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE conclusion_conclusion_pdf_signature DROP CONSTRAINT FK_905BED9D511FC912');
        $this->addSql('ALTER TABLE conclusion_conclusion_pdf_signature ADD CONSTRAINT FK_905BED9D511FC912 FOREIGN KEY (pdf_id) REFERENCES conclusion_conclusion_pdf (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE conclusion_conclusion_pdf_signature DROP CONSTRAINT fk_905bed9d511fc912');
        $this->addSql('ALTER TABLE conclusion_conclusion_pdf_signature ADD CONSTRAINT fk_905bed9d511fc912 FOREIGN KEY (pdf_id) REFERENCES conclusion_conclusion_pdf (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
