<?php

declare(strict_types=1);

namespace App\DataFixtures\Factory;

use App\Entity\Review;
use Doctrine\ORM\EntityRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

use function Zenstruck\Foundry\lazy;

/**
 * @extends ModelFactory<Review>
 *
 * @method        Review|Proxy                     create(array|callable $attributes = [])
 * @method static Review|Proxy                     createOne(array $attributes = [])
 * @method static Review|Proxy                     find(object|array|mixed $criteria)
 * @method static Review|Proxy                     findOrCreate(array $attributes)
 * @method static Review|Proxy                     first(string $sortedField = 'id')
 * @method static Review|Proxy                     last(string $sortedField = 'id')
 * @method static Review|Proxy                     random(array $attributes = [])
 * @method static Review|Proxy                     randomOrCreate(array $attributes = [])
 * @method static EntityRepository|RepositoryProxy repository()
 * @method static Review[]|Proxy[]                 all()
 * @method static Review[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static Review[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static Review[]|Proxy[]                 findBy(array $attributes)
 * @method static Review[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static Review[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 *
 * @psalm-method        Proxy<Review> create(array|callable $attributes = [])
 * @psalm-method static Proxy<Review> createOne(array $attributes = [])
 * @psalm-method static Proxy<Review> find(object|array|mixed $criteria)
 * @psalm-method static Proxy<Review> findOrCreate(array $attributes)
 * @psalm-method static Proxy<Review> first(string $sortedField = 'id')
 * @psalm-method static Proxy<Review> last(string $sortedField = 'id')
 * @psalm-method static Proxy<Review> random(array $attributes = [])
 * @psalm-method static Proxy<Review> randomOrCreate(array $attributes = [])
 * @psalm-method static RepositoryProxy<Review> repository()
 * @psalm-method static list<Proxy<Review>> all()
 * @psalm-method static list<Proxy<Review>> createMany(int $number, array|callable $attributes = [])
 * @psalm-method static list<Proxy<Review>> createSequence(iterable|callable $sequence)
 * @psalm-method static list<Proxy<Review>> findBy(array $attributes)
 * @psalm-method static list<Proxy<Review>> randomRange(int $min, int $max, array $attributes = [])
 * @psalm-method static list<Proxy<Review>> randomSet(int $number, array $attributes = [])
 */
final class ReviewFactory extends ModelFactory
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
            'publishedAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime('-1 week')),
            'body' => self::faker()->text(),
            'rating' => self::faker()->numberBetween(0, 5),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this;
        // ->afterInstantiate(function(Review $review): void {})
    }

    protected static function getClass(): string
    {
        return Review::class;
    }
}
