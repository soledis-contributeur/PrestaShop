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
 * CustomerB2BQueryBuilder builds the queries needed for customer B2B grid.
 */
final class CustomerB2BQueryBuilder extends AbstractDoctrineQueryBuilder
{
    private readonly DoctrineSearchCriteriaApplicator $searchCriteriaApplicator;

    public function __construct(
        Connection $connection,
        string $dbPrefix,
        DoctrineSearchCriteriaApplicator $searchCriteriaApplicator
    ) {
        parent::__construct($connection, $dbPrefix);
        $this->searchCriteriaApplicator = $searchCriteriaApplicator;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $qb = $this->getBaseQueryBuilder($searchCriteria->getFilters());

        $qb->select(
            'cb.id_customer_b2b',
            'c.id_customer',
            'c.firstname',
            'c.lastname',
            'c.email',
            'br.role',
            'c.active',
            'be.id_business_entity',
            'be.name AS business_entity_name',
            'be.legal_name AS business_entity_legal_name',
            'COUNT(DISTINCT bec.id_customer_b2b) AS customers_count'
        )
            ->groupBy('cb.id_customer_b2b');

        $orderBy = $searchCriteria->getOrderBy();
        $sortOrder = $searchCriteria->getOrderWay();
        $qb->orderBy($orderBy, $sortOrder);

        $this->searchCriteriaApplicator
            ->applyPagination($searchCriteria, $qb)
            ->applySorting($searchCriteria, $qb)
            ->applyDeterministicSorting($searchCriteria, $qb, 'cb', 'id_customer_b2b');

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $businessEntityId = $searchCriteria->getFilters()['businessEntityId'];
        $qb = $this->getBaseQueryBuilder($searchCriteria->getFilters());

        $qb->select('COUNT(DISTINCT cb.id_customer_b2b)');

        return $qb;
    }

    /**
     * Build the base query builder with common joins and filters
     */
    private function getBaseQueryBuilder(array $filters): QueryBuilder
    {
        $qb = $this->connection->createQueryBuilder()
            ->from($this->dbPrefix . 'customer', 'c')
            ->join('c', $this->dbPrefix . 'customer_b2b', 'cb', 'cb.id_customer = c.id_customer')
            ->join('cb', $this->dbPrefix . 'business_entity_customer_b2b', 'bec', 'bec.id_customer_b2b = cb.id_customer_b2b')
            ->join('bec', $this->dbPrefix . 'business_entity', 'be', 'be.id_business_entity = bec.id_business_entity')
            ->leftJoin('bec', $this->dbPrefix . 'b2b_role', 'br', 'br.id_role = bec.id_role_b2b');

        if (!empty($filters['businessEntityId'])) {
            $qb->andWhere('be.id_business_entity = :businessEntityId')
                ->setParameter('businessEntityId', $filters['businessEntityId']);
        }

        if (!empty($filters['search'])) {
            $qb->andWhere('c.firstname LIKE :search OR c.lastname LIKE :search OR c.email LIKE :search')
                ->setParameter('search', '%' . $filters['search'] . '%');
        }

        foreach ($filters as $filterName => $filterValue) {
            if ($filterValue === '' || $filterValue === null) {
                continue;
            }
            if ('active' === $filterName) {
                $qb->andWhere('c.active = :active')
                    ->setParameter('active', $filterValue);
            }
        }

        return $qb;
    }
}
