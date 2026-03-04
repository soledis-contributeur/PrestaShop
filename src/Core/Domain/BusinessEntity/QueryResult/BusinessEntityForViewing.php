<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Core\Domain\BusinessEntity\QueryResult;

class BusinessEntityForViewing
{
    private int $businessEntityId;
    private string $enterpriseId;
    private ?string $externalRef;
    private string $name;
    private ?string $legalName;
    private bool $deliveryAuthorized;
    private string $status;
    private string $createdAt;
    private string $updatedAt;

    private int $linkedCustomersCount;

    private ?AddressForViewing $invoiceAddress;
    private ?AddressForViewing $deliveryAddress;

    public function __construct(
        int $businessEntityId,
        string $enterpriseId,
        ?string $externalRef,
        string $name,
        ?string $legalName,
        bool $deliveryAuthorized,
        string $status,
        string $createdAt,
        string $updatedAt,
        int $linkedCustomersCount,
        ?AddressForViewing $invoiceAddress,
        ?AddressForViewing $deliveryAddress
    ) {
        $this->businessEntityId = $businessEntityId;
        $this->enterpriseId = $enterpriseId;
        $this->externalRef = $externalRef;
        $this->name = $name;
        $this->legalName = $legalName;
        $this->deliveryAuthorized = $deliveryAuthorized;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->linkedCustomersCount = $linkedCustomersCount;
        $this->invoiceAddress = $invoiceAddress;
        $this->deliveryAddress = $deliveryAddress;
    }

    public function getBusinessEntityId(): int
    {
        return $this->businessEntityId;
    }

    public function getEnterpriseId(): string
    {
        return $this->enterpriseId;
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

    public function getInvoiceAddress(): ?AddressForViewing
    {
        return $this->invoiceAddress;
    }

    public function getDeliveryAddress(): ?AddressForViewing
    {
        return $this->deliveryAddress;
    }
}
