<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Tests\Unit\Core\Domain\BusinessEntity\QueryResult;

use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\QueryResult\AddressForViewing;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\QueryResult\BusinessEntityForViewing;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\QueryResult\IdentifierForViewing;

class BusinessEntityForViewingTest extends TestCase
{
    public function testItExposesAllConstructorParamsViaGetters(): void
    {
        $invoice = [$this->buildAddress(1, 'invoice', true)];
        $delivery = [$this->buildAddress(2, 'delivery', false)];
        $identifiers = [new IdentifierForViewing(7, 'VAT number', 'FR123')];

        $businessEntity = new BusinessEntityForViewing(
            10,
            'EXT-1',
            'Tan Emporium',
            'Tan Emporium SAS',
            true,
            'active',
            '2026-01-01 10:00:00',
            '2026-02-02 11:00:00',
            3,
            5,
            'Customers B2B',
            $invoice,
            $delivery,
            $identifiers,
        );

        $this->assertSame(10, $businessEntity->getBusinessEntityId());
        $this->assertSame('EXT-1', $businessEntity->getExternalRef());
        $this->assertSame('Tan Emporium', $businessEntity->getName());
        $this->assertSame('Tan Emporium SAS', $businessEntity->getLegalName());
        $this->assertTrue($businessEntity->isDeliveryAuthorized());
        $this->assertSame('active', $businessEntity->getStatus());
        $this->assertSame('2026-01-01 10:00:00', $businessEntity->getCreatedAt());
        $this->assertSame('2026-02-02 11:00:00', $businessEntity->getUpdatedAt());
        $this->assertSame(3, $businessEntity->getLinkedCustomersCount());
        $this->assertSame(5, $businessEntity->getCustomerGroupId());
        $this->assertSame('Customers B2B', $businessEntity->getCustomerGroupName());
        $this->assertSame($invoice, $businessEntity->getInvoiceAddresses());
        $this->assertSame($delivery, $businessEntity->getDeliveryAddresses());
        $this->assertSame($identifiers, $businessEntity->getIdentifiers());
    }

    /**
     * @dataProvider initialsProvider
     */
    public function testItComputesInitialsFromName(string $name, string $expected): void
    {
        $this->assertSame($expected, $this->buildBusinessEntity($name)->getInitials());
    }

    public function initialsProvider(): array
    {
        return [
            'two words' => ['Tan Emporium', 'TE'],
            'single word' => ['Business', 'B'],
            'more than two words keeps first two' => ['Alpha Beta Gamma', 'AB'],
            'extra spaces' => ['  multiple   spaces  here ', 'MS'],
            'lowercase is upper-cased' => ['acme corp', 'AC'],
            'accented first letters' => ['élan vital', 'ÉV'],
            'empty name' => ['', ''],
        ];
    }

    public function testAddressesCountDeduplicatesSharedAddressIds(): void
    {
        $businessEntity = new BusinessEntityForViewing(
            1,
            null,
            'Name',
            null,
            false,
            'pending',
            '2026-01-01 00:00:00',
            '2026-01-01 00:00:00',
            0,
            1,
            'Visitor',
            [$this->buildAddress(1, 'both', true), $this->buildAddress(2, 'invoice', false)],
            [$this->buildAddress(1, 'both', true), $this->buildAddress(3, 'delivery', false)],
            [],
        );

        $this->assertSame(3, $businessEntity->getAddressesCount());
    }

    public function testAddressesCountIsZeroWithoutAddresses(): void
    {
        $this->assertSame(0, $this->buildBusinessEntity('Name')->getAddressesCount());
    }

    private function buildBusinessEntity(string $name): BusinessEntityForViewing
    {
        return new BusinessEntityForViewing(
            1,
            null,
            $name,
            null,
            false,
            'pending',
            '2026-01-01 00:00:00',
            '2026-01-01 00:00:00',
            0,
            1,
            'Visitor',
            [],
            [],
            [],
        );
    }

    private function buildAddress(int $id, string $type, bool $isDefault): AddressForViewing
    {
        return new AddressForViewing(
            $id,
            'Alias',
            '1 Street',
            null,
            '75000',
            'Paris',
            'France',
            'Company',
            'FR123',
            $type,
            $isDefault,
        );
    }
}
