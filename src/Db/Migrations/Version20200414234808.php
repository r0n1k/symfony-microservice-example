<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200414234808 extends AbstractMigration
{
   public function getDescription(): string
   {
      return '';
   }

   public function up(Schema $schema): void
   {
      // this up() migration is auto-generated, please modify it to your needs
      $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

      $this->addSql('CREATE SEQUENCE template_paragraph_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
      $this->addSql('CREATE SEQUENCE conclusion_paragraph_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
      $this->addSql('CREATE SEQUENCE conclusion_paragraph_block_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
      $this->addSql('CREATE SEQUENCE conclusion_paragraph_block_dictionary_id_seq INCREMENT BY 1 MINVALUE 1 START 1');

   }

   public function down(Schema $schema): void
   {
      // this down() migration is auto-generated, please modify it to your needs
      $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

      $this->addSql('CREATE SCHEMA public');
      $this->addSql('DROP SEQUENCE template_paragraph_id_seq CASCADE');
      $this->addSql('DROP SEQUENCE conclusion_paragraph_id_seq CASCADE');
      $this->addSql('DROP SEQUENCE conclusion_paragraph_block_id_seq CASCADE');
      $this->addSql('DROP SEQUENCE conclusion_paragraph_block_dictionary_id_seq CASCADE');
   }
}
