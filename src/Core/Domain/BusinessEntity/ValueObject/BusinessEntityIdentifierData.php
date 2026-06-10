<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

namespace PrestaShop\PrestaShop\Core\Domain\BusinessEntity\ValueObject;

final class BusinessEntityIdentifierData
{
    public function __construct(
        private readonly int $businessIdentifierId,
        private readonly string $value,
    ) {
    }

    public function getBusinessIdentifierId(): int
    {
        return $this->businessIdentifierId;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
