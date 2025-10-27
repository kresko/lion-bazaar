<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251025184606 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_category (id SERIAL NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE product_category_product (product_category_id INT NOT NULL, product_id INT NOT NULL, PRIMARY KEY(product_category_id, product_id))');
        $this->addSql('CREATE INDEX IDX_9A1E202FBE6903FD ON product_category_product (product_category_id)');
        $this->addSql('CREATE INDEX IDX_9A1E202F4584665A ON product_category_product (product_id)');
        $this->addSql('CREATE TABLE product_category_category (product_category_id INT NOT NULL, category_id INT NOT NULL, PRIMARY KEY(product_category_id, category_id))');
        $this->addSql('CREATE INDEX IDX_5B3AFE9BE6903FD ON product_category_category (product_category_id)');
        $this->addSql('CREATE INDEX IDX_5B3AFE912469DE2 ON product_category_category (category_id)');
        $this->addSql('ALTER TABLE product_category_product ADD CONSTRAINT FK_9A1E202FBE6903FD FOREIGN KEY (product_category_id) REFERENCES product_category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_category_product ADD CONSTRAINT FK_9A1E202F4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_category_category ADD CONSTRAINT FK_5B3AFE9BE6903FD FOREIGN KEY (product_category_id) REFERENCES product_category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_category_category ADD CONSTRAINT FK_5B3AFE912469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE product_category_product DROP CONSTRAINT FK_9A1E202FBE6903FD');
        $this->addSql('ALTER TABLE product_category_product DROP CONSTRAINT FK_9A1E202F4584665A');
        $this->addSql('ALTER TABLE product_category_category DROP CONSTRAINT FK_5B3AFE9BE6903FD');
        $this->addSql('ALTER TABLE product_category_category DROP CONSTRAINT FK_5B3AFE912469DE2');
        $this->addSql('DROP TABLE product_category');
        $this->addSql('DROP TABLE product_category_product');
        $this->addSql('DROP TABLE product_category_category');
    }
}
