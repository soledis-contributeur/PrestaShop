<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Query;

use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\ValueObject\BusinessEntityIdentifierData;

class CheckDuplicateBusinessIdentifier
{
    /**
     * @param BusinessEntityIdentifierData[] $identifiers
     */
    public function __construct(
        private readonly array $identifiers,
        private readonly int $countryId,
        private readonly ?int $currentBusinessEntityId = null,
    ) {
    }

    /**
     * @return BusinessEntityIdentifierData[]
     */
    public function getIdentifiers(): array
    {
        return $this->identifiers;
    }

    public function getCountryId(): int
    {
        return $this->countryId;
    }

    public function getCurrentBusinessEntityId(): ?int
    {
        return $this->currentBusinessEntityId;
    }
}
