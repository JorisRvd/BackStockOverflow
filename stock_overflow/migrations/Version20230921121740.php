<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230921121740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE clients (id INT AUTO_INCREMENT NOT NULL, company VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, zip_code INT NOT NULL, phone VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, product_id_id INT NOT NULL, user_id_id INT NOT NULL, date DATE NOT NULL, quantity INT NOT NULL, INDEX IDX_F5299398DE18E50B (product_id_id), INDEX IDX_F52993989D86650F (user_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, product_category�_id_id INT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, quantity INT NOT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_D34A04AD247390DF (product_category�_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, role VARCHAR(10) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE shipping (id INT AUTO_INCREMENT NOT NULL, clients_id_id INT NOT NULL, user_id_id INT NOT NULL, date DATETIME NOT NULL, INDEX IDX_2D1C1724A8C76A90 (clients_id_id), INDEX IDX_2D1C17249D86650F (user_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE shipping_shipping_item (shipping_id INT NOT NULL, shipping_item_id INT NOT NULL, INDEX IDX_7851C6DE4887F3F8 (shipping_id), INDEX IDX_7851C6DEF427549A (shipping_item_id), PRIMARY KEY(shipping_id, shipping_item_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE shipping_item (id INT AUTO_INCREMENT NOT NULL, product_id_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_8EA7B31CDE18E50B (product_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398DE18E50B FOREIGN KEY (product_id_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993989D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD247390DF FOREIGN KEY (product_category�_id_id) REFERENCES product_category (id)');
        $this->addSql('ALTER TABLE shipping ADD CONSTRAINT FK_2D1C1724A8C76A90 FOREIGN KEY (clients_id_id) REFERENCES clients (id)');
        $this->addSql('ALTER TABLE shipping ADD CONSTRAINT FK_2D1C17249D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE shipping_shipping_item ADD CONSTRAINT FK_7851C6DE4887F3F8 FOREIGN KEY (shipping_id) REFERENCES shipping (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE shipping_shipping_item ADD CONSTRAINT FK_7851C6DEF427549A FOREIGN KEY (shipping_item_id) REFERENCES shipping_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE shipping_item ADD CONSTRAINT FK_8EA7B31CDE18E50B FOREIGN KEY (product_id_id) REFERENCES product (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398DE18E50B');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993989D86650F');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD247390DF');
        $this->addSql('ALTER TABLE shipping DROP FOREIGN KEY FK_2D1C1724A8C76A90');
        $this->addSql('ALTER TABLE shipping DROP FOREIGN KEY FK_2D1C17249D86650F');
        $this->addSql('ALTER TABLE shipping_shipping_item DROP FOREIGN KEY FK_7851C6DE4887F3F8');
        $this->addSql('ALTER TABLE shipping_shipping_item DROP FOREIGN KEY FK_7851C6DEF427549A');
        $this->addSql('ALTER TABLE shipping_item DROP FOREIGN KEY FK_8EA7B31CDE18E50B');
        $this->addSql('DROP TABLE clients');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_category');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE shipping');
        $this->addSql('DROP TABLE shipping_shipping_item');
        $this->addSql('DROP TABLE shipping_item');
        $this->addSql('DROP TABLE user');
    }
}