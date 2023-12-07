<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Api\UrlGeneratorInterface;
use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Review;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ProcessorInterface<void>
 */
final readonly class ReviewRemoveProcessor implements ProcessorInterface
{
    /**
     * @param RemoveProcessor  $removeProcessor
     * @param MercureProcessor $mercureProcessor
     */
    public function __construct(
        #[Autowire(service: RemoveProcessor::class)]
        private ProcessorInterface $removeProcessor,
        #[Autowire(service: MercureProcessor::class)]
        private ProcessorInterface $mercureProcessor,
        private ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        private IriConverterInterface $iriConverter
    ) {}

    /**
     * @param Review $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $object = clone $data;

        // remove entity
        $this->removeProcessor->process($data, $operation, $uriVariables, $context);

        // publish on Mercure
        foreach (['/admin/reviews/{id}{._format}', '/books/{bookId}/reviews/{id}{._format}'] as $uriTemplate) {
            $iri = $this->iriConverter->getIriFromResource(
                $object,
                UrlGeneratorInterface::ABS_URL,
                $this->resourceMetadataCollectionFactory->create(Review::class)->getOperation($uriTemplate)
            );
            $this->mercureProcessor->process(
                $object,
                $operation,
                $uriVariables,
                $context + [
                    'item_uri_template' => $uriTemplate,
                    MercureProcessor::DATA => json_encode(['@id' => $iri]),
                ]
            );
        }
    }
}
