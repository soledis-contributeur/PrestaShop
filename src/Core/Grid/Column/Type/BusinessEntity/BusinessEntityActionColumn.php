<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Core\Grid\Column\Type\BusinessEntity;

use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\AbstractColumn;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class BusinessEntityActionColumn extends AbstractColumn
{
    public function getType()
    {
        return 'business_entity_action';
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'actions' => null,
            ])
            ->setAllowedTypes('actions', ['null', RowActionCollection::class]);
    }
}
