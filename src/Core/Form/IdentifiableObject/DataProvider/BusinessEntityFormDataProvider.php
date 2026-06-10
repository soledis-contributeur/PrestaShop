<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

namespace PrestaShop\PrestaShop\Core\Form\IdentifiableObject\DataProvider;

use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Core\Context\ShopContext;
use PrestaShopBundle\Entity\Enum\BusinessEntityStatus;
use PrestaShopBundle\Entity\Repository\BusinessEntityRepository;
use PrestaShopBundle\Form\Admin\Sell\BusinessEntity\BusinessEntityAddressType;
use PrestaShopBundle\Form\Admin\Sell\BusinessEntity\BusinessEntityGeneralInformationType;
use PrestaShopBundle\Form\Admin\Sell\BusinessEntity\BusinessEntityIdentifierType;

final class BusinessEntityFormDataProvider implements FormDataProviderInterface
{
    public const DEFAULT_BILLING_ADDRESS_INDEX = 1;
    public const DEFAULT_SHIPPING_ADDRESS_INDEX = 0;

    private const DEFAULT_CUSTOMER_GROUP_ID = 3;

    private readonly int $defaultCountryId;

    public function __construct(
        Configuration $configuration,
        private readonly ShopContext $shopContext,
        private readonly BusinessEntityRepository $businessEntityRepository,
    ) {
        $this->defaultCountryId = (int) $configuration->get('PS_COUNTRY_DEFAULT');
    }

    /**
     * {@inheritDoc}
     */
    public function getData($id)
    {
        $businessEntity = $this->businessEntityRepository->getBusinessEntityById((int) $id);

        if (null === $businessEntity) {
            return $this->getDefaultData();
        }

        $identifiers = [];
        foreach ($businessEntity->getBusinessEntityIdentifiers() as $businessEntityIdentifier) {
            $identifiers[] = [
                BusinessEntityIdentifierType::FIELD_BUSINESS_IDENTIFIER_ID => $businessEntityIdentifier->getBusinessIdentifier()->getId(),
                BusinessEntityIdentifierType::FIELD_VALUE => $businessEntityIdentifier->getValue(),
            ];
        }

        return [
            'general_information' => [
                BusinessEntityGeneralInformationType::FIELD_NAME => $businessEntity->getName(),
                BusinessEntityGeneralInformationType::FIELD_LEGAL_NAME => $businessEntity->getLegalName() ?? '',
                BusinessEntityGeneralInformationType::FIELD_EXTERNAL_REF => $businessEntity->getExternalRef() ?? '',
                BusinessEntityGeneralInformationType::FIELD_DELIVERY_AUTHORIZED => $businessEntity->isDeliveryAuthorized(),
                BusinessEntityGeneralInformationType::FIELD_STATUS => $businessEntity->getStatus(),
                BusinessEntityGeneralInformationType::FIELD_CUSTOMER_GROUP_ID => $businessEntity->getIdCustomerGroup(),
            ],
            'identifiers' => $identifiers,
            'billing_address' => [],
            'shipping_address' => [],
            'billingAddressAsShippingAddress' => true,
            'default_billing_address' => self::DEFAULT_BILLING_ADDRESS_INDEX,
            'default_shipping_address' => self::DEFAULT_SHIPPING_ADDRESS_INDEX,
            'shop_id' => $businessEntity->getIdShop(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultData()
    {
        return [
            'general_information' => [
                BusinessEntityGeneralInformationType::FIELD_NAME => '',
                BusinessEntityGeneralInformationType::FIELD_LEGAL_NAME => '',
                BusinessEntityGeneralInformationType::FIELD_EXTERNAL_REF => '',
                BusinessEntityGeneralInformationType::FIELD_DELIVERY_AUTHORIZED => false,
                BusinessEntityGeneralInformationType::FIELD_STATUS => BusinessEntityStatus::PENDING,
                BusinessEntityGeneralInformationType::FIELD_CUSTOMER_GROUP_ID => self::DEFAULT_CUSTOMER_GROUP_ID,
            ],
            'identifiers' => [],
            'billing_address' => [
                self::DEFAULT_BILLING_ADDRESS_INDEX => [
                    BusinessEntityAddressType::FIELD_COUNTRY_ID => $this->defaultCountryId,
                ],
            ],
            'shipping_address' => [
            ],
            'billingAddressAsShippingAddress' => true,
            'default_billing_address' => self::DEFAULT_BILLING_ADDRESS_INDEX,
            'default_shipping_address' => self::DEFAULT_SHIPPING_ADDRESS_INDEX,
            'shop_id' => $this->shopContext->isSingleShopContext() ? $this->shopContext->getId() : null,
        ];
    }
}
