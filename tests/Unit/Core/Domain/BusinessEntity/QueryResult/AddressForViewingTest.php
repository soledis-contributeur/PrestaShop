<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Tests\Unit\Core\Domain\BusinessEntity\QueryResult;

use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\QueryResult\AddressForViewing;

class AddressForViewingTest extends TestCase
{
    public function testItExposesAllConstructorParamsViaGetters(): void
    {
        $address = new AddressForViewing(
            12,
            'Warehouse',
            '1 Place des Ternes',
            'Building B',
            '75017',
            'Paris',
            'France',
            'Tan Emporium SAS',
            'FR12345678901',
            'both',
            true,
        );

        $this->assertSame(12, $address->getAddressId());
        $this->assertSame('Warehouse', $address->getAlias());
        $this->assertSame('1 Place des Ternes', $address->getAddress1());
        $this->assertSame('Building B', $address->getAddress2());
        $this->assertSame('75017', $address->getPostcode());
        $this->assertSame('Paris', $address->getCity());
        $this->assertSame('France', $address->getCountry());
        $this->assertSame('Tan Emporium SAS', $address->getCompany());
        $this->assertSame('FR12345678901', $address->getVatNumber());
        $this->assertSame('both', $address->getAddressType());
        $this->assertTrue($address->isDefault());
    }

    public function testItAcceptsNullableOptionalFields(): void
    {
        $address = new AddressForViewing(
            1,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            'invoice',
            false,
        );

        $this->assertNull($address->getAlias());
        $this->assertNull($address->getAddress1());
        $this->assertNull($address->getVatNumber());
        $this->assertSame('invoice', $address->getAddressType());
        $this->assertFalse($address->isDefault());
    }
}
