<?php

namespace App\Tests\Fixtures\Factory;

use App\Entity\Book;
use Doctrine\ORM\EntityRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Book>
 *
 * @method        Book|Proxy create(array|callable $attributes = [])
 * @method static Book|Proxy createOne(array $attributes = [])
 * @method static Book|Proxy find(object|array|mixed $criteria)
 * @method static Book|Proxy findOrCreate(array $attributes)
 * @method static Book|Proxy first(string $sortedField = 'id')
 * @method static Book|Proxy last(string $sortedField = 'id')
 * @method static Book|Proxy random(array $attributes = [])
 * @method static Book|Proxy randomOrCreate(array $attributes = [])
 * @method static EntityRepository|RepositoryProxy repository()
 * @method static Book[]|Proxy[] all()
 * @method static Book[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Book[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static Book[]|Proxy[] findBy(array $attributes)
 * @method static Book[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static Book[]|Proxy[] randomSet(int $number, array $attributes = [])
 *
 * @psalm-method        Proxy<Book> create(array|callable $attributes = [])
 * @psalm-method static Proxy<Book> createOne(array $attributes = [])
 * @psalm-method static Proxy<Book> find(object|array|mixed $criteria)
 * @psalm-method static Proxy<Book> findOrCreate(array $attributes)
 * @psalm-method static Proxy<Book> first(string $sortedField = 'id')
 * @psalm-method static Proxy<Book> last(string $sortedField = 'id')
 * @psalm-method static Proxy<Book> random(array $attributes = [])
 * @psalm-method static Proxy<Book> randomOrCreate(array $attributes = [])
 * @psalm-method static RepositoryProxy<Book> repository()
 * @psalm-method static list<Proxy<Book>> all()
 * @psalm-method static list<Proxy<Book>> createMany(int $number, array|callable $attributes = [])
 * @psalm-method static list<Proxy<Book>> createSequence(iterable|callable $sequence)
 * @psalm-method static list<Proxy<Book>> findBy(array $attributes)
 * @psalm-method static list<Proxy<Book>> randomRange(int $min, int $max, array $attributes = [])
 * @psalm-method static list<Proxy<Book>> randomSet(int $number, array $attributes = [])
 */
final class BookFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function getDefaults(): array
    {
        return [
            'isbn' => self::faker()->isbn13(),
            'title' => self::faker()->sentence(),
            'description' => self::faker()->paragraph(),
            'author' => self::faker()->name(),
            'publicationDate' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Book $book): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Book::class;
    }
}
