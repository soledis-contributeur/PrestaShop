<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

namespace PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\BusinessEntity;

use PrestaShop\PrestaShop\Core\Grid\Action\Row\AbstractRowAction;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\AccessibilityChecker\AccessibilityCheckerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DeleteBusinessEntityRowAction extends AbstractRowAction
{
    public function getType()
    {
        return 'delete_business_entity';
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired([
                'business_entity_id_field',
                'business_entity_delete_route',
                'business_entity_name_field',
                'business_entity_customers_count_field',
            ])
            ->setDefaults([
                'accessibility_checker' => null,
            ])
            ->setAllowedTypes('business_entity_id_field', 'string')
            ->setAllowedTypes('business_entity_delete_route', 'string')
            ->setAllowedTypes('business_entity_name_field', 'string')
            ->setAllowedTypes('business_entity_customers_count_field', 'string')
            ->setAllowedTypes('accessibility_checker', [AccessibilityCheckerInterface::class, 'callable', 'null']);
    }

    public function isApplicable(array $record)
    {
        $accessibilityChecker = $this->getOptions()['accessibility_checker'];

        if ($accessibilityChecker instanceof AccessibilityCheckerInterface) {
            return $accessibilityChecker->isGranted($record);
        }

        if (is_callable($accessibilityChecker)) {
            return call_user_func($accessibilityChecker, $record);
        }

        return parent::isApplicable($record);
    }
}
