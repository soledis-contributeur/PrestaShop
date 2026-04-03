<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Core\Grid\Definition\Factory;

use PrestaShop\PrestaShop\Core\Context\ShopContext;
use PrestaShop\PrestaShop\Core\Grid\Action\Bulk\BulkActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\GridActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\BusinessEntity\DeleteBusinessEntityRowAction;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\LinkRowAction;
use PrestaShop\PrestaShop\Core\Grid\Action\Type\SimpleGridAction;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\BusinessEntity\BusinessEntityActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\BadgeColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\BulkActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use PrestaShop\PrestaShop\Core\Grid\Filter\FilterCollection;
use PrestaShop\PrestaShop\Core\Hook\HookDispatcherInterface;
use PrestaShopBundle\Form\Admin\Type\SearchAndResetType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class BusinessEntityGridDefinitionFactory defines business entities grid structure.
 */
final class BusinessEntityGridDefinitionFactory extends AbstractGridDefinitionFactory
{
    use BulkDeleteActionTrait;
    use DeleteActionTrait;

    public const GRID_ID = 'business_entity';

    public function __construct(
        HookDispatcherInterface $hookDispatcher,
        private readonly ShopContext $shopContext,
    ) {
        parent::__construct($hookDispatcher);
    }

    protected function getId()
    {
        return self::GRID_ID;
    }

    protected function getName()
    {
        return $this->trans('Business entities', [], 'Admin.Global');
    }

    protected function getColumns()
    {
        $columns = (new ColumnCollection())
            ->add(
                (new BulkActionColumn('business_entities_bulk'))
                    ->setOptions([
                        'bulk_field' => 'id_business_entity',
                    ])
            )
            ->add(
                (new DataColumn('id_business_entity'))
                    ->setName($this->trans('ID', [], 'Admin.Global'))
                    ->setOptions([
                        'field' => 'id_business_entity',
                        'alignment' => 'center',
                    ])
            )
            ->add(
                (new DataColumn('name'))
                    ->setName($this->trans('Company', [], 'Admin.Global'))
                    ->setOptions([
                        'field' => 'name',
                    ])
            )
            ->add(
                (new DataColumn('legal_name'))
                    ->setName($this->trans('Legal name', [], 'Admin.Global'))
                    ->setOptions([
                        'field' => 'legal_name',
                    ])
            )
            ->add(
                (new DataColumn('vat_number'))
                    ->setName($this->trans('VAT number', [], 'Admin.Global'))
                    ->setOptions([
                        'field' => 'vat_number',
                    ])
            );

        if ($this->shopContext->isMultiShopUsed()) {
            $columns->add(
                (new DataColumn('shop_name'))
                    ->setName($this->trans('Shop', [], 'Admin.Global'))
                    ->setOptions([
                        'field' => 'shop_name',
                    ])
            );
        }

        return $columns
            ->add(
                (new BadgeColumn('customers_count'))
                    ->setName($this->trans('Customers', [], 'Admin.Global'))
                    ->setOptions([
                        'field' => 'customers_count',
                        'alignment' => 'left',
                        'badge_type' => '',
                        'badge_type_field' => 'customers_badge_type',
                    ])
            )
            ->add(
                (new BadgeColumn('status'))
                    ->setName($this->trans('Status', [], 'Admin.Global'))
                    ->setOptions([
                        'field' => 'status_label',
                        'alignment' => 'left',
                        'badge_type' => '',
                        'badge_type_field' => 'status',
                    ])
            )
            ->add(
                (new BusinessEntityActionColumn('actions'))
                    ->setName($this->trans('Actions', [], 'Admin.Global'))
                    ->setOptions([
                        'actions' => (new RowActionCollection())
                            ->add(
                                (new LinkRowAction('view'))
                                    ->setName($this->trans('View details', [], 'Admin.Actions'))
                                    ->setIcon('zoom_in')
                                    ->setOptions([
                                        'route' => 'admin_business_entities_view',
                                        'route_param_name' => 'businessEntityId',
                                        'route_param_field' => 'id_business_entity',
                                        'clickable_row' => true,
                                        'accessibility_checker' => static fn (array $record): bool => 'pending' !== $record['status'],
                                    ])
                            )
                            ->add(
                                (new LinkRowAction('add_b2b_customer'))
                                    ->setName($this->trans('Add B2B customer', [], 'Admin.Actions'))
                                    ->setIcon('person_add')
                                    ->setOptions([
                                        'route' => 'admin_business_entities_list',
                                        'route_param_name' => 'businessEntityId',
                                        'route_param_field' => 'id_business_entity',
                                        'accessibility_checker' => static fn (array $record): bool => 'pending' !== $record['status'],
                                    ])
                            )
                            ->add(
                                (new LinkRowAction('edit'))
                                    ->setName($this->trans('Edit', [], 'Admin.Actions'))
                                    ->setIcon('edit')
                                    ->setOptions([
                                        'route' => 'admin_business_entities_edit',
                                        'route_param_name' => 'businessEntityId',
                                        'route_param_field' => 'id_business_entity',
                                        'accessibility_checker' => static fn (array $record): bool => 'pending' !== $record['status'],
                                    ])
                            )
                            ->add(
                                (new LinkRowAction('edit_status'))
                                    ->setName($this->trans('Edit status', [], 'Admin.Actions'))
                                    ->setIcon('autorenew')
                                    ->setOptions([
                                        'route' => 'admin_business_entities_list',
                                        'route_param_name' => 'businessEntityId',
                                        'route_param_field' => 'id_business_entity',
                                        'accessibility_checker' => static fn (array $record): bool => 'pending' !== $record['status'],
                                    ])
                            )
                            ->add(
                                (new LinkRowAction('approve_pending'))
                                    ->setName($this->trans('Approve', [], 'Admin.Actions'))
                                    ->setIcon('check')
                                    ->setOptions([
                                        'route' => 'admin_business_entities_list',
                                        'route_param_name' => 'businessEntityId',
                                        'route_param_field' => 'id_business_entity',
                                        'use_inline_display' => true,
                                        'accessibility_checker' => static fn (array $record): bool => 'pending' === $record['status'],
                                        'attr' => ['class' => 'business-entity-action-approve'],
                                    ])
                            )
                            ->add(
                                (new LinkRowAction('reject_pending'))
                                    ->setName($this->trans('Reject', [], 'Admin.Actions'))
                                    ->setIcon('close')
                                    ->setOptions([
                                        'route' => 'admin_business_entities_list',
                                        'route_param_name' => 'businessEntityId',
                                        'route_param_field' => 'id_business_entity',
                                        'use_inline_display' => true,
                                        'accessibility_checker' => static fn (array $record): bool => 'pending' === $record['status'],
                                        'attr' => ['class' => 'business-entity-action-reject'],
                                    ])
                            )
                            ->add(
                                (new LinkRowAction('view_pending'))
                                    ->setName($this->trans('View details', [], 'Admin.Actions'))
                                    ->setIcon('visibility')
                                    ->setOptions([
                                        'route' => 'admin_business_entities_view',
                                        'route_param_name' => 'businessEntityId',
                                        'route_param_field' => 'id_business_entity',
                                        'use_inline_display' => true,
                                        'accessibility_checker' => static fn (array $record): bool => 'pending' === $record['status'],
                                        'attr' => ['class' => 'business-entity-action-view-pending'],
                                    ])
                            )
                            ->add((new DeleteBusinessEntityRowAction('delete'))
                                ->setName($this->trans('Delete', [], 'Admin.Actions'))
                                ->setIcon('delete')
                                ->setOptions([
                                    'business_entity_delete_route' => 'admin_business_entities_delete',
                                    'business_entity_id_field' => 'id_business_entity',
                                    'business_entity_name_field' => 'name',
                                    'business_entity_customers_count_field' => 'customers_count',
                                    'accessibility_checker' => static fn (array $record): bool => 'pending' !== $record['status'],
                                ])
                            ),
                    ])
            );
    }

