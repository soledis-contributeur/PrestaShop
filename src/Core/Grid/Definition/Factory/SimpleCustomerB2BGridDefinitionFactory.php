<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Core\Grid\Definition\Factory;

use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\LinkRowAction;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ToggleColumn;
use PrestaShop\PrestaShop\Core\Hook\HookDispatcherInterface;

final class SimpleCustomerB2BGridDefinitionFactory extends AbstractGridDefinitionFactory
{
    public const GRID_ID = 'simple_customer_b2b';

    public function __construct(HookDispatcherInterface $hookDispatcher)
    {
        parent::__construct($hookDispatcher);
    }

    protected function getId()
    {
        return self::GRID_ID;
    }

    protected function getName()
    {
        return $this->trans('Customers B2B', [], 'Admin.Global');
    }

    protected function getColumns()
    {
        return (new ColumnCollection())
            ->add(
                (new DataColumn('id_customer_b2b'))
                    ->setName($this->trans('ID', [], 'Admin.Global'))
                    ->setOptions(['field' => 'id_customer_b2b'])
            )
            ->add(
                (new DataColumn('firstname'))
                    ->setName($this->trans('Firstname', [], 'Admin.Global'))
                    ->setOptions(['field' => 'firstname'])
            )
            ->add(
                (new DataColumn('lastname'))
                    ->setName($this->trans('Name', [], 'Admin.Global'))
                    ->setOptions(['field' => 'lastname'])
            )
            ->add(
                (new DataColumn('email'))
                    ->setName($this->trans('Email', [], 'Admin.Global'))
                    ->setOptions(['field' => 'email'])
            )
            ->add(
                (new DataColumn('role'))
                    ->setName($this->trans('Role', [], 'Admin.Global'))
                    ->setOptions(['field' => 'role'])
            )
            ->add(
                (new ToggleColumn('active'))
                    ->setName($this->trans('Enabled', [], 'Admin.Global'))
                    ->setOptions([
                        'field' => 'active',
                        'primary_field' => 'id_customer',
                        'route' => 'admin_customer_b2b_toggle_status',
                        'route_param_name' => 'customerB2bId',
                    ])
            )
            ->add(
                (new ActionColumn('actions'))
                    ->setName($this->trans('Actions', [], 'Admin.Global'))
                    ->setOptions([
                        'actions' => (new RowActionCollection())
                            ->add(
                                (new LinkRowAction('view'))
                                    ->setName($this->trans('View details', [], 'Admin.Actions'))
                                    ->setIcon('people')
                                    ->setOptions([
                                        'route' => 'admin_customer_b2b_view',
                                        'route_param_name' => 'customerB2bId',
                                        'route_param_field' => 'id_customer',
                                        'clickable_row' => true,
                                    ])
                            ),
                    ])
            );
    }
}
