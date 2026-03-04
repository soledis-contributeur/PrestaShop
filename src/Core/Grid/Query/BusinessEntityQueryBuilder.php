<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Core\Grid\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

/**
 * Class BusinessEntityQueryBuilder builds search & count queries for business entities grid.
 */
final class BusinessEntityQueryBuilder extends AbstractDoctrineQueryBuilder
{
    public function __construct(
        Connection $connection,
        $dbPrefix,
        private readonly DoctrineSearchCriteriaApplicator $searchCriteriaApplicator
    ) {
        parent::__construct($connection, $dbPrefix);
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchQueryBuilder(SearchCriteriaInterface $searchCriteria)
    {
        $qb = $this->getBaseQueryBuilder($searchCriteria->getFilters());

        $qb->select('be.id_business_entity')
            ->addSelect('be.name')
            ->addSelect('be.legal_name')
            ->addSelect('be.status')
            ->addSelect('COUNT(DISTINCT becb.id_customer_b2b) AS customers_count')
            ->groupBy('be.id_business_entity')
        ;

        $orderBy = $searchCriteria->getOrderBy();
        $sortOrder = $searchCriteria->getOrderWay();
        $qb->orderBy($orderBy, $sortOrder);

        $this->searchCriteriaApplicator
            ->applyPagination($searchCriteria, $qb)
            ->applySorting($searchCriteria, $qb)
            ->applyDeterministicSorting($searchCriteria, $qb, 'be', 'id_business_entity')
        ;

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountQueryBuilder(SearchCriteriaInterface $searchCriteria)
    {
        $qb = $this->getBaseQueryBuilder($searchCriteria->getFilters());

        if (!empty($searchCriteria->getFilters()['customers_count'])) {
            $subQb = $this->getBaseQueryBuilder($searchCriteria->getFilters());
            $subQb->select('be.id_business_entity')
                ->addSelect('COUNT(DISTINCT becb.id_customer_b2b) AS customers_count')
                ->groupBy('be.id_business_entity')
            ;

            $outerQb = $this->connection->createQueryBuilder();
            $outerQb->select('COUNT(*)')
                ->from('(' . $subQb->getSQL() . ')', 't')
            ;

            foreach ($subQb->getParameters() as $name => $value) {
                $outerQb->setParameter($name, $value);
            }

            return $outerQb;
        }

        $qb->select('COUNT(DISTINCT be.id_business_entity)');

        return $qb;
    }

    private function getBaseQueryBuilder(array $filters): QueryBuilder
    {
        $qb = $this->connection->createQueryBuilder()
            ->from($this->dbPrefix . 'business_entity', 'be')
            ->leftJoin(
                'be',
                $this->dbPrefix . 'business_entity_customer_b2b',
                'becb',
                'becb.id_business_entity = be.id_business_entity'
            )
        ;

        foreach ($filters as $filterName => $filterValue) {
            if ($filterValue === '' || $filterValue === null) {
                continue;
            }

            if ('id_business_entity' === $filterName) {
                $qb->andWhere("be.id_business_entity = :$filterName");
                $qb->setParameter($filterName, (int) $filterValue);
                continue;
            }

            if ('name' === $filterName) {
                $qb->andWhere("be.name LIKE :$filterName");
                $qb->setParameter($filterName, '%' . $filterValue . '%');
                continue;
            }

            if ('legal_name' === $filterName) {
                $qb->andWhere("be.legal_name LIKE :$filterName");
                $qb->setParameter($filterName, '%' . $filterValue . '%');
                continue;
            }

            if ('status' === $filterName) {
                $qb->andWhere("be.status = :$filterName");
                $qb->setParameter($filterName, (string) $filterValue);
                continue;
            }

            if ('customers_count' === $filterName) {
                if (!is_numeric($filterValue)) {
                    continue;
                }

                $qb->having('COUNT(DISTINCT becb.id_customer_b2b) = :customers_count');
                $qb->setParameter('customers_count', (int) $filterValue);
                continue;
            }
        }

        return $qb;
    }
}
