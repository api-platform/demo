<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Book;
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
    ) {
    }

    /**
     * @param Book $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        // remove entity
        $this->removeProcessor->process($data, $operation, $uriVariables, $context);
    }
}
