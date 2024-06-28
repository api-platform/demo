<?php

declare(strict_types=1);

namespace App\DataFixtures\Factory;

use App\Entity\Book;
use App\Enum\BookCondition;
use App\Repository\BookRepository;
use Zenstruck\Foundry\FactoryCollection;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;
use Zenstruck\Foundry\Persistence\ProxyRepositoryDecorator;

/**
 * @method        Book|Proxy                                     create(array|callable $attributes = [])
 * @method static Book|Proxy                                     createOne(array $attributes = [])
 * @method static Book|Proxy                                     find(object|array|mixed $criteria)
 * @method static Book|Proxy                                     findOrCreate(array $attributes)
 * @method static Book|Proxy                                     first(string $sortedField = 'id')
 * @method static Book|Proxy                                     last(string $sortedField = 'id')
 * @method static Book|Proxy                                     random(array $attributes = [])
 * @method static Book|Proxy                                     randomOrCreate(array $attributes = [])
 * @method static Book[]|Proxy[]                                 all()
 * @method static Book[]|Proxy[]                                 createMany(int $number, array|callable $attributes = [])
 * @method static Book[]|Proxy[]                                 createSequence(iterable|callable $sequence)
 * @method static Book[]|Proxy[]                                 findBy(array $attributes)
 * @method static Book[]|Proxy[]                                 randomRange(int $min, int $max, array $attributes = [])
 * @method static Book[]|Proxy[]                                 randomSet(int $number, array $attributes = [])
 * @method        FactoryCollection<Book|Proxy>                  many(int $min, int|null $max = null)
 * @method        FactoryCollection<Book|Proxy>                  sequence(iterable|callable $sequence)
 * @method static ProxyRepositoryDecorator<Book, BookRepository> repository()
 *
 * @phpstan-method Book&Proxy<Book> create(array|callable $attributes = [])
 * @phpstan-method static Book&Proxy<Book> createOne(array $attributes = [])
 * @phpstan-method static Book&Proxy<Book> find(object|array|mixed $criteria)
 * @phpstan-method static Book&Proxy<Book> findOrCreate(array $attributes)
 * @phpstan-method static Book&Proxy<Book> first(string $sortedField = 'id')
 * @phpstan-method static Book&Proxy<Book> last(string $sortedField = 'id')
 * @phpstan-method static Book&Proxy<Book> random(array $attributes = [])
 * @phpstan-method static Book&Proxy<Book> randomOrCreate(array $attributes = [])
 * @phpstan-method static list<Book&Proxy<Book>> all()
 * @phpstan-method static list<Book&Proxy<Book>> createMany(int $number, array|callable $attributes = [])
 * @phpstan-method static list<Book&Proxy<Book>> createSequence(iterable|callable $sequence)
 * @phpstan-method static list<Book&Proxy<Book>> findBy(array $attributes)
 * @phpstan-method static list<Book&Proxy<Book>> randomRange(int $min, int $max, array $attributes = [])
 * @phpstan-method static list<Book&Proxy<Book>> randomSet(int $number, array $attributes = [])
 * @phpstan-method FactoryCollection<Book&Proxy<Book>> many(int $min, int|null $max = null)
 * @phpstan-method FactoryCollection<Book&Proxy<Book>> sequence(iterable|callable $sequence)
 *
 * @extends PersistentProxyObjectFactory<Book>
 */
final class BookFactory extends PersistentProxyObjectFactory
{
    private array $data;

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    public function __construct()
    {
        parent::__construct();

        $this->data = json_decode(file_get_contents(__DIR__ . '/../books.json'), true);
        shuffle($this->data);
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array
    {
        return [
            'condition' => self::faker()->randomElement(BookCondition::getCases()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (Book $book): void {
                if ($book->book && $book->title && $book->author) {
                    return;
                }

                if (!$book->book) {
                    $book->book = 'https://openlibrary.org/books/OL' . self::faker()->unique()->randomNumber(7, true) . 'M.json';
                    $book->title ??= self::faker()->text();
                    $book->author ??= self::faker()->name();

                    return;
                }

                // An Open Library book URI has been specified: try to find it in the array of books
                $data = array_filter($this->data, static function (array $datum) use ($book) {
                    return $book->book === $datum['book'];
                });
                if ($data) {
                    $datum = current($data);
                    $book->title ??= $datum['title'];
                    // A book can have no author
                    $book->author ??= $datum['author'] ?? self::faker()->name();

                    return;
                }

                // No Open Library book has been found in the array of books
                $book->title ??= self::faker()->text();
                $book->author ??= self::faker()->name();
            })
        ;
    }

    public static function class(): string
    {
        return Book::class;
    }
}
