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
        private Security $security
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

        return $data;
    }
}
