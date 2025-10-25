<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251025185351 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_category_category DROP CONSTRAINT fk_5b3afe912469de2');
        $this->addSql('ALTER TABLE product_category_category DROP CONSTRAINT fk_5b3afe9be6903fd');
        $this->addSql('ALTER TABLE product_category_product DROP CONSTRAINT fk_9a1e202f4584665a');
        $this->addSql('ALTER TABLE product_category_product DROP CONSTRAINT fk_9a1e202fbe6903fd');
        $this->addSql('DROP TABLE product_category_category');
        $this->addSql('DROP TABLE product_category_product');
        $this->addSql('ALTER TABLE product_category ADD fk_product_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product_category ADD fk_category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product_category ADD CONSTRAINT FK_CDFC7356B5EAACC9 FOREIGN KEY (fk_product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_category ADD CONSTRAINT FK_CDFC73567BB031D6 FOREIGN KEY (fk_category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_CDFC7356B5EAACC9 ON product_category (fk_product_id)');
        $this->addSql('CREATE INDEX IDX_CDFC73567BB031D6 ON product_category (fk_category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE TABLE product_category_category (product_category_id INT NOT NULL, category_id INT NOT NULL, PRIMARY KEY(product_category_id, category_id))');
        $this->addSql('CREATE INDEX idx_5b3afe912469de2 ON product_category_category (category_id)');
        $this->addSql('CREATE INDEX idx_5b3afe9be6903fd ON product_category_category (product_category_id)');
        $this->addSql('CREATE TABLE product_category_product (product_category_id INT NOT NULL, product_id INT NOT NULL, PRIMARY KEY(product_category_id, product_id))');
        $this->addSql('CREATE INDEX idx_9a1e202f4584665a ON product_category_product (product_id)');
        $this->addSql('CREATE INDEX idx_9a1e202fbe6903fd ON product_category_product (product_category_id)');
        $this->addSql('ALTER TABLE product_category_category ADD CONSTRAINT fk_5b3afe912469de2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_category_category ADD CONSTRAINT fk_5b3afe9be6903fd FOREIGN KEY (product_category_id) REFERENCES product_category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_category_product ADD CONSTRAINT fk_9a1e202f4584665a FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_category_product ADD CONSTRAINT fk_9a1e202fbe6903fd FOREIGN KEY (product_category_id) REFERENCES product_category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_category DROP CONSTRAINT FK_CDFC7356B5EAACC9');
        $this->addSql('ALTER TABLE product_category DROP CONSTRAINT FK_CDFC73567BB031D6');
        $this->addSql('DROP INDEX IDX_CDFC7356B5EAACC9');
        $this->addSql('DROP INDEX IDX_CDFC73567BB031D6');
        $this->addSql('ALTER TABLE product_category DROP fk_product_id');
        $this->addSql('ALTER TABLE product_category DROP fk_category_id');
    }
}
