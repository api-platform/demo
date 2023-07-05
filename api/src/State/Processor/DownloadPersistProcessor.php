<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Download;
use App\Repository\DownloadRepository;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class DownloadPersistProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: DownloadRepository::class)]
        private ObjectRepository $repository,
        private Security $security
    ) {
    }

    /**
     * @param Download $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Download
    {
        $data->user = $this->security->getUser();
        $data->downloadedAt = new \DateTimeImmutable();

        // save entity
        $this->repository->save($data, true);

        return $data;
    }
}
