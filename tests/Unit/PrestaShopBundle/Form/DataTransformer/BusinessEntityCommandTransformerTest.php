<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Tests\Unit\PrestaShopBundle\Form\DataTransformer;

use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\ValueObject\BusinessEntityIdentifierData;
use PrestaShopBundle\Entity\Enum\BusinessEntityStatus;
use PrestaShopBundle\Form\DataTransformer\BusinessEntityCommandTransformer;

class BusinessEntityCommandTransformerTest extends TestCase
{
    public function testItBuildsBusinessEntityIdentifierDataFromPayload(): void
    {
        $result = (new BusinessEntityCommandTransformer())->reverseTransform($this->buildValue([
            ['business_identifier_id' => '1', 'value' => 'FR123'],
            ['business_identifier_id' => '2', 'value' => 'SIREN456'],
        ]));

        $this->assertCount(2, $result['identifiers']);
        $this->assertContainsOnlyInstancesOf(BusinessEntityIdentifierData::class, $result['identifiers']);
        $this->assertSame(1, $result['identifiers'][0]->getBusinessIdentifierId());
        $this->assertSame('FR123', $result['identifiers'][0]->getValue());
        $this->assertSame(2, $result['identifiers'][1]->getBusinessIdentifierId());
        $this->assertSame('SIREN456', $result['identifiers'][1]->getValue());
    }

    public function testItReturnsEmptyIdentifiersWhenNoneSubmitted(): void
    {
        $value = $this->buildValue([]);
        unset($value['identifiers']);

        $result = (new BusinessEntityCommandTransformer())->reverseTransform($value);

        $this->assertSame([], $result['identifiers']);
    }

    public function testItReturnsNullWhenValueIsNull(): void
    {
        $this->assertNull((new BusinessEntityCommandTransformer())->reverseTransform(null));
    }

    /**
     * @param array<int, array<string, string>> $identifiers
     *
     * @return array<string, mixed>
     */
    private function buildValue(array $identifiers): array
    {
        return [
            'general_information' => [
                'name' => 'BE',
                'legal_name' => 'BE SAS',
                'external_ref' => 'REF',
                'delivery_authorized' => false,
                'status' => BusinessEntityStatus::PENDING,
                'customer_group_id' => 3,
            ],
            'identifiers' => $identifiers,
            'billing_address' => [],
            'shipping_address' => [],
            'default_billing_address' => 1,
            'default_shipping_address' => 0,
            'shop_id' => 1,
            'billingAddressAsShippingAddress' => true,
        ];
    }
}
