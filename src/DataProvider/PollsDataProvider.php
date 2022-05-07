<?php

declare(strict_types=1);

namespace App\DataProvider;


use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\PaginationExtension;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Controller\Is\UserAware;
use App\Entity\Poll;
use App\Security\PermissionsReferee;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;


/**
 * ApiPlatform "hook".
 * Try with this next time : https://api-platform.com/docs/core/data-providers/#injecting-extensions-pagination-filter-eagerloading-etc
 *
 * Class PollsDataProvider
 * @package App\DataProvider
 */
final class PollsDataProvider implements ItemDataProviderInterface, CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $managerRegistry;
    private $paginationExtension;
    private $permissionsReferee;
    private $context;

    use UserAware;

    public function __construct(
        ManagerRegistry $managerRegistry,
        PaginationExtension $paginationExtension,
        PermissionsReferee $permissionsReferee
    )
    {
        $this->managerRegistry = $managerRegistry;
        $this->paginationExtension = $paginationExtension;
        $this->permissionsReferee = $permissionsReferee;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        $this->context = $context;
        return Poll::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->managerRegistry
            ->getManagerForClass($resourceClass)
            ->getRepository($resourceClass)
            ->createQueryBuilder('poll');

        $predicate = $queryBuilder->expr()->eq('poll.scope', ':scope');

        $currentUser = $this->getUser();
        if (null != $currentUser) {
            $predicate = $queryBuilder->expr()->orX(
                $predicate,
                $queryBuilder->expr()->eq('poll.author', ':author')
            );
            $queryBuilder = $queryBuilder->setParameter('author', $currentUser->getId());
        }

        $queryBuilder = $queryBuilder
            ->where($predicate)
            ->orderBy('poll.id', 'DESC')
            ->setParameter('scope', 'public');

        $this->paginationExtension->applyToCollection(
            $queryBuilder,
            new QueryNameGenerator(),
            $resourceClass,
            $operationName,
            $this->context
        );

        if (
            $this->paginationExtension instanceof QueryResultCollectionExtensionInterface
            &&
            $this->paginationExtension->supportsResult($resourceClass, $operationName, $this->context)
        ) {

            return $this->paginationExtension->getResult(
                $queryBuilder,
                $resourceClass,
                $operationName,
                $this->context
            );
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Retrieves an item. (a poll)
     * This is used by ApiPlatform.
     *
     * @param string $resourceClass
     * @param array|int|string $id
     * @param string|null $operationName
     * @param array $context
     * @return Poll|null
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        /** @var Poll $poll */
        $poll = $this->managerRegistry
            ->getManagerForClass($resourceClass)
            ->getRepository($resourceClass)
            ->findOneByIdLike((string)$id);

        if ($poll) {
            $poll->setCanGenerateInvitations($this->permissionsReferee->canGenerateInvitationsFor($poll));
        }

        return $poll;
    }
}