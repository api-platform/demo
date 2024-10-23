<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\BookRepository\BookRepositoryInterface;
use App\Entity\Book;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<Book, Book>
 */
final readonly class BookPersistProcessor implements ProcessorInterface
{
    /**
     * @param PersistProcessor $persistProcessor
     */
    public function __construct(
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $persistProcessor,
        private BookRepositoryInterface $bookRepository,
    ) {
    }

    /**
     * @param Book $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Book
    {
        $book = $this->bookRepository->find($data->book);

        // this should never happen
        if (!$book instanceof Book) {
            throw new NotFoundHttpException();
        }

        $data->title = $book->title;
        $data->author = $book->author;

        // save entity
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
