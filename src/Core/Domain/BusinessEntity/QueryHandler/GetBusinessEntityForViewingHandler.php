<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Core\Domain\BusinessEntity\QueryHandler;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use PrestaShop\PrestaShop\Adapter\Address\Repository\AddressRepository;
use PrestaShop\PrestaShop\Adapter\Country\Repository\CountryRepository;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsQueryHandler;
use PrestaShop\PrestaShop\Core\Context\LanguageContext;
use PrestaShop\PrestaShop\Core\Domain\Address\Exception\AddressNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\Address\ValueObject\AddressId;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Exception\BusinessEntityNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Query\GetBusinessEntityForViewing;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\QueryResult\AddressForViewing;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\QueryResult\BusinessEntityForViewing;
use PrestaShop\PrestaShop\Core\Domain\Country\Exception\CountryNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\Country\ValueObject\CountryId;
use PrestaShopBundle\Entity\Repository\BusinessEntityRepository;

#[AsQueryHandler]
final class GetBusinessEntityForViewingHandler implements GetBusinessEntityForViewingHandlerInterface
{
    private const INVOICE_ADDRESS_TYPES = ['invoice', 'both'];
    private const DELIVERY_ADDRESS_TYPES = ['delivery', 'both'];

    public function __construct(
        private readonly BusinessEntityRepository $businessEntityRepository,
        private readonly AddressRepository $addressRepository,
        private readonly CountryRepository $countryRepository,
        private readonly Connection $connection,
        private readonly LanguageContext $defaultLanguageContext,
        private readonly string $dbPrefix,
    ) {
    }

    public function handle(GetBusinessEntityForViewing $query): BusinessEntityForViewing
    {
        $businessEntityId = $query->getBusinessEntityId();

        $businessEntity = $this->businessEntityRepository->getBusinessEntityById($businessEntityId);

        if (null === $businessEntity) {
            throw new BusinessEntityNotFoundException(sprintf('Business entity with id %d was not found.', $businessEntityId));
        }

        $invoiceAddress = $this->fetchAddressForBusinessEntity($businessEntityId, self::INVOICE_ADDRESS_TYPES);
        $deliveryAddress = $this->fetchAddressForBusinessEntity($businessEntityId, self::DELIVERY_ADDRESS_TYPES);
        $linkedCustomersCount = $this->businessEntityRepository->getLinkedCustomersCount($businessEntityId);

        return new BusinessEntityForViewing(
            $businessEntity->getId(),
            $businessEntity->getExternalRef(),
            $businessEntity->getName(),
            $businessEntity->getLegalName(),
            $businessEntity->isDeliveryAuthorized(),
            $businessEntity->getStatus()->value,
            $businessEntity->getCreatedAt()->format('Y-m-d H:i:s'),
            $businessEntity->getUpdatedAt()->format('Y-m-d H:i:s'),
            $linkedCustomersCount,
            $invoiceAddress,
            $deliveryAddress
        );
    }

    /**
     * @param string[] $addressTypes
     */
    private function fetchAddressForBusinessEntity(int $businessEntityId, array $addressTypes): ?AddressForViewing
    {
        $addressId = $this->connection->createQueryBuilder()
            ->select('bea.id_address')
            ->from($this->dbPrefix . 'business_entity_address', 'bea')
            ->innerJoin('bea', $this->dbPrefix . 'address', 'a', 'a.id_address = bea.id_address')
            ->where('bea.id_business_entity = :businessEntityId')
            ->andWhere('bea.address_type IN (:addressTypes)')
            ->andWhere('a.deleted = 0')
            ->setParameter('businessEntityId', $businessEntityId)
            ->setParameter('addressTypes', $addressTypes, ArrayParameterType::STRING)
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        if (false === $addressId) {
            return null;
        }

        return $this->buildAddressForViewing((int) $addressId);
    }

    private function buildAddressForViewing(int $addressId): ?AddressForViewing
    {
        try {
            $address = $this->addressRepository->get(new AddressId($addressId));
        } catch (AddressNotFoundException) {
            return null;
        }

        $countryName = '';

        if (!empty($address->id_country)) {
            try {
                $country = $this->countryRepository->get(new CountryId((int) $address->id_country));
                $languageId = (int) $this->defaultLanguageContext->getId();

                if (is_array($country->name) && isset($country->name[$languageId])) {
                    $countryName = (string) $country->name[$languageId];
                } elseif (is_string($country->name)) {
                    $countryName = $country->name;
                }
            } catch (CountryNotFoundException) {
                $countryName = '';
            }
        }

        return new AddressForViewing(
            (int) $address->id,
            (string) $address->alias,
            (string) $address->address1,
            (string) $address->address2,
            (string) $address->postcode,
            (string) $address->city,
            $countryName,
            (string) $address->company,
            (string) $address->vat_number
        );
    }
}
