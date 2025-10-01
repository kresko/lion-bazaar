<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250930151019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE category_url_id_seq CASCADE');
        $this->addSql('CREATE TABLE url (id SERIAL NOT NULL, fk_category INT NOT NULL, url VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F47645AE34645A1F ON url (fk_category)');
        $this->addSql('COMMENT ON COLUMN url.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE url ADD CONSTRAINT FK_F47645AE34645A1F FOREIGN KEY (fk_category) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE category_url DROP CONSTRAINT fk_d1b4e8f434645a1f');
        $this->addSql('DROP TABLE category_url');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE category_url_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE category_url (id SERIAL NOT NULL, fk_category INT NOT NULL, url VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_d1b4e8f434645a1f ON category_url (fk_category)');
        $this->addSql('COMMENT ON COLUMN category_url.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE category_url ADD CONSTRAINT fk_d1b4e8f434645a1f FOREIGN KEY (fk_category) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE url DROP CONSTRAINT FK_F47645AE34645A1F');
        $this->addSql('DROP TABLE url');
    }
}
