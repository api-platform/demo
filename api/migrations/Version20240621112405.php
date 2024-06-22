<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240621112405 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("ALTER TABLE book ADD promotion_status VARCHAR(255) DEFAULT 'None' NOT NULL");

        // Convert isPromoted values to promotionStatus
        $this->addSql("UPDATE book SET promotion_status = 'Basic' WHERE is_promoted = TRUE");
        $this->addSql("UPDATE book SET promotion_status = 'None' WHERE is_promoted = FALSE");

        // Drop the old column
        $this->addSql('ALTER TABLE book DROP is_promoted');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book ADD is_promoted BOOLEAN DEFAULT FALSE NOT NULL');

        // Convert promotionStatus values back to isPromoted
        $this->addSql("UPDATE book SET is_promoted = 1 WHERE promotion_status = 'Basic'");
        $this->addSql("UPDATE book SET is_promoted = 0 WHERE promotion_status = 'None'");

        // Drop the new column
        $this->addSql("ALTER TABLE book DROP promotion_status");
    }
}
