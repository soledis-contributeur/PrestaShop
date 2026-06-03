<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Core\Domain\BusinessEntity\QueryResult;

class AddressForViewing
{
    public function __construct(
        private readonly int $addressId,
        private readonly ?string $alias,
        private readonly ?string $address1,
        private readonly ?string $address2,
        private readonly ?string $postcode,
        private readonly ?string $city,
        private readonly ?string $country,
        private readonly ?string $company,
        private readonly ?string $vatNumber,
        private readonly string $addressType,
        private readonly bool $isDefault,
    ) {
    }

    public function getAddressId(): int
    {
        return $this->addressId;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function getAddress1(): ?string
    {
        return $this->address1;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function getVatNumber(): ?string
    {
        return $this->vatNumber;
    }

    public function getAddressType(): string
    {
        return $this->addressType;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }
}
