<?php


namespace App\DataProvider;


use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\PaginationExtension;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Poll;
use Doctrine\Common\Persistence\ManagerRegistry;


/**
 * Try with this next time : https://api-platform.com/docs/core/data-providers/#injecting-extensions-pagination-filter-eagerloading-etc
 *
 * Class PollsDataProvider
 * @package App\DataProvider
 */
final class PollsDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $managerRegistry;
    private $paginationExtension;
    private $context;

    public function __construct(ManagerRegistry $managerRegistry, PaginationExtension $paginationExtension)
    {
        $this->managerRegistry = $managerRegistry;
        $this->paginationExtension = $paginationExtension;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        $this->context = $context;
        return Poll::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null)
    {
        $queryBuilder = $this->managerRegistry
            ->getManagerForClass($resourceClass)
            ->getRepository($resourceClass)->createQueryBuilder('poll')
            ->where('poll.scope = :scope')
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
}