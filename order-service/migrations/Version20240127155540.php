<?php

declare(strict_types=1);

namespace OrderMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240127155540 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE orders (order_id VARCHAR(255) NOT NULL, product_id VARCHAR(255) DEFAULT NULL, quantity_ordered INTEGER NOT NULL, customer_name VARCHAR(255) NOT NULL, order_status VARCHAR(255) NOT NULL, PRIMARY KEY(order_id), CONSTRAINT FK_E52FFDEE4584665A FOREIGN KEY (product_id) REFERENCES products (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_E52FFDEE4584665A ON orders (product_id)');
        $this->addSql('CREATE TABLE products (id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE orders');
        $this->addSql('DROP TABLE products');
    }
}
