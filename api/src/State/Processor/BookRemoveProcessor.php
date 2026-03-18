<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Book;
use App\Repository\BookmarkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ProcessorInterface<Book, void>
 */
final readonly class BookRemoveProcessor implements ProcessorInterface
{
    /**
     * @param RemoveProcessor $removeProcessor
     */
    public function __construct(
        #[Autowire(service: RemoveProcessor::class)]
        private ProcessorInterface $removeProcessor,
        private EntityManagerInterface $entityManager,
        private BookmarkRepository $bookmarkRepository,
    ) {
    }

    /**
     * @param Book $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        // Remove related bookmarks and reviews before deleting the book
        foreach ($this->bookmarkRepository->findBy(['book' => $data]) as $bookmark) {
            $this->entityManager->remove($bookmark);
        }
        foreach ($data->reviews as $review) {
            $this->entityManager->remove($review);
        }
        $this->entityManager->flush();

        // remove entity
        $this->removeProcessor->process($data, $operation, $uriVariables, $context);
    }
}
