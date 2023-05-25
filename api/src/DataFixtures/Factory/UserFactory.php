<?php

namespace App\DataFixtures\Factory;

use App\Entity\User;
use App\Repository\UserRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<User>
 *
 * @method        User|Proxy create(array|callable $attributes = [])
 * @method static User|Proxy createOne(array $attributes = [])
 * @method static User|Proxy find(object|array|mixed $criteria)
 * @method static User|Proxy findOrCreate(array $attributes)
 * @method static User|Proxy first(string $sortedField = 'id')
 * @method static User|Proxy last(string $sortedField = 'id')
 * @method static User|Proxy random(array $attributes = [])
 * @method static User|Proxy randomOrCreate(array $attributes = [])
 * @method static UserRepository|RepositoryProxy repository()
 * @method static User[]|Proxy[] all()
 * @method static User[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static User[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static User[]|Proxy[] findBy(array $attributes)
 * @method static User[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static User[]|Proxy[] randomSet(int $number, array $attributes = [])
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

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function getDefaults(): array
    {
        return [
            'email' => self::faker()->email(),
            'password' => self::faker()->password(),
            'roles' => [],
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(User $user): void {})
        ;
    }

    protected static function getClass(): string
    {
        return User::class;
    }
}
