<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Test\ApiTestCase;
use ApiPlatform\Test\Client;
use App\DataFixtures\Factory\BookFactory;
use App\DataFixtures\Factory\ReviewFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Repository\ReviewRepository;
use App\Tests\Api\Security\TokenGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

final class McpTest extends ApiTestCase
{
    use Factories;
    use ResetDatabase;

    // see the "api-platform-mcp-audience" Keycloak client scope
    private const string AUDIENCE = 'api-platform-mcp';

    private Client $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    #[Test]
    public function asAnonymousICanListTheTools(): void
    {
        $result = $this->call('tools/list');

        $tools = array_column($result['result']['tools'], null, 'name');
        self::assertArrayHasKey('search_books', $tools);
        self::assertArrayHasKey('get_book', $tools);
        // listed even for anonymous clients, so they can discover it and authenticate
        self::assertArrayHasKey('add_review', $tools);
        self::assertTrue($tools['search_books']['annotations']['readOnlyHint']);
        self::assertSame(['title', 'author'], array_keys($tools['search_books']['inputSchema']['properties']));
        self::assertSame(['bookId', 'body', 'rating'], array_keys($tools['add_review']['inputSchema']['properties']));
    }

    #[Test]
    public function asAnonymousICanSearchBooks(): void
    {
        BookFactory::createOne(['title' => 'Hyperion', 'author' => 'Dan Simmons']);
        BookFactory::createOne(['title' => 'The Fall of Hyperion', 'author' => 'Dan Simmons']);
        BookFactory::createOne(['title' => 'Foundation', 'author' => 'Isaac Asimov']);

        $result = $this->call('tools/call', ['name' => 'search_books', 'arguments' => ['title' => 'HYPERION']]);

        self::assertArrayNotHasKey('error', $result);
        self::assertFalse($result['result']['isError']);
        $content = $result['result']['structuredContent'];
        self::assertSame(2, $content['totalItems']);
        self::assertSame(['Hyperion', 'The Fall of Hyperion'], array_column($content['member'], 'title'));

        $result = $this->call('tools/call', ['name' => 'search_books', 'arguments' => ['author' => 'asimov']]);

        self::assertSame(['Foundation'], array_column($result['result']['structuredContent']['member'], 'title'));
    }

    #[Test]
    public function asAnonymousISearchBooksWithALimit(): void
    {
        BookFactory::createMany(35);

        $result = $this->call('tools/call', ['name' => 'search_books', 'arguments' => []]);

        self::assertCount(30, $result['result']['structuredContent']['member']);
    }

    #[Test]
    public function asAnonymousICanGetABook(): void
    {
        $book = BookFactory::createOne(['title' => 'Hyperion']);

        $result = $this->call('tools/call', ['name' => 'get_book', 'arguments' => ['id' => (string) $book->getId()]]);

        self::assertArrayNotHasKey('error', $result);
        self::assertSame('/books/' . $book->getId(), $result['result']['structuredContent']['@id']);
        self::assertSame('Hyperion', $result['result']['structuredContent']['title']);
    }

    #[Test]
    public function asAnonymousICannotGetAnUnknownBook(): void
    {
        $result = $this->call('tools/call', ['name' => 'get_book', 'arguments' => ['id' => (string) Uuid::v7()]]);

        self::assertArrayHasKey('error', $result);
    }

    #[Test]
    public function asAnonymousICannotAddAReview(): void
    {
        $book = BookFactory::createOne();

        $response = $this->send('tools/call', ['name' => 'add_review', 'arguments' => [
            'bookId' => (string) $book->getId(),
            'body' => 'Very good book!',
            'rating' => 5,
        ]]);

        // MCP clients start the OAuth flow on this challenge
        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        self::assertSame(
            'Bearer resource_metadata="http://localhost/.well-known/oauth-protected-resource/mcp"',
            $response->getHeaders(false)['www-authenticate'][0]
        );
        self::assertCount(0, self::getContainer()->get(ReviewRepository::class)->findBy(['book' => $book]));
    }

