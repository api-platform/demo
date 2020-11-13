<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SwaggerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setup(): void
    {
        $this->client = static::createClient();
    }

    public function testStats(): void
    {
        $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Stats', (string) $this->client->getResponse()->getContent());
    }
}
