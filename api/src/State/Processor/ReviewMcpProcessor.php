<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Entity\Review;
use App\Mcp\Input\ReviewInput;
use App\Repository\BookRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Turns the "add_review" MCP tool arguments into a Review, then reuses the REST creation logic.
 *
 * @implements ProcessorInterface<ReviewInput, Review>
 */
final readonly class ReviewMcpProcessor implements ProcessorInterface
{
    /**
     * @param ReviewPersistProcessor $persistProcessor
     */
    public function __construct(
        #[Autowire(service: ReviewPersistProcessor::class)]
        private ProcessorInterface $persistProcessor,
        private BookRepository $bookRepository,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * @param ReviewInput $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Review
    {
        $book = Uuid::isValid($data->bookId) ? $this->bookRepository->find($data->bookId) : null;
        if (!$book) {
            throw new NotFoundHttpException('Book not found.');
        }

        $review = new Review();
        $review->book = $book;
        $review->body = $data->body;
        $review->rating = $data->rating;

        // same validation groups as the REST Post operation, including UniqueUserBook
        $violations = $this->validator->validate($review, null, ['Default', 'Review:create']);
        if (\count($violations) > 0) {
            throw new ValidationException($violations);
        }

        return $this->persistProcessor->process($review, $operation, $uriVariables, $context);
    }
}
