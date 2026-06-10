<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

namespace PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Command;

use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Exception\BusinessEntityIdentifierConstraintException;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\ValueObject\BusinessEntityId;
use PrestaShopBundle\Entity\Enum\BusinessEntityStatus;

class EditBusinessEntityCommand
{
    private readonly BusinessEntityId $businessEntityId;

    /**
     * @throws BusinessEntityIdentifierConstraintException
     */
    public function __construct(
        int $businessEntityId,
        private readonly string $name,
        private readonly string $legalName,
        private readonly ?string $externalRef,
        private readonly bool $deliveryAuthorized,
        private readonly BusinessEntityStatus $status,
        private readonly int $customerGroupId,
        private readonly array $identifiers = [],
    ) {
        $this->businessEntityId = new BusinessEntityId($businessEntityId);
        $this->assertAtLeastOneIdentifier();
    }

    public function getBusinessEntityId(): BusinessEntityId
    {
        return $this->businessEntityId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLegalName(): string
    {
        return $this->legalName;
    }

    public function getExternalRef(): ?string
    {
        return $this->externalRef;
    }

    public function isDeliveryAuthorized(): bool
    {
        return $this->deliveryAuthorized;
    }

    public function getStatus(): BusinessEntityStatus
    {
        return $this->status;
    }

    public function getCustomerGroupId(): int
    {
        return $this->customerGroupId;
    }

    public function getIdentifiers(): array
    {
        return $this->identifiers;
    }

    /**
     * @throws BusinessEntityIdentifierConstraintException
     */
    private function assertAtLeastOneIdentifier(): void
    {
        if (!count($this->identifiers)) {
            throw new BusinessEntityIdentifierConstraintException(
                code: BusinessEntityIdentifierConstraintException::MISSING_IDENTIFIER
            );
        }
    }
}
