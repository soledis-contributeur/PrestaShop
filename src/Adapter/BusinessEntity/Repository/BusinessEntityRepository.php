<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

namespace PrestaShop\PrestaShop\Adapter\BusinessEntity\Repository;

use Doctrine\ORM\EntityManagerInterface;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\QueryResult\AddressForViewing;
use PrestaShopBundle\Entity\B2B\BusinessEntity;

class BusinessEntityRepository
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getBusinessEntityById(int $businessEntityId): ?BusinessEntity
    {
        return $this->entityManager->getRepository(BusinessEntity::class)->find($businessEntityId);
    }

    public function fetchAddressForBusinessEntity(int $businessEntityId, array $addressTypes): ?AddressForViewing
    {
        $query = $this->entityManager->createQuery(
            'SELECT a
            FROM PrestaShopBundle\Entity\Address\Entity\Address a
            JOIN a.businessEntityAddresses bea
            WHERE bea.businessEntity = :businessEntityId
            AND bea.addressType IN (:addressTypes)
            AND a.deleted = 0'
        )
            ->setParameter('businessEntityId', $businessEntityId)
            ->setParameter('addressTypes', $addressTypes);

        $address = $query->getOneOrNullResult();

        if (!$address) {
            return null;
        }

        return new AddressForViewing(
            $address->getId(),
            $address->getAlias(),
            $address->getAddress1(),
            $address->getAddress2(),
            $address->getPostcode(),
            $address->getCity(),
            $address->getCountry(),
            $address->getCompany(),
            $address->getVatNumber()
        );
    }

    public function getLinkedCustomersCount(int $businessEntityId): int
    {
        $query = $this->entityManager->createQuery(
            'SELECT COUNT(bec.id)
            FROM PrestaShopBundle\Entity\B2B\BusinessEntityCustomerB2b bec
            WHERE bec.businessEntity = :businessEntityId'
        )
            ->setParameter('businessEntityId', $businessEntityId);

        return (int) $query->getSingleScalarResult();
    }

    public function getPendingCount(): int
    {
        $query = $this->entityManager->createQuery(
            'SELECT COUNT(be.id)
            FROM PrestaShopBundle\Entity\B2B\BusinessEntity be
            WHERE be.status = :status'
        )
            ->setParameter('status', 'pending');

        return (int) $query->getSingleScalarResult();
    }
}
