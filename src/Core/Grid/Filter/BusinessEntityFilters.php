<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Core\Grid\Filter;

use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteria;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

final class BusinessEntityFilters implements SearchCriteriaInterface
{
    public const GRID_ID = 'business_entity';

    private SearchCriteria $criteria;

    public function __construct(array $gridParams = [])
    {
        $filters = $gridParams['filters'] ?? [];
        if (!is_array($filters)) {
            $filters = [];
        }

        $orderBy = (string) ($gridParams['orderBy'] ?? 'id_business_entity');
        $sortOrder = (string) ($gridParams['sortOrder'] ?? 'asc');
        $offset = (int) ($gridParams['offset'] ?? 0);
        $limit = (int) ($gridParams['limit'] ?? 20);

        $this->criteria = new SearchCriteria(
            $filters,
            $orderBy,
            $sortOrder,
            $offset,
            $limit
        );
    }

    public function getFilters(): array
    {
        return $this->criteria->getFilters();
    }

    public function getOrderBy(): string
    {
        return $this->criteria->getOrderBy();
    }

    public function getOrderWay(): string
    {
        return $this->criteria->getOrderWay();
    }

    public function getLimit(): int
    {
        return $this->criteria->getLimit();
    }

    public function getOffset(): int
    {
        return $this->criteria->getOffset();
    }
}
