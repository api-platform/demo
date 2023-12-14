<?php

declare(strict_types=1);

namespace App\DataFixtures\Factory;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<User>
 *
 * @method        User|Proxy                       create(array|callable $attributes = [])
 * @method static User|Proxy                       createOne(array $attributes = [])
 * @method static User|Proxy                       find(object|array|mixed $criteria)
 * @method static User|Proxy                       findOrCreate(array $attributes)
 * @method static User|Proxy                       first(string $sortedField = 'id')
 * @method static User|Proxy                       last(string $sortedField = 'id')
 * @method static User|Proxy                       random(array $attributes = [])
 * @method static User|Proxy                       randomOrCreate(array $attributes = [])
 * @method static EntityRepository|RepositoryProxy repository()
 * @method static User[]|Proxy[]                   all()
 * @method static User[]|Proxy[]                   createMany(int $number, array|callable $attributes = [])
 * @method static User[]|Proxy[]                   createSequence(iterable|callable $sequence)
 * @method static User[]|Proxy[]                   findBy(array $attributes)
 * @method static User[]|Proxy[]                   randomRange(int $min, int $max, array $attributes = [])
 * @method static User[]|Proxy[]                   randomSet(int $number, array $attributes = [])
 *
 * @psalm-method        Proxy<User> create(array|callable $attributes = [])
 * @psalm-method static Proxy<User> createOne(array $attributes = [])
 * @psalm-method static Proxy<User> find(object|array|mixed $criteria)
 * @psalm-method static Proxy<User> findOrCreate(array $attributes)
 * @psalm-method static Proxy<User> first(string $sortedField = 'id')
 * @psalm-method static Proxy<User> last(string $sortedField = 'id')
 * @psalm-method static Proxy<User> random(array $attributes = [])
 * @psalm-method static Proxy<User> randomOrCreate(array $attributes = [])
 * @psalm-method static RepositoryProxy<User> repository()
 * @psalm-method static list<Proxy<User>> all()
 * @psalm-method static list<Proxy<User>> createMany(int $number, array|callable $attributes = [])
 * @psalm-method static list<Proxy<User>> createSequence(iterable|callable $sequence)
 * @psalm-method static list<Proxy<User>> findBy(array $attributes)
 * @psalm-method static list<Proxy<User>> randomRange(int $min, int $max, array $attributes = [])
 * @psalm-method static list<Proxy<User>> randomSet(int $number, array $attributes = [])
 */
final class UserFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    public function __construct()
    {
        parent::__construct();
    }

    public static function createOneAdmin(array $attributes = []): Proxy|User
    {
        return self::createOne(['roles' => ['ROLE_ADMIN']] + $attributes);
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function getDefaults(): array
    {
        return [
            'sub' => Uuid::v7(),
            'email' => self::faker()->unique()->email(),
            'firstName' => self::faker()->firstName(),
            'lastName' => self::faker()->lastName(),
            'roles' => ['ROLE_USER'],
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this;
        // ->afterInstantiate(function(User $user): void {})
    }

    protected static function getClass(): string
    {
        return User::class;
    }
}
