<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Core\Domain\BusinessEntity\QueryResult;

class DuplicateBusinessIdentifierResult
{
    /**
     * @param array<int, array{value: string, businessEntityName: string}> $duplicates
     */
    public function __construct(
        private readonly array $duplicates,
    ) {
    }

    /**
     * @return array<int, array{value: string, businessEntityName: string}>
     */
    public function getDuplicates(): array
    {
        return $this->duplicates;
    }

    public function hasDuplicates(): bool
    {
        return count($this->duplicates) > 0;
    }
}
