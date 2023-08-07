<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Review;
use App\Repository\ReviewRepository;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class ReviewPersistProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: ReviewRepository::class)]
        private ObjectRepository $repository,
        private Security $security,
        #[Autowire(service: MercureProcessor::class)]
        private ProcessorInterface $mercureProcessor
    ) {
    }

    /**
     * @param Review $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Review
    {
        $data->user = $this->security->getUser();
        $data->publishedAt = new \DateTimeImmutable();

        // save entity
        $this->repository->save($data, true);

        // publish on Mercure
        // todo find a way to do it in API Platform
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
