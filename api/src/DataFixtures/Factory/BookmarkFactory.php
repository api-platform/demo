<?php

declare(strict_types=1);

namespace App\DataFixtures\Factory;

use App\Entity\Bookmark;
use App\Repository\BookmarkRepository;
use Zenstruck\Foundry\FactoryCollection;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;
use Zenstruck\Foundry\Persistence\ProxyRepositoryDecorator;

use function Zenstruck\Foundry\lazy;

/**
 * @method        Bookmark|Proxy                                         create(array|callable $attributes = [])
 * @method static Bookmark|Proxy                                         createOne(array $attributes = [])
 * @method static Bookmark|Proxy                                         find(object|array|mixed $criteria)
 * @method static Bookmark|Proxy                                         findOrCreate(array $attributes)
 * @method static Bookmark|Proxy                                         first(string $sortedField = 'id')
 * @method static Bookmark|Proxy                                         last(string $sortedField = 'id')
 * @method static Bookmark|Proxy                                         random(array $attributes = [])
 * @method static Bookmark|Proxy                                         randomOrCreate(array $attributes = [])
 * @method static Bookmark[]|Proxy[]                                     all()
 * @method static Bookmark[]|Proxy[]                                     createMany(int $number, array|callable $attributes = [])
 * @method static Bookmark[]|Proxy[]                                     createSequence(iterable|callable $sequence)
 * @method static Bookmark[]|Proxy[]                                     findBy(array $attributes)
 * @method static Bookmark[]|Proxy[]                                     randomRange(int $min, int $max, array $attributes = [])
 * @method static Bookmark[]|Proxy[]                                     randomSet(int $number, array $attributes = [])
 * @method        FactoryCollection<Bookmark|Proxy>                      many(int $min, int|null $max = null)
 * @method        FactoryCollection<Bookmark|Proxy>                      sequence(iterable|callable $sequence)
 * @method static ProxyRepositoryDecorator<Bookmark, BookmarkRepository> repository()
 *
 * @phpstan-method Bookmark&Proxy<Bookmark> create(array|callable $attributes = [])
 * @phpstan-method static Bookmark&Proxy<Bookmark> createOne(array $attributes = [])
 * @phpstan-method static Bookmark&Proxy<Bookmark> find(object|array|mixed $criteria)
 * @phpstan-method static Bookmark&Proxy<Bookmark> findOrCreate(array $attributes)
 * @phpstan-method static Bookmark&Proxy<Bookmark> first(string $sortedField = 'id')
 * @phpstan-method static Bookmark&Proxy<Bookmark> last(string $sortedField = 'id')
 * @phpstan-method static Bookmark&Proxy<Bookmark> random(array $attributes = [])
 * @phpstan-method static Bookmark&Proxy<Bookmark> randomOrCreate(array $attributes = [])
 * @phpstan-method static list<Bookmark&Proxy<Bookmark>> all()
 * @phpstan-method static list<Bookmark&Proxy<Bookmark>> createMany(int $number, array|callable $attributes = [])
 * @phpstan-method static list<Bookmark&Proxy<Bookmark>> createSequence(iterable|callable $sequence)
 * @phpstan-method static list<Bookmark&Proxy<Bookmark>> findBy(array $attributes)
 * @phpstan-method static list<Bookmark&Proxy<Bookmark>> randomRange(int $min, int $max, array $attributes = [])
 * @phpstan-method static list<Bookmark&Proxy<Bookmark>> randomSet(int $number, array $attributes = [])
 * @phpstan-method FactoryCollection<Bookmark&Proxy<Bookmark>> many(int $min, int|null $max = null)
 * @phpstan-method FactoryCollection<Bookmark&Proxy<Bookmark>> sequence(iterable|callable $sequence)
 *
 * @extends PersistentProxyObjectFactory<Bookmark>
 */
final class BookmarkFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array
    {
        return [
            'user' => lazy(static fn () => UserFactory::new()),
            'book' => lazy(static fn () => BookFactory::new()),
            'bookmarkedAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    public static function class(): string
    {
        return Bookmark::class;
    }
}
