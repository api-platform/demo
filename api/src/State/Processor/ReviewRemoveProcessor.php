<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Review;
use App\Security\Http\Protection\ResourceHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ProcessorInterface<Review, void>
 */
final readonly class ReviewRemoveProcessor implements ProcessorInterface
{
    /**
     * @param RemoveProcessor $removeProcessor
     */
    public function __construct(
        #[Autowire(service: RemoveProcessor::class)]
        private ProcessorInterface $removeProcessor,
        private ResourceHandlerInterface $resourceHandler,
    ) {
    }

    /**
     * @param Review $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $object = clone $data;

        // remove entity
        $this->removeProcessor->process($data, $operation, $uriVariables, $context);

        // project specification: only delete resource on OIDC server for known users (john.doe and chuck.norris)
        if (\in_array($object->user->email, ['john.doe@example.com', 'chuck.norris@example.com'], true)) {
            $this->resourceHandler->delete($object, $object->user, [
                'operation_name' => '/books/{bookId}/reviews/{id}{._format}',
            ]);
        }
    }
}
