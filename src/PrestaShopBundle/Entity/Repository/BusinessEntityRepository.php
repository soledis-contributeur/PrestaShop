<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShopBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use PrestaShopBundle\Entity\B2B\BusinessEntity;
use PrestaShopBundle\Entity\B2B\BusinessEntityCustomerB2b;
use PrestaShopBundle\Entity\Enum\BusinessEntityStatus;

class BusinessEntityRepository extends EntityRepository
{
    public function getBusinessEntityById(int $businessEntityId): ?BusinessEntity
    {
        /** @var BusinessEntity|null $businessEntity */
        $businessEntity = $this->find($businessEntityId);

        return $businessEntity;
    }

    public function getLinkedCustomersCount(int $businessEntityId): int
    {
        return (int) $this->getEntityManager()
            ->createQueryBuilder()
            ->select('COUNT(bec.id)')
            ->from(BusinessEntityCustomerB2b::class, 'bec')
            ->where('bec.businessEntity = :businessEntityId')
            ->setParameter('businessEntityId', $businessEntityId)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function getPendingCount(): int
    {
        return (int) $this->createQueryBuilder('be')
            ->select('COUNT(be.id)')
            ->where('be.status = :status')
            ->setParameter('status', BusinessEntityStatus::PENDING)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
