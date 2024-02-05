<?php

namespace App\Tests\Api\Mock;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\MockHttpClient;

#[AsDecorator(decorates: 'security.authorization.client', priority: 1000)]
final class NotImplementedMock extends MockHttpClient
{
    public function __construct(
        #[Autowire('%env(OIDC_SERVER_URL_INTERNAL)%/')]
        string $baseUri
    ) {
        parent::__construct($this->handleRequest(...), $baseUri);
    }

    public function handleRequest(string $method, string $url): void
    {
        throw new \UnexpectedValueException("Mock not implemented: $method/$url");
    }
}
