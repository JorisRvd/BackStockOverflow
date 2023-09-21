<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230921123313 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993989D86650F');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398DE18E50B');
        $this->addSql('DROP INDEX IDX_F52993989D86650F ON `order`');
        $this->addSql('DROP INDEX IDX_F5299398DE18E50B ON `order`');
        $this->addSql('ALTER TABLE `order` ADD product_id INT NOT NULL, ADD user_id INT NOT NULL, DROP product_id_id, DROP user_id_id');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993984584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_F52993984584665A ON `order` (product_id)');
        $this->addSql('CREATE INDEX IDX_F5299398A76ED395 ON `order` (user_id)');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD247390DF');
        $this->addSql('DROP INDEX IDX_D34A04AD247390DF ON product');
        $this->addSql('ALTER TABLE product CHANGE product_category�_id_id product_category_id INT NOT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADBE6903FD FOREIGN KEY (product_category_id) REFERENCES product_category (id)');
        $this->addSql('CREATE INDEX IDX_D34A04ADBE6903FD ON product (product_category_id)');
        $this->addSql('ALTER TABLE shipping DROP FOREIGN KEY FK_2D1C17249D86650F');
        $this->addSql('ALTER TABLE shipping DROP FOREIGN KEY FK_2D1C1724A8C76A90');
        $this->addSql('DROP INDEX IDX_2D1C1724A8C76A90 ON shipping');
        $this->addSql('DROP INDEX IDX_2D1C17249D86650F ON shipping');
        $this->addSql('ALTER TABLE shipping ADD clients_id INT NOT NULL, ADD user_id INT NOT NULL, DROP clients_id_id, DROP user_id_id');
        $this->addSql('ALTER TABLE shipping ADD CONSTRAINT FK_2D1C1724AB014612 FOREIGN KEY (clients_id) REFERENCES clients (id)');
        $this->addSql('ALTER TABLE shipping ADD CONSTRAINT FK_2D1C1724A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_2D1C1724AB014612 ON shipping (clients_id)');
        $this->addSql('CREATE INDEX IDX_2D1C1724A76ED395 ON shipping (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993984584665A');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395');
        $this->addSql('DROP INDEX IDX_F52993984584665A ON `order`');
        $this->addSql('DROP INDEX IDX_F5299398A76ED395 ON `order`');
        $this->addSql('ALTER TABLE `order` ADD product_id_id INT NOT NULL, ADD user_id_id INT NOT NULL, DROP product_id, DROP user_id');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993989D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398DE18E50B FOREIGN KEY (product_id_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_F52993989D86650F ON `order` (user_id_id)');
        $this->addSql('CREATE INDEX IDX_F5299398DE18E50B ON `order` (product_id_id)');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADBE6903FD');
        $this->addSql('DROP INDEX IDX_D34A04ADBE6903FD ON product');
        $this->addSql('ALTER TABLE product CHANGE product_category_id product_category�_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD247390DF FOREIGN KEY (product_category�_id_id) REFERENCES product_category (id)');
        $this->addSql('CREATE INDEX IDX_D34A04AD247390DF ON product (product_category�_id_id)');
        $this->addSql('ALTER TABLE shipping DROP FOREIGN KEY FK_2D1C1724AB014612');
        $this->addSql('ALTER TABLE shipping DROP FOREIGN KEY FK_2D1C1724A76ED395');
        $this->addSql('DROP INDEX IDX_2D1C1724AB014612 ON shipping');
        $this->addSql('DROP INDEX IDX_2D1C1724A76ED395 ON shipping');
        $this->addSql('ALTER TABLE shipping ADD clients_id_id INT NOT NULL, ADD user_id_id INT NOT NULL, DROP clients_id, DROP user_id');
        $this->addSql('ALTER TABLE shipping ADD CONSTRAINT FK_2D1C17249D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE shipping ADD CONSTRAINT FK_2D1C1724A8C76A90 FOREIGN KEY (clients_id_id) REFERENCES clients (id)');
        $this->addSql('CREATE INDEX IDX_2D1C1724A8C76A90 ON shipping (clients_id_id)');
        $this->addSql('CREATE INDEX IDX_2D1C17249D86650F ON shipping (user_id_id)');
    }
}
