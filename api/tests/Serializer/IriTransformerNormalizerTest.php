<?php

declare(strict_types=1);

namespace App\Tests\Serializer;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Api\UrlGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Operation\Factory\OperationMetadataFactoryInterface;
use App\Serializer\IriTransformerNormalizer;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class IriTransformerNormalizerTest extends TestCase
{
    private MockObject|NormalizerInterface $normalizerMock;
    private IriConverterInterface|MockObject $iriConverterMock;
    private MockObject|OperationMetadataFactoryInterface $operationMetadataFactoryMock;
    private MockObject|Operation $operationMock;
    private MockObject|\stdClass $objectMock;
    private IriTransformerNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizerMock = $this->createMock(NormalizerInterface::class);
        $this->iriConverterMock = $this->createMock(IriConverterInterface::class);
        $this->operationMetadataFactoryMock = $this->createMock(OperationMetadataFactoryInterface::class);
        $this->operationMock = $this->createMock(Operation::class);
        $this->objectMock = new \stdClass();
        $this->objectMock->book = $this->createMock(\stdClass::class);
        $this->objectMock->user = $this->createMock(\stdClass::class);

        $this->normalizer = new IriTransformerNormalizer($this->iriConverterMock, $this->operationMetadataFactoryMock);
        $this->normalizer->setNormalizer($this->normalizerMock);
    }

    /**
     * @test
     */
    public function itDoesNotSupportInvalidData(): void
    {
        $this->assertFalse($this->normalizer->supportsNormalization(null));
        $this->assertFalse($this->normalizer->supportsNormalization([]));
        $this->assertFalse($this->normalizer->supportsNormalization('string'));
        $this->assertFalse($this->normalizer->supportsNormalization(12345));
        $this->assertFalse($this->normalizer->supportsNormalization(new ArrayCollection([$this->objectMock])));
    }

    /**
     * @test
     */
    public function itDoesNotSupportInvalidContext(): void
    {
        $this->assertFalse($this->normalizer->supportsNormalization($this->objectMock));
        $this->assertFalse($this->normalizer->supportsNormalization($this->objectMock, null, [IriTransformerNormalizer::class => true]));
    }

    /**
     * @test
     */
    public function itDoesNotSupportInvalidFormat(): void
    {
        $this->assertFalse($this->normalizer->supportsNormalization($this->objectMock, null, [
            IriTransformerNormalizer::CONTEXT_KEY => [
                'book' => '/books/{id}{._format}',
            ],
        ]));
        $this->assertFalse($this->normalizer->supportsNormalization($this->objectMock, 'json', [
            IriTransformerNormalizer::CONTEXT_KEY => [
                'book' => '/books/{id}{._format}',
            ],
        ]));
        $this->assertFalse($this->normalizer->supportsNormalization($this->objectMock, 'xml', [
            IriTransformerNormalizer::CONTEXT_KEY => [
                'book' => '/books/{id}{._format}',
            ],
        ]));
    }

    /**
     * @test
     */
    public function itSupportsValidObjectClassAndContext(): void
    {
        $this->assertTrue($this->normalizer->supportsNormalization($this->objectMock, 'jsonld', [
            IriTransformerNormalizer::CONTEXT_KEY => [
                'book' => '/books/{id}{._format}',
            ],
        ]));
    }

    /**
     * @test
     */
    public function itNormalizesData(): void
    {
        $this->normalizerMock
            ->expects($this->once())
            ->method('normalize')
            ->with($this->objectMock, 'jsonld', [
                IriTransformerNormalizer::class => true,
                IriTransformerNormalizer::CONTEXT_KEY => [
                    'ignore' => 'lorem ipsum',
                    'book' => '/books/{id}{._format}',
                    'user' => '/users/{id}{._format}',
                ],
            ])
            ->willReturn([
                'book' => '/admin/books/a528046c-7ba1-4acc-bff2-b5390ab17d41',
                'user' => [
                    '@id' => '/admin/users/b960cf9e-8f1a-4690-8923-623c1d049d41',
                ],
            ])
        ;
        $this->operationMetadataFactoryMock
            ->expects($this->exactly(2))
            ->method('create')
            ->withConsecutive(
                ['/books/{id}{._format}'],
                ['/users/{id}{._format}'],
            )
            ->willReturnOnConsecutiveCalls(
                $this->operationMock,
                $this->operationMock,
            )
        ;
        $this->iriConverterMock
            ->expects($this->exactly(2))
            ->method('getIriFromResource')
            ->withConsecutive(
                [$this->objectMock->book, UrlGeneratorInterface::ABS_PATH, $this->operationMock],
                [$this->objectMock->book, UrlGeneratorInterface::ABS_PATH, $this->operationMock],
            )
            ->willReturnOnConsecutiveCalls(
                '/books/a528046c-7ba1-4acc-bff2-b5390ab17d41',
                '/users/b960cf9e-8f1a-4690-8923-623c1d049d41',
            )
        ;

        $this->assertEquals([
            'book' => '/books/a528046c-7ba1-4acc-bff2-b5390ab17d41',
            'user' => [
                '@id' => '/users/b960cf9e-8f1a-4690-8923-623c1d049d41',
            ],
        ], $this->normalizer->normalize($this->objectMock, 'jsonld', [
            IriTransformerNormalizer::CONTEXT_KEY => [
                'ignore' => 'lorem ipsum',
                'book' => '/books/{id}{._format}',
                'user' => '/users/{id}{._format}',
            ],
        ]));
    }
}
