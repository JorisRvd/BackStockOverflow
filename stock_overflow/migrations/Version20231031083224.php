<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231031083224 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE shipping_product (shipping_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_350202694887F3F8 (shipping_id), INDEX IDX_350202694584665A (product_id), PRIMARY KEY(shipping_id, product_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE shipping_product ADD CONSTRAINT FK_350202694887F3F8 FOREIGN KEY (shipping_id) REFERENCES shipping (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE shipping_product ADD CONSTRAINT FK_350202694584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE shipping_item DROP FOREIGN KEY FK_8EA7B31C4584665A');
        $this->addSql('ALTER TABLE shipping_item DROP FOREIGN KEY FK_8EA7B31C4887F3F8');
        $this->addSql('DROP TABLE shipping_item');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE shipping_item (id INT AUTO_INCREMENT NOT NULL, shipping_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_8EA7B31C4584665A (product_id), INDEX IDX_8EA7B31C4887F3F8 (shipping_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE shipping_item ADD CONSTRAINT FK_8EA7B31C4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE shipping_item ADD CONSTRAINT FK_8EA7B31C4887F3F8 FOREIGN KEY (shipping_id) REFERENCES shipping (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE shipping_product DROP FOREIGN KEY FK_350202694887F3F8');
        $this->addSql('ALTER TABLE shipping_product DROP FOREIGN KEY FK_350202694584665A');
        $this->addSql('DROP TABLE shipping_product');
    }
}
