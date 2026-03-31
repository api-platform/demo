<?php

declare(strict_types=1);

namespace App\HttpCache;

use ApiPlatform\HttpCache\PurgerInterface;
use Psr\Log\LoggerInterface;

/**
 * Wraps the Souin purger to prevent cache invalidation failures from crashing the application.
 *
 * When the Caddy admin API is unreachable (e.g. during CLI commands like doctrine:fixtures:load),
 * exceptions are caught and logged as warnings instead of propagating.
 */
final class FaultTolerantSouinPurger implements PurgerInterface
{
    public function __construct(
        private readonly PurgerInterface $inner,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function purge(array $iris): void
    {
        try {
            $this->inner->purge($iris);
        } catch (\Throwable $e) {
            $this->logger->warning('Failed to purge HTTP cache: {message}', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);
        }
    }

    public function getResponseHeaders(array $iris): array
    {
        return $this->inner->getResponseHeaders($iris);
    }
}
