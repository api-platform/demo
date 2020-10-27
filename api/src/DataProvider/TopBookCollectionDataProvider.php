<?php declare(strict_types=1);

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\TopBook;

final class TopBookCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private TopBookDataProvider $dataProvider;

    public function __construct(TopBookDataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return TopBook::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        try {
            $topBooks = $this->dataProvider->getTopBooks();
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Unable to retrieve top books from external source: %s', $e->getMessage()));
        }

        return $topBooks;
    }
}
