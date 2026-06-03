<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Adapter\BusinessEntity\QueryHandler;

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
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\QueryHandler\GetBusinessEntityForViewingHandlerInterface;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\QueryResult\AddressForViewing;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\QueryResult\BusinessEntityForViewing;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\QueryResult\IdentifierForViewing;
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

        $invoiceAddresses = $this->fetchAddressesForBusinessEntity($businessEntityId, self::INVOICE_ADDRESS_TYPES);
        $deliveryAddresses = $this->fetchAddressesForBusinessEntity($businessEntityId, self::DELIVERY_ADDRESS_TYPES);
        $identifiers = $this->fetchIdentifiersForBusinessEntity($businessEntityId);
        $linkedCustomersCount = $this->businessEntityRepository->getLinkedCustomersCount($businessEntityId);
        $customerGroupName = $this->fetchCustomerGroupName($businessEntity->getIdCustomerGroup());

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
            $businessEntity->getIdCustomerGroup(),
            $customerGroupName,
            $invoiceAddresses,
            $deliveryAddresses,
            $identifiers,
        );
    }

    private function fetchCustomerGroupName(int $idGroup): string
    {
        $name = $this->connection->createQueryBuilder()
            ->select('gl.name')
            ->from($this->dbPrefix . 'group_lang', 'gl')
            ->where('gl.id_group = :idGroup')
            ->andWhere('gl.id_lang = :idLang')
            ->setParameter('idGroup', $idGroup)
            ->setParameter('idLang', (int) $this->defaultLanguageContext->getId())
            ->executeQuery()
            ->fetchOne();

        return false !== $name ? (string) $name : '';
    }

    /**
     * @param string[] $addressTypes
     *
     * @return AddressForViewing[]
     */
    private function fetchAddressesForBusinessEntity(int $businessEntityId, array $addressTypes): array
    {
        $rows = $this->connection->createQueryBuilder()
            ->select('bea.id_address', 'bea.address_type', 'bea.is_default')
            ->from($this->dbPrefix . 'business_entity_address', 'bea')
            ->innerJoin('bea', $this->dbPrefix . 'address', 'a', 'a.id_address = bea.id_address')
            ->where('bea.id_business_entity = :businessEntityId')
            ->andWhere('bea.address_type IN (:addressTypes)')
            ->andWhere('a.deleted = 0')
            ->orderBy('bea.is_default', 'DESC')
            ->addOrderBy('bea.id_business_entity_address', 'ASC')
            ->setParameter('businessEntityId', $businessEntityId)
            ->setParameter('addressTypes', $addressTypes, ArrayParameterType::STRING)
            ->executeQuery()
            ->fetchAllAssociative();

        $addresses = [];
        foreach ($rows as $row) {
            $address = $this->buildAddressForViewing(
                (int) $row['id_address'],
                (string) $row['address_type'],
                (bool) $row['is_default'],
            );
            if (null !== $address) {
                $addresses[] = $address;
            }
        }

        return $addresses;
    }

    private function buildAddressForViewing(int $addressId, string $addressType, bool $isDefault): ?AddressForViewing
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
            (string) $address->vat_number,
            $addressType,
            $isDefault,
        );
    }

    /**
     * @return IdentifierForViewing[]
     */
    private function fetchIdentifiersForBusinessEntity(int $businessEntityId): array
    {
        $rows = $this->connection->createQueryBuilder()
            ->select('bi.id_business_identifier', 'bi.label', 'bei.value')
            ->from($this->dbPrefix . 'business_entity_identifier', 'bei')
            ->innerJoin('bei', $this->dbPrefix . 'business_identifier', 'bi', 'bi.id_business_identifier = bei.id_business_identifier')
            ->where('bei.id_business_entity = :businessEntityId')
            ->andWhere('bi.deleted = 0')
            ->orderBy('bi.id_business_identifier', 'ASC')
            ->setParameter('businessEntityId', $businessEntityId)
            ->executeQuery()
            ->fetchAllAssociative();

        $identifiers = [];
        foreach ($rows as $row) {
            $identifiers[] = new IdentifierForViewing(
                (int) $row['id_business_identifier'],
                (string) $row['label'],
                null !== $row['value'] ? (string) $row['value'] : null,
            );
        }

        return $identifiers;
    }
}
