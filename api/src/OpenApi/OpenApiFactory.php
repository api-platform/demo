<?php

declare(strict_types=1);

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\Response as OpenApiResponse;
use ApiPlatform\OpenApi\OpenApi;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
#[AsDecorator(decorates: 'api_platform.openapi.factory')]
final class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(private readonly OpenApiFactoryInterface $decorated)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $paths = $openApi->getPaths();

        $paths->addPath(
            '/stats',
            (new PathItem())
                ->withGet(
                    (new OpenApiOperation())
                        ->withOperationId('get')
                        ->withTags(['Stats'])
                        ->withResponse(
                            Response::HTTP_OK,
                            (new OpenApiResponse())
                                ->withContent(new \ArrayObject([
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'books_count' => [
                                                    'type' => 'integer',
                                                    'example' => 997,
                                                ],
                                                'topbooks_count' => [
                                                    'type' => 'integer',
                                                    'example' => 101,
                                                ],
                                            ],
                                        ],
                                    ],
                                ]))
                        )
                        ->withSummary('Retrieves the number of books and top books (legacy endpoint).')
                )
        );
        $paths->addPath(
            '/profile',
            (new PathItem())
                ->withGet(
                    (new OpenApiOperation())
                        ->withOperationId('get')
                        ->withTags(['Profile'])
                        ->withResponse(
                            Response::HTTP_OK,
                            (new OpenApiResponse())
                                ->withContent(new \ArrayObject([
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'id' => [
                                                    'type' => 'string',
                                                ],
                                                'email' => [
                                                    'type' => 'string',
                                                ],
                                                'roles' => [
                                                    'type' => 'array',
                                                ],
                                            ],
                                        ],
                                    ],
                                ]))
                        )
                )
        );

        return $openApi;
    }
}
