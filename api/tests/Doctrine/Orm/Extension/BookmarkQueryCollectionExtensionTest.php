<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\Orm\Extension;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Doctrine\Orm\Extension\BookmarkQueryCollectionExtension;
use App\Entity\Bookmark;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

final class BookmarkQueryCollectionExtensionTest extends TestCase
{
    private MockObject|Security $securityMock;
    private MockObject|UserInterface $userMock;
    private MockObject|QueryBuilder $queryBuilderMock;
    private MockObject|QueryNameGeneratorInterface $queryNameGeneratorMock;
    private MockObject|Operation $operationMock;
    private BookmarkQueryCollectionExtension $extension;

    protected function setUp(): void
    {
        $this->securityMock = $this->createMock(Security::class);
        $this->userMock = $this->createMock(UserInterface::class);
        $this->queryBuilderMock = $this->createMock(QueryBuilder::class);
        $this->queryNameGeneratorMock = $this->createMock(QueryNameGeneratorInterface::class);
        $this->operationMock = $this->createMock(Operation::class);

        $this->extension = new BookmarkQueryCollectionExtension($this->securityMock);
    }

    /**
     * @test
     */
    public function itFiltersBookmarksQueryOnCurrentUser(): void
    {
        $this->operationMock
            ->expects($this->once())
            ->method('getName')
            ->willReturn('_api_/bookmarks{._format}_get_collection')
        ;
        $this->securityMock
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->userMock)
        ;
        $this->queryBuilderMock
            ->expects($this->once())
            ->method('getRootAliases')
            ->willReturn(['o'])
        ;
        $this->queryBuilderMock
            ->expects($this->once())
            ->method('andWhere')
            ->with('o.user = :user')
            ->willReturn($this->queryBuilderMock)
        ;
        $this->queryBuilderMock
            ->expects($this->once())
            ->method('setParameter')
            ->with('user', $this->userMock)
            ->willReturn($this->queryBuilderMock)
        ;

        $this->extension->applyToCollection(
            $this->queryBuilderMock,
            $this->queryNameGeneratorMock,
            Bookmark::class,
            $this->operationMock
        );
    }

    /**
     * @test
     */
    public function itIgnoresInvalidResourceClass(): void
    {
        $this->operationMock->expects($this->never())->method('getName');
        $this->securityMock->expects($this->never())->method('getUser');
        $this->queryBuilderMock->expects($this->never())->method('getRootAliases');
        $this->queryBuilderMock->expects($this->never())->method('andWhere');
        $this->queryBuilderMock->expects($this->never())->method('setParameter');

        $this->extension->applyToCollection(
            $this->queryBuilderMock,
            $this->queryNameGeneratorMock,
            \stdClass::class,
            $this->operationMock
        );
    }

    /**
     * @test
     */
    public function itIgnoresInvalidOperation(): void
    {
        $this->operationMock
            ->expects($this->once())
            ->method('getName')
            ->willReturn('_api_/books{._format}_get_collection')
        ;
        $this->securityMock->expects($this->never())->method('getUser');
        $this->queryBuilderMock->expects($this->never())->method('getRootAliases');
        $this->queryBuilderMock->expects($this->never())->method('andWhere');
        $this->queryBuilderMock->expects($this->never())->method('setParameter');

        $this->extension->applyToCollection(
            $this->queryBuilderMock,
            $this->queryNameGeneratorMock,
            Bookmark::class,
            $this->operationMock
        );
    }

    /**
     * @test
     */
    public function itIgnoresInvalidUser(): void
    {
        $this->operationMock
            ->expects($this->once())
            ->method('getName')
            ->willReturn('_api_/bookmarks{._format}_get_collection')
        ;
        $this->securityMock
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(null)
        ;
        $this->queryBuilderMock->expects($this->never())->method('getRootAliases');
        $this->queryBuilderMock->expects($this->never())->method('andWhere');
        $this->queryBuilderMock->expects($this->never())->method('setParameter');

        $this->extension->applyToCollection(
            $this->queryBuilderMock,
            $this->queryNameGeneratorMock,
            Bookmark::class,
            $this->operationMock
        );
    }
}
