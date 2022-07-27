<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200525020201 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE conclusion_user_user_certificate DROP CONSTRAINT FK_D19EF75599223FFD');
        $this->addSql('ALTER TABLE conclusion_user_user_certificate DROP CONSTRAINT FK_D19EF755A76ED395');
        $this->addSql('ALTER TABLE conclusion_user_user_certificate ALTER certificate_id TYPE INT');
        $this->addSql('ALTER TABLE conclusion_user_user_certificate ALTER certificate_id DROP DEFAULT');
        $this->addSql('ALTER TABLE conclusion_user_user_certificate ALTER user_id TYPE INT');
        $this->addSql('ALTER TABLE conclusion_user_user_certificate ALTER user_id DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN conclusion_user_user_certificate.certificate_id IS \'(DC2Type:user_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_user_user_certificate.user_id IS \'(DC2Type:user_certificate_id)\'');
        $this->addSql('ALTER TABLE conclusion_user_user_certificate ADD CONSTRAINT FK_D19EF75599223FFD FOREIGN KEY (certificate_id) REFERENCES conclusion_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conclusion_user_user_certificate ADD CONSTRAINT FK_D19EF755A76ED395 FOREIGN KEY (user_id) REFERENCES conclusion_user_certificates (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE conclusion_user_user_certificate DROP CONSTRAINT fk_d19ef75599223ffd');
        $this->addSql('ALTER TABLE conclusion_user_user_certificate DROP CONSTRAINT fk_d19ef755a76ed395');
        $this->addSql('ALTER TABLE conclusion_user_user_certificate ALTER certificate_id TYPE INT');
        $this->addSql('ALTER TABLE conclusion_user_user_certificate ALTER certificate_id DROP DEFAULT');
        $this->addSql('ALTER TABLE conclusion_user_user_certificate ALTER user_id TYPE INT');
        $this->addSql('ALTER TABLE conclusion_user_user_certificate ALTER user_id DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN conclusion_user_user_certificate.certificate_id IS \'(DC2Type:user_certificate_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_user_user_certificate.user_id IS \'(DC2Type:user_id)\'');
        $this->addSql('ALTER TABLE conclusion_user_user_certificate ADD CONSTRAINT fk_d19ef75599223ffd FOREIGN KEY (certificate_id) REFERENCES conclusion_user_certificates (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conclusion_user_user_certificate ADD CONSTRAINT fk_d19ef755a76ed395 FOREIGN KEY (user_id) REFERENCES conclusion_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
