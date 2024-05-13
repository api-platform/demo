<?php

declare(strict_types=1);

namespace App\DataFixtures\Factory;

use App\Entity\Bookmark;
use Doctrine\ORM\EntityRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

use function Zenstruck\Foundry\lazy;

/**
 * @extends ModelFactory<Bookmark>
 *
 * @method        Bookmark|Proxy                   create(array|callable $attributes = [])
 * @method static Bookmark|Proxy                   createOne(array $attributes = [])
 * @method static Bookmark|Proxy                   find(object|array|mixed $criteria)
 * @method static Bookmark|Proxy                   findOrCreate(array $attributes)
 * @method static Bookmark|Proxy                   first(string $sortedField = 'id')
 * @method static Bookmark|Proxy                   last(string $sortedField = 'id')
 * @method static Bookmark|Proxy                   random(array $attributes = [])
 * @method static Bookmark|Proxy                   randomOrCreate(array $attributes = [])
 * @method static EntityRepository|RepositoryProxy repository()
 * @method static Bookmark[]|Proxy[]               all()
 * @method static Bookmark[]|Proxy[]               createMany(int $number, array|callable $attributes = [])
 * @method static Bookmark[]|Proxy[]               createSequence(iterable|callable $sequence)
 * @method static Bookmark[]|Proxy[]               findBy(array $attributes)
 * @method static Bookmark[]|Proxy[]               randomRange(int $min, int $max, array $attributes = [])
 * @method static Bookmark[]|Proxy[]               randomSet(int $number, array $attributes = [])
 *
 * @psalm-method        Proxy<Bookmark> create(array|callable $attributes = [])
 * @psalm-method static Proxy<Bookmark> createOne(array $attributes = [])
 * @psalm-method static Proxy<Bookmark> find(object|array|mixed $criteria)
 * @psalm-method static Proxy<Bookmark> findOrCreate(array $attributes)
 * @psalm-method static Proxy<Bookmark> first(string $sortedField = 'id')
 * @psalm-method static Proxy<Bookmark> last(string $sortedField = 'id')
 * @psalm-method static Proxy<Bookmark> random(array $attributes = [])
 * @psalm-method static Proxy<Bookmark> randomOrCreate(array $attributes = [])
 * @psalm-method static RepositoryProxy<Bookmark> repository()
 * @psalm-method static list<Proxy<Bookmark>> all()
 * @psalm-method static list<Proxy<Bookmark>> createMany(int $number, array|callable $attributes = [])
 * @psalm-method static list<Proxy<Bookmark>> createSequence(iterable|callable $sequence)
 * @psalm-method static list<Proxy<Bookmark>> findBy(array $attributes)
 * @psalm-method static list<Proxy<Bookmark>> randomRange(int $min, int $max, array $attributes = [])
 * @psalm-method static list<Proxy<Bookmark>> randomSet(int $number, array $attributes = [])
 */
final class BookmarkFactory extends ModelFactory
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
            'user' => lazy(static fn () => UserFactory::new()),
            'book' => lazy(static fn () => BookFactory::new()),
            'bookmarkedAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this;
        // ->afterInstantiate(function(Bookmark $bookmark): void {})
    }

    protected static function getClass(): string
    {
        return Bookmark::class;
    }
}
