<?php

declare(strict_types=1);

namespace App\DataFixtures\Factory;

use App\Entity\Review;
use App\Repository\ReviewRepository;
use App\Security\Http\Protection\ResourceHandlerInterface;
use Zenstruck\Foundry\FactoryCollection;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;
use Zenstruck\Foundry\Persistence\ProxyRepositoryDecorator;

use function Zenstruck\Foundry\lazy;

/**
 * @method        Review|Proxy                                       create(array|callable $attributes = [])
 * @method static Review|Proxy                                       createOne(array $attributes = [])
 * @method static Review|Proxy                                       find(object|array|mixed $criteria)
 * @method static Review|Proxy                                       findOrCreate(array $attributes)
 * @method static Review|Proxy                                       first(string $sortedField = 'id')
 * @method static Review|Proxy                                       last(string $sortedField = 'id')
 * @method static Review|Proxy                                       random(array $attributes = [])
 * @method static Review|Proxy                                       randomOrCreate(array $attributes = [])
 * @method static Review[]|Proxy[]                                   all()
 * @method static Review[]|Proxy[]                                   createMany(int $number, array|callable $attributes = [])
 * @method static Review[]|Proxy[]                                   createSequence(iterable|callable $sequence)
 * @method static Review[]|Proxy[]                                   findBy(array $attributes)
 * @method static Review[]|Proxy[]                                   randomRange(int $min, int $max, array $attributes = [])
 * @method static Review[]|Proxy[]                                   randomSet(int $number, array $attributes = [])
 * @method        FactoryCollection<Review|Proxy>                    many(int $min, int|null $max = null)
 * @method        FactoryCollection<Review|Proxy>                    sequence(iterable|callable $sequence)
 * @method static ProxyRepositoryDecorator<Review, ReviewRepository> repository()
 *
 * @phpstan-method Review&Proxy<Review> create(array|callable $attributes = [])
 * @phpstan-method static Review&Proxy<Review> createOne(array $attributes = [])
 * @phpstan-method static Review&Proxy<Review> find(object|array|mixed $criteria)
 * @phpstan-method static Review&Proxy<Review> findOrCreate(array $attributes)
 * @phpstan-method static Review&Proxy<Review> first(string $sortedField = 'id')
 * @phpstan-method static Review&Proxy<Review> last(string $sortedField = 'id')
 * @phpstan-method static Review&Proxy<Review> random(array $attributes = [])
 * @phpstan-method static Review&Proxy<Review> randomOrCreate(array $attributes = [])
 * @phpstan-method static list<Review&Proxy<Review>> all()
 * @phpstan-method static list<Review&Proxy<Review>> createMany(int $number, array|callable $attributes = [])
 * @phpstan-method static list<Review&Proxy<Review>> createSequence(iterable|callable $sequence)
 * @phpstan-method static list<Review&Proxy<Review>> findBy(array $attributes)
 * @phpstan-method static list<Review&Proxy<Review>> randomRange(int $min, int $max, array $attributes = [])
 * @phpstan-method static list<Review&Proxy<Review>> randomSet(int $number, array $attributes = [])
 * @phpstan-method FactoryCollection<Review&Proxy<Review>> many(int $min, int|null $max = null)
 * @phpstan-method FactoryCollection<Review&Proxy<Review>> sequence(iterable|callable $sequence)
 *
 * @extends PersistentProxyObjectFactory<Review>
 */
final class ReviewFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    public function __construct(
        private readonly ?ResourceHandlerInterface $resourceHandler = null,
    ) {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array
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
    protected function initialize(): static
    {
        return $this
            // create the resource on the OIDC server
            ->afterPersist(function (Review $object): void {
                if (!$this->resourceHandler) {
                    return;
                }

                // project specification: only create resource on OIDC server for known users (john.doe and chuck.norris)
                if (\in_array($object->user?->email, ['john.doe@example.com', 'chuck.norris@example.com'], true)) {
                    $this->resourceHandler->create($object, $object->user, [
                        'operation_name' => '/books/{bookId}/reviews/{id}{._format}',
                    ]);
                }
            })
        ;
    }

    public static function class(): string
    {
        return Review::class;
    }
}