    protected function getFilters()
    {
        return (new FilterCollection())
            ->add(
                (new Filter('id_business_entity', NumberType::class))
                    ->setTypeOptions([
                        'attr' => [
                            'placeholder' => $this->trans('Search ID', [], 'Admin.Actions'),
                        ],
                        'required' => false,
                    ])
                    ->setAssociatedColumn('id_business_entity')
            )
            ->add(
                (new Filter('name', TextType::class))
                    ->setTypeOptions([
                        'attr' => [
                            'placeholder' => $this->trans('Search name', [], 'Admin.Actions'),
                        ],
                        'required' => false,
                    ])
                    ->setAssociatedColumn('name')
            )
            ->add(
                (new Filter('legal_name', TextType::class))
                    ->setTypeOptions([
                        'attr' => [
                            'placeholder' => $this->trans('Search legal name', [], 'Admin.Actions'),
                        ],
                        'required' => false,
                    ])
                    ->setAssociatedColumn('legal_name')
            )
            ->add(
                (new Filter('vat_number', TextType::class))
                    ->setTypeOptions([
                        'attr' => [
                            'placeholder' => $this->trans('Search VAT number', [], 'Admin.Actions'),
                        ],
                        'required' => false,
                    ])
                    ->setAssociatedColumn('vat_number')
            )
            ->add(
                (new Filter('customers_count', NumberType::class))
                    ->setTypeOptions([
                        'attr' => [
                            'placeholder' => $this->trans('Search customers count', [], 'Admin.Actions'),
                        ],
                        'required' => false,
                    ])
                    ->setAssociatedColumn('customers_count')
            )
            ->add(
                (new Filter('status', ChoiceType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'placeholder' => $this->trans('All', [], 'Admin.Global'),
                        'choices' => [
                            $this->trans('Pending', [], 'Admin.Global') => 'pending',
                            $this->trans('Active', [], 'Admin.Global') => 'active',
                            $this->trans('Inactive', [], 'Admin.Global') => 'inactive',
                            $this->trans('Rejected', [], 'Admin.Global') => 'rejected',
                        ],
                    ])
                    ->setAssociatedColumn('status')
            )
            ->add(
                (new Filter('actions', SearchAndResetType::class))
                    ->setTypeOptions([
                        'reset_route' => 'admin_common_reset_search_by_filter_id',
                        'reset_route_params' => [
                            'filterId' => self::GRID_ID,
                        ],
                        'redirect_route' => 'admin_business_entities_list',
                    ])
                    ->setAssociatedColumn('actions')
            )
        ;
    }

    protected function getBulkActions()
    {
        return (new BulkActionCollection())
            ->add(
                $this->buildBulkDeleteAction('admin_business_entities_bulk_delete')
            );
    }

    protected function getGridActions()
    {
        return (new GridActionCollection())
            ->add(
                (new SimpleGridAction('common_refresh_list'))
                    ->setName($this->trans('Refresh list', [], 'Admin.Global'))
                    ->setIcon('refresh')
            )
            ->add(
                (new SimpleGridAction('common_show_query'))
                    ->setName($this->trans('Show SQL query', [], 'Admin.Actions'))
                    ->setIcon('code')
            )
            ->add(
                (new SimpleGridAction('common_export_sql_manager'))
                    ->setName($this->trans('Export to SQL Manager', [], 'Admin.Actions'))
                    ->setIcon('storage')
            );
    }
}
