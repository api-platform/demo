<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230906094949 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE book (id UUID NOT NULL, book VARCHAR(255) NOT NULL, title TEXT NOT NULL, author VARCHAR(255) DEFAULT NULL, "condition" VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CBE5A331CBE5A331 ON book (book)');
        $this->addSql('COMMENT ON COLUMN book.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE bookmark (id UUID NOT NULL, user_id UUID NOT NULL, book_id UUID NOT NULL, bookmarked_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DA62921DA76ED395 ON bookmark (user_id)');
        $this->addSql('CREATE INDEX IDX_DA62921D16A2B381 ON bookmark (book_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DA62921DA76ED39516A2B381 ON bookmark (user_id, book_id)');
        $this->addSql('COMMENT ON COLUMN bookmark.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bookmark.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bookmark.book_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bookmark.bookmarked_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE parchment (id UUID NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN parchment.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE review (id UUID NOT NULL, user_id UUID NOT NULL, book_id UUID NOT NULL, published_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, body TEXT NOT NULL, rating SMALLINT NOT NULL, letter VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_794381C6A76ED395 ON review (user_id)');
        $this->addSql('CREATE INDEX IDX_794381C616A2B381 ON review (book_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_794381C6A76ED39516A2B381 ON review (user_id, book_id)');
        $this->addSql('COMMENT ON COLUMN review.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN review.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN review.book_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN review.published_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE "user" (id UUID NOT NULL, sub UUID NOT NULL, email VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, roles JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649580282DC ON "user" (sub)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('COMMENT ON COLUMN "user".id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN "user".sub IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE bookmark ADD CONSTRAINT FK_DA62921DA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bookmark ADD CONSTRAINT FK_DA62921D16A2B381 FOREIGN KEY (book_id) REFERENCES book (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C616A2B381 FOREIGN KEY (book_id) REFERENCES book (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE bookmark DROP CONSTRAINT FK_DA62921DA76ED395');
        $this->addSql('ALTER TABLE bookmark DROP CONSTRAINT FK_DA62921D16A2B381');
        $this->addSql('ALTER TABLE review DROP CONSTRAINT FK_794381C6A76ED395');
        $this->addSql('ALTER TABLE review DROP CONSTRAINT FK_794381C616A2B381');
        $this->addSql('DROP TABLE book');
        $this->addSql('DROP TABLE bookmark');
        $this->addSql('DROP TABLE parchment');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE "user"');
    }
}
