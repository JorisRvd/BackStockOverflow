<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231010073505 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        // $this->addSql('CREATE TABLE shipping_shipping_item (shipping_id INT NOT NULL, shipping_item_id INT NOT NULL, INDEX IDX_7851C6DE4887F3F8 (shipping_id), INDEX IDX_7851C6DEF427549A (shipping_item_id), PRIMARY KEY(shipping_id, shipping_item_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        // $this->addSql('ALTER TABLE shipping_shipping_item ADD CONSTRAINT FK_7851C6DE4887F3F8 FOREIGN KEY (shipping_id) REFERENCES shipping (id) ON DELETE CASCADE');
        // $this->addSql('ALTER TABLE shipping_shipping_item ADD CONSTRAINT FK_7851C6DEF427549A FOREIGN KEY (shipping_item_id) REFERENCES shipping_item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE shipping_item_shipping DROP FOREIGN KEY FK_B7E7AA2D4887F3F8');
        $this->addSql('ALTER TABLE shipping_item_shipping DROP FOREIGN KEY FK_B7E7AA2DF427549A');
        $this->addSql('DROP TABLE shipping_item_shipping');
        $this->addSql('ALTER TABLE `order` ADD status VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE shipping_item_shipping (shipping_item_id INT NOT NULL, shipping_id INT NOT NULL, INDEX IDX_B7E7AA2DF427549A (shipping_item_id), INDEX IDX_B7E7AA2D4887F3F8 (shipping_id), PRIMARY KEY(shipping_item_id, shipping_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE shipping_item_shipping ADD CONSTRAINT FK_B7E7AA2D4887F3F8 FOREIGN KEY (shipping_id) REFERENCES shipping (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE shipping_item_shipping ADD CONSTRAINT FK_B7E7AA2DF427549A FOREIGN KEY (shipping_item_id) REFERENCES shipping_item (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE shipping_shipping_item DROP FOREIGN KEY FK_7851C6DE4887F3F8');
        $this->addSql('ALTER TABLE shipping_shipping_item DROP FOREIGN KEY FK_7851C6DEF427549A');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE shipping_shipping_item');
        $this->addSql('ALTER TABLE `order` DROP status');
    }
}
