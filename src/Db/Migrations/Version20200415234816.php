<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200415234816 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE project_user_assignment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE conclusion_project_user_assignment (id INT NOT NULL, project_id UUID DEFAULT NULL, user_id INT DEFAULT NULL, role VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_65DC1EE4166D1F9C ON conclusion_project_user_assignment (project_id)');
        $this->addSql('CREATE INDEX IDX_65DC1EE4A76ED395 ON conclusion_project_user_assignment (user_id)');
        $this->addSql('COMMENT ON COLUMN conclusion_project_user_assignment.project_id IS \'(DC2Type:project_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_project_user_assignment.user_id IS \'(DC2Type:user_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_project_user_assignment.role IS \'(DC2Type:project_user_assignment_role)\'');
        $this->addSql('CREATE TABLE conclusion_template_paragraph (id INT NOT NULL, parent_id INT DEFAULT NULL, template_id UUID DEFAULT NULL, title VARCHAR(255) NOT NULL, block_kind VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5DD9D6E3727ACA70 ON conclusion_template_paragraph (parent_id)');
        $this->addSql('CREATE INDEX IDX_5DD9D6E35DA0FB8 ON conclusion_template_paragraph (template_id)');
        $this->addSql('COMMENT ON COLUMN conclusion_template_paragraph.id IS \'(DC2Type:template_paragraph_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_template_paragraph.parent_id IS \'(DC2Type:template_paragraph_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_template_paragraph.template_id IS \'(DC2Type:template_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_template_paragraph.title IS \'(DC2Type:template_paragraph_title)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_template_paragraph.block_kind IS \'(DC2Type:template_paragraph_blockkind)\'');
        $this->addSql('CREATE TABLE conclusion_template (id UUID NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN conclusion_template.id IS \'(DC2Type:template_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_template.title IS \'(DC2Type:template_title)\'');
        $this->addSql('CREATE TABLE conclusion_user_certificates (id INT NOT NULL, scope VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN conclusion_user_certificates.id IS \'(DC2Type:user_certificate_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_user_certificates.scope IS \'(DC2Type:user_certificate_scope)\'');
        $this->addSql('CREATE TABLE conclusion_user_user_certificate (certificate_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(certificate_id, user_id))');
        $this->addSql('CREATE INDEX IDX_D19EF75599223FFD ON conclusion_user_user_certificate (certificate_id)');
        $this->addSql('CREATE INDEX IDX_D19EF755A76ED395 ON conclusion_user_user_certificate (user_id)');
        $this->addSql('COMMENT ON COLUMN conclusion_user_user_certificate.certificate_id IS \'(DC2Type:user_certificate_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_user_user_certificate.user_id IS \'(DC2Type:user_id)\'');
        $this->addSql('CREATE TABLE conclusion_user (id INT NOT NULL, role VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, full_name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN conclusion_user.id IS \'(DC2Type:user_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_user.role IS \'(DC2Type:user_role)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_user.email IS \'(DC2Type:user_email)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_user.full_name IS \'(DC2Type:user_fullname)\'');
        $this->addSql('CREATE TABLE conclusion_project (id UUID NOT NULL, name VARCHAR(255) NOT NULL, state VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN conclusion_project.id IS \'(DC2Type:project_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_project.name IS \'(DC2Type:project_name)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_project.state IS \'(DC2Type:project_state)\'');
        $this->addSql('CREATE TABLE conclusion_conclusions (id UUID NOT NULL, project_id UUID NOT NULL, author_id INT DEFAULT NULL, kind VARCHAR(255) NOT NULL, revision INT NOT NULL, template_id VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_76A023AF166D1F9C ON conclusion_conclusions (project_id)');
        $this->addSql('CREATE INDEX IDX_76A023AFF675F31B ON conclusion_conclusions (author_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_76A023AF166D1F9C6D6315CC ON conclusion_conclusions (project_id, revision)');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusions.id IS \'(DC2Type:conclusion_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusions.project_id IS \'(DC2Type:project_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusions.author_id IS \'(DC2Type:user_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusions.kind IS \'(DC2Type:conclusion_kind)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusions.revision IS \'(DC2Type:conclusion_revision)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusions.template_id IS \'(DC2Type:conclusion_template_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusions.title IS \'(DC2Type:conclusion_title)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusions.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE conclusion_conclusion_paragraph (id INT NOT NULL, parent_id INT DEFAULT NULL, conclusion_id UUID DEFAULT NULL, title VARCHAR(255) NOT NULL, sort_order INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FE866E06727ACA70 ON conclusion_conclusion_paragraph (parent_id)');
        $this->addSql('CREATE INDEX IDX_FE866E062BFE04C8 ON conclusion_conclusion_paragraph (conclusion_id)');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusion_paragraph.id IS \'(DC2Type:conclusion_paragraph_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusion_paragraph.parent_id IS \'(DC2Type:conclusion_paragraph_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusion_paragraph.conclusion_id IS \'(DC2Type:conclusion_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusion_paragraph.title IS \'(DC2Type:conclusion_paragraph_title)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusion_paragraph.sort_order IS \'(DC2Type:conclusion_paragraph_order)\'');
        $this->addSql('CREATE TABLE conclusion_conclusion_paragraph_block (id INT NOT NULL, paragraph_id INT NOT NULL, executor_id INT DEFAULT NULL, kind VARCHAR(255) NOT NULL, file_path VARCHAR(255) DEFAULT NULL, state VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8E10E6B28B50597F ON conclusion_conclusion_paragraph_block (paragraph_id)');
        $this->addSql('CREATE INDEX IDX_8E10E6B28ABD09BB ON conclusion_conclusion_paragraph_block (executor_id)');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusion_paragraph_block.id IS \'(DC2Type:conclusion_paragraph_block_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusion_paragraph_block.paragraph_id IS \'(DC2Type:conclusion_paragraph_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusion_paragraph_block.executor_id IS \'(DC2Type:user_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusion_paragraph_block.kind IS \'(DC2Type:conclusion_paragraph_block_kind)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusion_paragraph_block.file_path IS \'(DC2Type:conclusion_paragraph_block_filepath)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusion_paragraph_block.state IS \'(DC2Type:conclusion_paragraph_block_state)\'');
        $this->addSql('CREATE TABLE conclusion_conclusion_paragraph_block_dictionary (id INT NOT NULL, block_id INT DEFAULT NULL, path VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AB817AB3E9ED820C ON conclusion_conclusion_paragraph_block_dictionary (block_id)');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusion_paragraph_block_dictionary.id IS \'(DC2Type:conclusion_paragraph_block_dictionary_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusion_paragraph_block_dictionary.block_id IS \'(DC2Type:conclusion_paragraph_block_id)\'');
        $this->addSql('COMMENT ON COLUMN conclusion_conclusion_paragraph_block_dictionary.path IS \'(DC2Type:conclusion_paragraph_block_dictionary_path)\'');
        $this->addSql('ALTER TABLE conclusion_project_user_assignment ADD CONSTRAINT FK_65DC1EE4166D1F9C FOREIGN KEY (project_id) REFERENCES conclusion_project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conclusion_project_user_assignment ADD CONSTRAINT FK_65DC1EE4A76ED395 FOREIGN KEY (user_id) REFERENCES conclusion_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conclusion_template_paragraph ADD CONSTRAINT FK_5DD9D6E3727ACA70 FOREIGN KEY (parent_id) REFERENCES conclusion_template_paragraph (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conclusion_template_paragraph ADD CONSTRAINT FK_5DD9D6E35DA0FB8 FOREIGN KEY (template_id) REFERENCES conclusion_template (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conclusion_user_user_certificate ADD CONSTRAINT FK_D19EF75599223FFD FOREIGN KEY (certificate_id) REFERENCES conclusion_user_certificates (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conclusion_user_user_certificate ADD CONSTRAINT FK_D19EF755A76ED395 FOREIGN KEY (user_id) REFERENCES conclusion_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conclusion_conclusions ADD CONSTRAINT FK_76A023AF166D1F9C FOREIGN KEY (project_id) REFERENCES conclusion_project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conclusion_conclusions ADD CONSTRAINT FK_76A023AFF675F31B FOREIGN KEY (author_id) REFERENCES conclusion_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conclusion_conclusion_paragraph ADD CONSTRAINT FK_FE866E06727ACA70 FOREIGN KEY (parent_id) REFERENCES conclusion_conclusion_paragraph (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conclusion_conclusion_paragraph ADD CONSTRAINT FK_FE866E062BFE04C8 FOREIGN KEY (conclusion_id) REFERENCES conclusion_conclusions (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conclusion_conclusion_paragraph_block ADD CONSTRAINT FK_8E10E6B28B50597F FOREIGN KEY (paragraph_id) REFERENCES conclusion_conclusion_paragraph (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conclusion_conclusion_paragraph_block ADD CONSTRAINT FK_8E10E6B28ABD09BB FOREIGN KEY (executor_id) REFERENCES conclusion_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conclusion_conclusion_paragraph_block_dictionary ADD CONSTRAINT FK_AB817AB3E9ED820C FOREIGN KEY (block_id) REFERENCES conclusion_conclusion_paragraph_block (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE conclusion_template_paragraph DROP CONSTRAINT FK_5DD9D6E3727ACA70');
        $this->addSql('ALTER TABLE conclusion_template_paragraph DROP CONSTRAINT FK_5DD9D6E35DA0FB8');
        $this->addSql('ALTER TABLE conclusion_user_user_certificate DROP CONSTRAINT FK_D19EF75599223FFD');
        $this->addSql('ALTER TABLE conclusion_project_user_assignment DROP CONSTRAINT FK_65DC1EE4A76ED395');
        $this->addSql('ALTER TABLE conclusion_user_user_certificate DROP CONSTRAINT FK_D19EF755A76ED395');
        $this->addSql('ALTER TABLE conclusion_conclusions DROP CONSTRAINT FK_76A023AFF675F31B');
        $this->addSql('ALTER TABLE conclusion_conclusion_paragraph_block DROP CONSTRAINT FK_8E10E6B28ABD09BB');
        $this->addSql('ALTER TABLE conclusion_project_user_assignment DROP CONSTRAINT FK_65DC1EE4166D1F9C');
        $this->addSql('ALTER TABLE conclusion_conclusions DROP CONSTRAINT FK_76A023AF166D1F9C');
        $this->addSql('ALTER TABLE conclusion_conclusion_paragraph DROP CONSTRAINT FK_FE866E062BFE04C8');
        $this->addSql('ALTER TABLE conclusion_conclusion_paragraph DROP CONSTRAINT FK_FE866E06727ACA70');
        $this->addSql('ALTER TABLE conclusion_conclusion_paragraph_block DROP CONSTRAINT FK_8E10E6B28B50597F');
        $this->addSql('ALTER TABLE conclusion_conclusion_paragraph_block_dictionary DROP CONSTRAINT FK_AB817AB3E9ED820C');
        $this->addSql('DROP SEQUENCE project_user_assignment_id_seq CASCADE');
        $this->addSql('DROP TABLE conclusion_project_user_assignment');
        $this->addSql('DROP TABLE conclusion_template_paragraph');
        $this->addSql('DROP TABLE conclusion_template');
        $this->addSql('DROP TABLE conclusion_user_certificates');
        $this->addSql('DROP TABLE conclusion_user_user_certificate');
        $this->addSql('DROP TABLE conclusion_user');
        $this->addSql('DROP TABLE conclusion_project');
        $this->addSql('DROP TABLE conclusion_conclusions');
        $this->addSql('DROP TABLE conclusion_conclusion_paragraph');
        $this->addSql('DROP TABLE conclusion_conclusion_paragraph_block');
        $this->addSql('DROP TABLE conclusion_conclusion_paragraph_block_dictionary');
    }
}
