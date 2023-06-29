<?php

declare(strict_types=1);

namespace App\DataFixtures\Factory;

use App\Entity\Download;
use Doctrine\ORM\EntityRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;
use function Zenstruck\Foundry\lazy;

/**
 * @extends ModelFactory<Download>
 *
 * @method        Download|Proxy                       create(array|callable $attributes = [])
 * @method static Download|Proxy                       createOne(array $attributes = [])
 * @method static Download|Proxy                       find(object|array|mixed $criteria)
 * @method static Download|Proxy                       findOrCreate(array $attributes)
 * @method static Download|Proxy                       first(string $sortedField = 'id')
 * @method static Download|Proxy                       last(string $sortedField = 'id')
 * @method static Download|Proxy                       random(array $attributes = [])
 * @method static Download|Proxy                       randomOrCreate(array $attributes = [])
 * @method static EntityRepository|RepositoryProxy repository()
 * @method static Download[]|Proxy[]                   all()
 * @method static Download[]|Proxy[]                   createMany(int $number, array|callable $attributes = [])
 * @method static Download[]|Proxy[]                   createSequence(iterable|callable $sequence)
 * @method static Download[]|Proxy[]                   findBy(array $attributes)
 * @method static Download[]|Proxy[]                   randomRange(int $min, int $max, array $attributes = [])
 * @method static Download[]|Proxy[]                   randomSet(int $number, array $attributes = [])
 *
 * @psalm-method        Proxy<Download> create(array|callable $attributes = [])
 * @psalm-method static Proxy<Download> createOne(array $attributes = [])
 * @psalm-method static Proxy<Download> find(object|array|mixed $criteria)
 * @psalm-method static Proxy<Download> findOrCreate(array $attributes)
 * @psalm-method static Proxy<Download> first(string $sortedField = 'id')
 * @psalm-method static Proxy<Download> last(string $sortedField = 'id')
 * @psalm-method static Proxy<Download> random(array $attributes = [])
 * @psalm-method static Proxy<Download> randomOrCreate(array $attributes = [])
 * @psalm-method static RepositoryProxy<Download> repository()
 * @psalm-method static list<Proxy<Download>> all()
 * @psalm-method static list<Proxy<Download>> createMany(int $number, array|callable $attributes = [])
 * @psalm-method static list<Proxy<Download>> createSequence(iterable|callable $sequence)
 * @psalm-method static list<Proxy<Download>> findBy(array $attributes)
 * @psalm-method static list<Proxy<Download>> randomRange(int $min, int $max, array $attributes = [])
 * @psalm-method static list<Proxy<Download>> randomSet(int $number, array $attributes = [])
 */
final class DownloadFactory extends ModelFactory
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
            'user' => lazy(fn () => UserFactory::randomOrCreate()),
            'book' => lazy(fn () => BookFactory::randomOrCreate()),
            'downloadedAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Download $download): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Download::class;
    }
}
