<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Bookmark;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @implements ProcessorInterface<Bookmark>
 */
final readonly class BookmarkPersistProcessor implements ProcessorInterface
{
    /**
     * @param PersistProcessor $persistProcessor
     */
    public function __construct(
        #[Autowire(service: PersistProcessor::class)]
        private ProcessorInterface $persistProcessor,
        private Security $security,
        private ClockInterface $clock
    ) {}

    /**
     * @param Bookmark $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Bookmark
    {
        /** @phpstan-ignore-next-line */
        $data->user = $this->security->getUser();
        $data->bookmarkedAt = $this->clock->now();

        // save entity
        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
