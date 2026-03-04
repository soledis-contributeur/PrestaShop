<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Core\Domain\BusinessEntity\QueryResult;

class AddressForViewing
{
    /**
     * @var int
     */
    private $addressId;

    /**
     * @var string|null
     */
    private $alias;

    /**
     * @var string|null
     */
    private $address1;

    /**
     * @var string|null
     */
    private $address2;

    /**
     * @var string|null
     */
    private $postcode;

    /**
     * @var string|null
     */
    private $city;

    /**
     * @var string|null
     */
    private $country;

    /**
     * @var string|null
     */
    private $company;

    /**
     * @var string|null
     */
    private $vat_number;

    public function __construct(
        int $addressId,
        ?string $alias,
        ?string $address1,
        ?string $address2,
        ?string $postcode,
        ?string $city,
        ?string $country,
        ?string $company,
        ?string $vat_number
    ) {
        $this->addressId = $addressId;
        $this->alias = $alias;
        $this->address1 = $address1;
        $this->address2 = $address2;
        $this->postcode = $postcode;
        $this->city = $city;
        $this->country = $country;
        $this->company = $company;
        $this->vat_number = $vat_number;
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
        return $this->vat_number;
    }
}
