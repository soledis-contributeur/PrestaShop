<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Adapter\BusinessEntity\QueryHandler;

use PrestaShop\PrestaShop\Adapter\Address\Repository\AddressRepository;
use PrestaShop\PrestaShop\Adapter\BusinessEntity\Repository\BusinessEntityRepository;
use PrestaShop\PrestaShop\Core\Domain\Address\ValueObject\AddressId;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Exception\BusinessEntityNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Query\GetBusinessEntityForViewing;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\QueryHandler\GetBusinessEntityForViewingHandlerInterface;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\QueryResult\AddressForViewing;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\QueryResult\BusinessEntityForViewing;
use PrestaShopBundle\Entity\Enum\AddressTypeEnum;

class GetBusinessEntityForViewingHandler implements GetBusinessEntityForViewingHandlerInterface
{
    private BusinessEntityRepository $businessEntityRepository;
    private AddressRepository $addressRepository;

    public function __construct(BusinessEntityRepository $businessEntityRepository, AddressRepository $addressRepository)
    {
        $this->businessEntityRepository = $businessEntityRepository;
        $this->addressRepository = $addressRepository;
    }

    public function handle(GetBusinessEntityForViewing $query): BusinessEntityForViewing
    {
        $businessEntityId = $query->getBusinessEntityId()->getValue();

        $businessEntity = $this->businessEntityRepository->getBusinessEntityById($businessEntityId);

        if ($businessEntity === null) {
            throw new BusinessEntityNotFoundException(sprintf('Business entity "%d" was not found', $businessEntityId));
        }

        $invoiceAddress = null;
        $deliveryAddress = null;

        foreach ($businessEntity->getBusinessEntityAddresses() as $businessEntityAddress) {
            if ($businessEntityAddress->getAddressType() === AddressTypeEnum::INVOICE || $businessEntityAddress->getAddressType() === AddressTypeEnum::BOTH) {
                $invoiceAddress = $this->addressRepository->get(new AddressId($businessEntityAddress->getAddressId()));
            }
            if ($businessEntityAddress->getAddressType() === AddressTypeEnum::DELIVERY || $businessEntityAddress->getAddressType() === AddressTypeEnum::BOTH) {
                $deliveryAddress = $this->addressRepository->get(new AddressId($businessEntityAddress->getAddressId()));
            }
        }

        $linkedCustomersCount = $this->businessEntityRepository->getLinkedCustomersCount($businessEntityId);

        return new BusinessEntityForViewing(
            $businessEntity->getId(),
            $businessEntity->getEnterpriseId(),
            $businessEntity->getExternalRef(),
            $businessEntity->getName(),
            $businessEntity->getLegalName(),
            $businessEntity->isFlagDeliveryAuthorized(),
            $businessEntity->getStatus()->value,
            $businessEntity->getCreatedAt()->format('Y-m-d'),
            $businessEntity->getUpdatedAt()->format('Y-m-d'),
            $linkedCustomersCount,
            $invoiceAddress ? new AddressForViewing(
                $invoiceAddress->id_address,
                $invoiceAddress->alias,
                $invoiceAddress->address1,
                $invoiceAddress->address2,
                $invoiceAddress->postcode,
                $invoiceAddress->city,
                $invoiceAddress->country,
                $invoiceAddress->company,
                $invoiceAddress->vat_number
            ) : null,
            $deliveryAddress ? new AddressForViewing(
                $deliveryAddress->id_address,
                $deliveryAddress->alias,
                $deliveryAddress->address1,
                $deliveryAddress->address2,
                $deliveryAddress->postcode,
                $deliveryAddress->city,
                $deliveryAddress->country,
                $deliveryAddress->company,
                $deliveryAddress->vat_number
            ) : null
        );
    }

    public function __invoke(GetBusinessEntityForViewing $query): BusinessEntityForViewing
    {
        return $this->handle($query);
    }
}
