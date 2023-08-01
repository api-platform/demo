<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Bookmark;
use App\Repository\BookmarkRepository;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class BookmarkPersistProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: BookmarkRepository::class)]
        private ObjectRepository $repository,
        private Security $security
    ) {
    }

    /**
     * @param Bookmark $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Bookmark
    {
        $data->user = $this->security->getUser();
        $data->bookmarkedAt = new \DateTimeImmutable();

        // save entity
        $this->repository->save($data, true);

        return $data;
    }
}
