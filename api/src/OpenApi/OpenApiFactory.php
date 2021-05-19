<?php

declare(strict_types=1);

namespace App\OpenApi;

use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\Model\Operation;
use ApiPlatform\Core\OpenApi\Model\PathItem;
use ApiPlatform\Core\OpenApi\OpenApi;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(private OpenApiFactoryInterface $decorated)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $openApi
            ->getPaths()
            ->addPath('/stats', new PathItem(null, null, null, new Operation(
                    'get',
                    ['Stats'],
                    [
                        Response::HTTP_OK => [
                            'content' => [
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
                            ],
                        ],
                    ],
                    'Retrieves the number of books and top books (legacy endpoint).'
                )
            ));

        return $openApi;
    }
}
