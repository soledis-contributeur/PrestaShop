<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Core\Domain\BusinessEntity\QueryResult;

class BusinessEntityForViewing
{
    /**
     * @param AddressForViewing[] $invoiceAddresses
     * @param AddressForViewing[] $deliveryAddresses
     * @param IdentifierForViewing[] $identifiers
     */
    public function __construct(
        private readonly int $businessEntityId,
        private readonly ?string $externalRef,
        private readonly string $name,
        private readonly ?string $legalName,
        private readonly bool $deliveryAuthorized,
        private readonly string $status,
        private readonly string $createdAt,
        private readonly string $updatedAt,
        private readonly int $linkedCustomersCount,
        private readonly int $customerGroupId,
        private readonly string $customerGroupName,
        private readonly array $invoiceAddresses,
        private readonly array $deliveryAddresses,
        private readonly array $identifiers,
    ) {
    }

    public function getBusinessEntityId(): int
    {
        return $this->businessEntityId;
    }

    public function getExternalRef(): ?string
    {
        return $this->externalRef;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLegalName(): ?string
    {
        return $this->legalName;
    }

    public function isDeliveryAuthorized(): bool
    {
        return $this->deliveryAuthorized;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function getLinkedCustomersCount(): int
    {
        return $this->linkedCustomersCount;
    }

    public function getCustomerGroupId(): int
    {
        return $this->customerGroupId;
    }

    public function getCustomerGroupName(): string
    {
        return $this->customerGroupName;
    }

    /**
     * @return AddressForViewing[]
     */
    public function getInvoiceAddresses(): array
    {
        return $this->invoiceAddresses;
    }

    /**
     * @return AddressForViewing[]
     */
    public function getDeliveryAddresses(): array
    {
        return $this->deliveryAddresses;
    }

    /**
     * @return IdentifierForViewing[]
     */
    public function getIdentifiers(): array
    {
        return $this->identifiers;
    }

    public function getAddressesCount(): int
    {
        $unique = [];
        foreach ($this->invoiceAddresses as $address) {
            $unique[$address->getAddressId()] = true;
        }
        foreach ($this->deliveryAddresses as $address) {
            $unique[$address->getAddressId()] = true;
        }

        return count($unique);
    }

    public function getInitials(): string
    {
        $initials = '';
        $words = preg_split('/\s+/u', trim($this->name)) ?: [];
        foreach ($words as $word) {
            if ('' === $word) {
                continue;
            }
            $initials .= mb_strtoupper(mb_substr($word, 0, 1));
            if (mb_strlen($initials) >= 2) {
                break;
            }
        }

        return $initials;
    }
}
