<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240621112857 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book ADD slug VARCHAR(255)');

        // Step 2: Populate the `slug` column with default values
        $books = $this->connection->fetchAllAssociative('SELECT id FROM book');
        foreach ($books as $book) {
            $this->addSql('UPDATE book SET slug = ? WHERE id = ?', ['book-' . $book['id'], $book['id']]);
        }

        // Step 3: Alter the `slug` column to be NOT NULL
        $this->addSql('ALTER TABLE book ALTER COLUMN slug SET NOT NULL');

        // Step 4: Add a unique constraint to the `slug` column
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CBE5A331989D9B62 ON book (slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_CBE5A331989D9B62');
        $this->addSql('ALTER TABLE book DROP COLUMN slug');
    }
}
