<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251007185733 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cms_block (id SERIAL NOT NULL, fk_cms_slot_id INT DEFAULT NULL, key VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AD680C0E6EFFB2B9 ON cms_block (fk_cms_slot_id)');
        $this->addSql('COMMENT ON COLUMN cms_block.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN cms_block.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE cms_content_item (id SERIAL NOT NULL, fk_cms_block_id INT DEFAULT NULL, key VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, data JSON DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_535A3FA1A2DE4CE8 ON cms_content_item (fk_cms_block_id)');
        $this->addSql('COMMENT ON COLUMN cms_content_item.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN cms_content_item.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE cms_slot (id SERIAL NOT NULL, key VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN cms_slot.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN cms_slot.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE cms_block ADD CONSTRAINT FK_AD680C0E6EFFB2B9 FOREIGN KEY (fk_cms_slot_id) REFERENCES cms_slot (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cms_content_item ADD CONSTRAINT FK_535A3FA1A2DE4CE8 FOREIGN KEY (fk_cms_block_id) REFERENCES cms_block (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE cms_block DROP CONSTRAINT FK_AD680C0E6EFFB2B9');
        $this->addSql('ALTER TABLE cms_content_item DROP CONSTRAINT FK_535A3FA1A2DE4CE8');
        $this->addSql('DROP TABLE cms_block');
        $this->addSql('DROP TABLE cms_content_item');
        $this->addSql('DROP TABLE cms_slot');
    }
}