    #[Test]
    public function asAnonymousICanCallAPublicToolWithoutChallenge(): void
    {
        $response = $this->send('tools/call', ['name' => 'search_books', 'arguments' => []]);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    #[Test]
    #[DataProvider(methodName: 'getInvalidTokenClaims')]
    public function asAUserICannotUseAnInvalidToken(array $claims): void
    {
        $token = self::getContainer()->get(TokenGenerator::class)->generateToken($claims + [
            'email' => UserFactory::createOne()->email,
        ]);

        $response = $this->send('tools/list', [], $token);

        self::assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        self::assertStringContainsString(
            'resource_metadata="http://localhost/.well-known/oauth-protected-resource/mcp"',
            $response->getHeaders(false)['www-authenticate'][0]
        );
    }

    public static function getInvalidTokenClaims(): iterable
    {
        // e.g. a token issued to the PWA
        yield 'token not issued for the MCP server' => [[]];
        yield 'expired token' => [['aud' => self::AUDIENCE, 'exp' => time() - 3600]];
    }

    #[Test]
    public function asAUserWithoutTheUserRoleICannotAddAReview(): void
    {
        $book = BookFactory::createOne();
        $token = $this->generateToken(UserFactory::createOne()->email, ['realm_access' => ['roles' => ['offline_access']]]);

        $result = $this->call('tools/call', ['name' => 'add_review', 'arguments' => [
            'bookId' => (string) $book->getId(),
            'body' => 'Very good book!',
            'rating' => 5,
        ]], $token);

        self::assertSame('Access Denied.', $result['error']['message']);
    }

    #[Test]
    #[Group('mercure')]
    public function asAUserICanAddAReview(): void
    {
        $book = BookFactory::createOne();
        $user = UserFactory::createOne();
        self::getMercureHub()->reset();

        $token = $this->generateToken($user->email);

        $result = $this->call('tools/call', ['name' => 'add_review', 'arguments' => [
            'bookId' => (string) $book->getId(),
            'body' => 'Very good book!',
            'rating' => 5,
        ]], $token);

        self::assertArrayNotHasKey('error', $result);
        self::assertSame('/books/' . $book->getId(), $result['result']['structuredContent']['book']);
        self::assertSame('Very good book!', $result['result']['structuredContent']['body']);
        $reviews = self::getContainer()->get(ReviewRepository::class)->findBy(['book' => $book]);
        self::assertCount(1, $reviews);
        self::assertSame($user->email, $reviews[0]->user->email);
        self::assertSame(5, $reviews[0]->rating);
        // the PWA is notified in real time
        self::assertCount(2, self::getMercureMessages());
    }

    #[Test]
    public function asAUserICannotAddADuplicateReview(): void
    {
        $book = BookFactory::createOne();
        $user = UserFactory::createOne();
        ReviewFactory::createOne(['book' => $book, 'user' => $user]);

        $token = $this->generateToken($user->email);

        $result = $this->call('tools/call', ['name' => 'add_review', 'arguments' => [
            'bookId' => (string) $book->getId(),
            'body' => 'Very good book!',
            'rating' => 5,
        ]], $token);

        self::assertStringContainsString('You have already reviewed this book.', $result['error']['message']);
    }

    #[Test]
    public function asAUserICannotAddAReviewOnAnUnknownBook(): void
    {
        $token = $this->generateToken(UserFactory::createOne()->email);

        $result = $this->call('tools/call', ['name' => 'add_review', 'arguments' => [
            'bookId' => 'invalid',
            'body' => 'Very good book!',
            'rating' => 5,
        ]], $token);

        self::assertSame('Book not found.', $result['error']['message']);
    }

    private function generateToken(string $email, array $claims = []): string
    {
        return self::getContainer()->get(TokenGenerator::class)->generateToken($claims + [
            'email' => $email,
            'aud' => self::AUDIENCE,
        ]);
    }

    private function call(string $method, array $params = [], ?string $token = null): array
    {
        return $this->send($method, $params, $token)->toArray();
    }

    /**
     * Opens an MCP session, then sends a JSON-RPC request in it.
     */
    private function send(string $method, array $params = [], ?string $token = null): ResponseInterface
    {
        $response = $this->client->request('POST', '/mcp', [
            'headers' => ['Accept' => 'application/json, text/event-stream'],
            'json' => [
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'initialize',
                'params' => [
                    'protocolVersion' => '2025-06-18',
                    'clientInfo' => ['name' => 'api-platform-demo-tests', 'version' => '1.0'],
                    'capabilities' => [],
                ],
            ],
        ]);
        self::assertResponseIsSuccessful();
        $sessionId = $response->getHeaders()['mcp-session-id'][0];

        $headers = ['Accept' => 'application/json, text/event-stream', 'Mcp-Session-Id' => $sessionId];
        if ($token) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        return $this->client->request('POST', '/mcp', [
            'headers' => $headers,
            'json' => ['jsonrpc' => '2.0', 'id' => 2, 'method' => $method, 'params' => $params],
        ]);
    }
}
