<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Tests\Unit\Core\Domain\BusinessEntity\ValueObject;

use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\ValueObject\BusinessEntityIdentifierData;

class BusinessEntityIdentifierDataTest extends TestCase
{
    public function testItExposesAllConstructorParamsViaGetters(): void
    {
        $identifierData = new BusinessEntityIdentifierData(7, 'FR123456789');

        $this->assertSame(7, $identifierData->getBusinessIdentifierId());
        $this->assertSame('FR123456789', $identifierData->getValue());
    }
}
