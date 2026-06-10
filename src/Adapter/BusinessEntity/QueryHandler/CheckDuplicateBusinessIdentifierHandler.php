<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Adapter\BusinessEntity\QueryHandler;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsQueryHandler;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Query\CheckDuplicateBusinessIdentifier;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\QueryHandler\CheckDuplicateBusinessIdentifierHandlerInterface;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\QueryResult\DuplicateBusinessIdentifierResult;

#[AsQueryHandler]
final class CheckDuplicateBusinessIdentifierHandler implements CheckDuplicateBusinessIdentifierHandlerInterface
{
    private const BILLING_ADDRESS_TYPES = ['invoice', 'both'];

    public function __construct(
        private readonly Connection $connection,
        private readonly string $dbPrefix,
    ) {
    }

    public function handle(CheckDuplicateBusinessIdentifier $query): DuplicateBusinessIdentifierResult
    {
        $currentBusinessEntityId = $query->getCurrentBusinessEntityId();
        $countryId = $query->getCountryId();

        if (null !== $currentBusinessEntityId && $countryId <= 0) {
            $countryId = (int) $this->getBillingCountryId($currentBusinessEntityId);
        }

        if ($countryId <= 0) {
            return new DuplicateBusinessIdentifierResult([]);
        }

        $duplicates = [];

        foreach ($query->getIdentifiers() as $identifier) {
            $value = trim($identifier->getValue());
            if ('' === $value) {
                continue;
            }

            $businessEntityName = $this->findDuplicateBusinessEntityName(
                $value,
                $identifier->getBusinessIdentifierId(),
                $countryId,
                $currentBusinessEntityId,
            );

            if (null !== $businessEntityName) {
                $duplicates[] = [
                    'value' => $value,
                    'businessEntityName' => $businessEntityName,
                ];
            }
        }

        return new DuplicateBusinessIdentifierResult($duplicates);
    }

    private function getBillingCountryId(int $businessEntityId): ?int
    {
        $countryId = $this->connection->createQueryBuilder()
            ->select('a.id_country')
            ->from($this->dbPrefix . 'business_entity_address', 'bea')
            ->innerJoin('bea', $this->dbPrefix . 'address', 'a', 'a.id_address = bea.id_address')
            ->where('bea.id_business_entity = :businessEntityId')
            ->andWhere('bea.address_type IN (:addressTypes)')
            ->andWhere('a.deleted = 0')
            ->orderBy('bea.is_default', 'DESC')
            ->setParameter('businessEntityId', $businessEntityId)
            ->setParameter('addressTypes', self::BILLING_ADDRESS_TYPES, ArrayParameterType::STRING)
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        return false !== $countryId ? (int) $countryId : null;
    }

    private function findDuplicateBusinessEntityName(
        string $value,
        int $businessIdentifierId,
        int $countryId,
        ?int $currentBusinessEntityId,
    ): ?string {
        $qb = $this->connection->createQueryBuilder()
            ->select('be.name')
            ->from($this->dbPrefix . 'business_entity_identifier', 'bei')
            ->innerJoin('bei', $this->dbPrefix . 'business_entity', 'be', 'be.id_business_entity = bei.id_business_entity')
            ->innerJoin('be', $this->dbPrefix . 'business_entity_address', 'bea', 'bea.id_business_entity = be.id_business_entity')
            ->innerJoin('bea', $this->dbPrefix . 'address', 'a', 'a.id_address = bea.id_address')
            ->where('bei.value = :value')
            ->andWhere('bei.id_business_identifier = :businessIdentifierId')
            ->andWhere('a.id_country = :countryId')
            ->andWhere('bea.address_type IN (:addressTypes)')
            ->andWhere('be.deleted = 0')
            ->andWhere('a.deleted = 0')
            ->setParameter('value', $value)
            ->setParameter('businessIdentifierId', $businessIdentifierId)
            ->setParameter('countryId', $countryId)
            ->setParameter('addressTypes', self::BILLING_ADDRESS_TYPES, ArrayParameterType::STRING)
            ->setMaxResults(1);

        if (null !== $currentBusinessEntityId) {
            $qb
                ->andWhere('be.id_business_entity != :currentBusinessEntityId')
                ->setParameter('currentBusinessEntityId', $currentBusinessEntityId);
        }

        $name = $qb->executeQuery()->fetchOne();

        return false !== $name ? (string) $name : null;
    }
}
