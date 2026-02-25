<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Adapter\Address\Repository;

use Address;
use PrestaShop\PrestaShop\Adapter\Address\DTO\NewAddress;
use PrestaShop\PrestaShop\Core\Domain\Address\Exception\AddressNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\Address\ValueObject\AddressId;
use PrestaShop\PrestaShop\Core\Domain\AttributeGroup\Attribute\Exception\AttributeNotFoundException;
use PrestaShop\PrestaShop\Core\Exception\CoreException;
use PrestaShop\PrestaShop\Core\Repository\AbstractMultiShopObjectModelRepository;

/**
 * Provides access to address data source
 */
class AddressRepository extends AbstractMultiShopObjectModelRepository
{
    /**
     * @param AddressId $addressId
     *
     * @return Address
     *
     * @throws AttributeNotFoundException
     * @throws CoreException
     */
    public function get(AddressId $addressId): Address
    {
        /** @var Address $address */
        $address = $this->getObjectModel(
            $addressId->getValue(),
            Address::class,
            AddressNotFoundException::class
        );

        return $address;
    }

    /**
     * @throws CoreException
     */
    public function add(NewAddress $address): AddressId
    {
        $addressModel = new Address();

        // required fields
        $addressModel->alias = $address->getAlias();
        $addressModel->address1 = $address->getAddress1();
        $addressModel->lastname = $address->getLastName();
        $addressModel->firstname = $address->getFirstName();
        $addressModel->city = $address->getCity();
        $addressModel->postcode = $address->getPostcode();
        $addressModel->id_country = $address->getCountryId()->getValue();

        // optional fields
        $addressModel->id_state = $address->getStateId();
        $addressModel->id_customer = $address->getCustomerId();
        $addressModel->id_manufacturer = $address->getManufacturerId();
        $addressModel->id_supplier = $address->getSupplierId();
        $addressModel->address2 = $address->getAddress2();
        $addressModel->phone = $address->getPhone();
        $addressModel->phone_mobile = $address->getPhoneMobile();
        $addressModel->dni = $address->getDni();
        $addressModel->company = $address->getCompany();
        $addressModel->vat_number = $address->getVatNumber();
        $addressModel->other = $address->getOther();

        $addressModel->deleted = $address->isDeleted();

        $addressId = $this->addObjectModel($addressModel, Address::class);

        return new AddressId($addressId);
    }

    /**
     * @throws CoreException
     * @throws AttributeNotFoundException
     */
    public function delete(AddressId $addressId): void
    {
        $this->deleteObjectModel(
            $this->get($addressId),
            Address::class
        );
    }
}
