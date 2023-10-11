<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231010123253 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shipping_item ADD shipping_id INT NOT NULL');
        $this->addSql('ALTER TABLE shipping_item ADD CONSTRAINT FK_8EA7B31C4887F3F8 FOREIGN KEY (shipping_id) REFERENCES shipping (id)');
        $this->addSql('CREATE INDEX IDX_8EA7B31C4887F3F8 ON shipping_item (shipping_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shipping_item DROP FOREIGN KEY FK_8EA7B31C4887F3F8');
        $this->addSql('DROP INDEX IDX_8EA7B31C4887F3F8 ON shipping_item');
        $this->addSql('ALTER TABLE shipping_item DROP shipping_id');
    }
}
