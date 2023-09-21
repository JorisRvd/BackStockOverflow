<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230921122035 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shipping_item DROP FOREIGN KEY FK_8EA7B31CDE18E50B');
        $this->addSql('DROP INDEX IDX_8EA7B31CDE18E50B ON shipping_item');
        $this->addSql('ALTER TABLE shipping_item CHANGE product_id_id product_id INT NOT NULL');
        $this->addSql('ALTER TABLE shipping_item ADD CONSTRAINT FK_8EA7B31C4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_8EA7B31C4584665A ON shipping_item (product_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shipping_item DROP FOREIGN KEY FK_8EA7B31C4584665A');
        $this->addSql('DROP INDEX IDX_8EA7B31C4584665A ON shipping_item');
        $this->addSql('ALTER TABLE shipping_item CHANGE product_id product_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE shipping_item ADD CONSTRAINT FK_8EA7B31CDE18E50B FOREIGN KEY (product_id_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_8EA7B31CDE18E50B ON shipping_item (product_id_id)');
    }
}
