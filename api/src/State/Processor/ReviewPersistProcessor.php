<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Review;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ProcessorInterface<Review>
 */
final readonly class ReviewPersistProcessor implements ProcessorInterface
{
    /**
     * @param PersistProcessor $persistProcessor
     * @param MercureProcessor $mercureProcessor
     */
    public function __construct(
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $persistProcessor,
        #[Autowire(service: MercureProcessor::class)]
        private ProcessorInterface $mercureProcessor,
        private Security $security,
        private ClockInterface $clock
    ) {}

    /**
     * @param Review $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Review
    {
        // standard PUT
        if (isset($context['previous_data'])) {
            $data->user = $context['previous_data']->user;
            $data->publishedAt = $context['previous_data']->publishedAt;
        }

        // prevent overriding user, for instance from admin
        if ($operation instanceof Post) {
            /** @phpstan-ignore-next-line */
            $data->user = $this->security->getUser();
            $data->publishedAt = $this->clock->now();
        }

        // save entity
        $data = $this->persistProcessor->process($data, $operation, $uriVariables, $context);

        // publish on Mercure
        foreach (['/admin/reviews/{id}{._format}', '/books/{bookId}/reviews/{id}{._format}'] as $uriTemplate) {
            $this->mercureProcessor->process(
                $data,
                $operation,
                $uriVariables,
                $context + [
                    'item_uri_template' => $uriTemplate,
                ]
            );
        }

        return $data;
    }
}
