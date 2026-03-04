<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShopBundle\Controller\Admin\Sell\BusinessEntity;

use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Command\GetPendingCountCommand;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\CommandHandler\GetPendingCountCommandHandler;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Exception\BusinessEntityBillingAddressConstraintException;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Exception\UnableToCreateBusinessEntityAddress;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Query\GetBusinessEntityForViewing;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\Builder\FormBuilderInterface;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\Handler\FormHandlerInterface;
use PrestaShop\PrestaShop\Core\Grid\Filter\BusinessEntityFilters;
use PrestaShop\PrestaShop\Core\Grid\Filter\CustomerB2BFilters;
use PrestaShop\PrestaShop\Core\Grid\Presenter\GridPresenterInterface;
use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;
use PrestaShopBundle\Form\Admin\Sell\BusinessEntity\BusinessEntityType;
use PrestaShopBundle\Security\Attribute\AdminSecurity;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Class BusinessEntitiesController manages the "Sell > Business Entities" page.
 */
class BusinessEntitiesController extends PrestaShopAdminController
{
    #[AdminSecurity("is_granted('read', request.get('_legacy_controller'))")]
    public function listAction(
        Request $request,
        #[Autowire(service: 'prestashop.core.grid.grid_factory.business_entity')]
        object $businessEntityGridFactory,
        #[Autowire(service: 'prestashop.core.grid.presenter.grid_presenter')]
        GridPresenterInterface $gridPresenter,
        GetPendingCountCommandHandler $getPendingCountCommandHandler
    ): Response {
        /** @var array<string, mixed> $gridParams */
        $gridParams = $request->query->all(BusinessEntityFilters::GRID_ID);

        $orderBy = $request->query->get('orderBy', 'id_business_entity');
        $sortOrder = $request->query->get('sortOrder', 'ASC');

        $gridParams['orderBy'] = $orderBy;
        $gridParams['sortOrder'] = $sortOrder;

        $filters = new BusinessEntityFilters($gridParams);

        $grid = $businessEntityGridFactory->getGrid($filters, $orderBy, $sortOrder);

        $businessEntityGrid = $gridPresenter->present($grid);

        $pendingCount = $getPendingCountCommandHandler->handle(new GetPendingCountCommand());

        $currentStatusFilter = $filters->getFilters()['status'] ?? null;
        $isPendingFilter = ($currentStatusFilter === 'pending');

        $pendingUrl = $this->generateUrl('admin_business_entities_list', [
            BusinessEntityFilters::GRID_ID => [
                'filters' => ['status' => 'pending'],
            ],
        ]);

        return $this->render(
            '@PrestaShop/Admin/Sell/BusinessEntity/list.html.twig',
            [
                'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
                'enableSidebar' => true,
                'layoutTitle' => $this->trans('Business entities', [], 'Admin.Navigation.Menu'),
                'layoutHeaderToolbarBtn' => $this->getBusinessEntitiesToolbarButtons(),
                'businessEntityGrid' => $businessEntityGrid,
                'pendingCount' => $pendingCount,
                'pendingUrl' => $pendingUrl,
                'isPendingFilter' => $isPendingFilter,
                'orderBy' => $orderBy,
                'sortOrder' => $sortOrder,
            ]
        );
    }

    #[AdminSecurity("is_granted('read', request.get('_legacy_controller'))")]
    public function viewAction(
        Request $request,
        int $businessEntityId,
        #[Autowire(service: 'prestashop.core.grid.grid_factory.customer_b2b')]
        object $customerB2BGridFactory,
        #[Autowire(service: 'prestashop.core.grid.presenter.grid_presenter')]
        GridPresenterInterface $gridPresenter
    ): Response {
        $businessEntityForViewing = $this->dispatchQuery(
            new GetBusinessEntityForViewing($businessEntityId)
        );

        $orderBy = $request->query->get('orderBy', 'id_customer_b2b');
        $sortOrder = $request->query->get('sortOrder', 'ASC');

        $gridParams['filters'] = ['businessEntityId' => $businessEntityId];
        $gridParams['orderBy'] = $orderBy;
        $gridParams['sortOrder'] = $sortOrder;

        $filters = new CustomerB2BFilters($gridParams);

        $customerB2BGrid = $customerB2BGridFactory->getGrid($filters, $orderBy, $sortOrder, $businessEntityId);
        $customerB2BGridRendered = $gridPresenter->present($customerB2BGrid);

        return $this->render(
            '@PrestaShop/Admin/Sell/BusinessEntity/view.html.twig',
            [
                'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
                'enableSidebar' => true,
                'layoutHeaderToolbarBtn' => $this->getBusinessEntityViewToolbarButtons($businessEntityId),
                'businessEntity' => $businessEntityForViewing,
                'businessEntityId' => $businessEntityId,
                'customerB2bGrid' => $customerB2BGridRendered,
            ]
        );
    }

    #[AdminSecurity("is_granted('create', 'AdminBusinessEntities')", message: 'You do not have permission to create this.', redirectRoute: 'admin_business_entities_list')]
    public function createAction(
        Request $request,
        #[Autowire(service: 'prestashop.core.form.identifiable_object.builder.business_entity_form_builder')]
        FormBuilderInterface $formBuilder,
        #[Autowire(service: 'prestashop.core.form.identifiable_object.business_entity_form_handler')]
        FormHandlerInterface $formHandler,
        #[Autowire(expression: 'service("prestashop.adapter.legacy.context").getContext().country.id')]
        int $defaultCountryId
    ): Response {
        $formData = [];

        $billingAddressTypeIndex = BusinessEntityType::BILLING_ADDRESS_TYPE;
        if (isset($request->request->all('business_entity')[$billingAddressTypeIndex])) {
            foreach ($request->request->all('business_entity')[$billingAddressTypeIndex] as $billingAddressIndex => $billingAddress) {
                $formData[$billingAddressTypeIndex][$billingAddressIndex] = [];
                $formData[$billingAddressTypeIndex][$billingAddressIndex]['id_country'] = $billingAddress['id_country'] ?? $defaultCountryId;
            }
        }
        $shippingAddressTypeIndex = BusinessEntityType::SHIPPING_ADDRESS_TYPE;
        if (isset($request->request->all('business_entity')[$shippingAddressTypeIndex])) {
            foreach ($request->request->all('business_entity')[$shippingAddressTypeIndex] as $shippingAddressIndex => $billingAddress) {
                $formData[$shippingAddressTypeIndex][$shippingAddressIndex] = [];
                $formData[$shippingAddressTypeIndex][$shippingAddressIndex]['id_country'] = $billingAddress['id_country'] ?? $defaultCountryId;
            }
        }

        $form = $formBuilder->getForm($formData);

        $form->handleRequest($request);

        try {
            $result = $formHandler->handle($form);
            if ($businessEntityId = $result->getIdentifiableObjectId()) {
                $this->addFlash(
                    'success',
                    $this->trans('Business entity successfully created.', [], 'Admin.Notifications.Success')
                );

                return $this->redirectToRoute('admin_business_entities_list', ['businessEntityId' => $businessEntityId]
                );
            }
        } catch (Throwable $e) {
            $this->addFlash('error', $this->getErrorMessageForException($e, $this->getErrorMessages()));
        }

        return $this->render(
            '@PrestaShop/Admin/Sell/BusinessEntity/create.html.twig',
            [
                'layoutTitle' => $this->trans('New business entity', [], 'Admin.Navigation.Menu'),
                'businessEntityForm' => $form->createView(),
            ]
        );
    }

    #[AdminSecurity("is_granted('update', request.get('_legacy_controller'))", message: 'You do not have permission to edit this.', redirectRoute: 'admin_business_entities_list')]
    public function editAction(
        int $businessEntityId,
        Request $request,
    ): Response {
        return $this->redirectToRoute('admin_business_entities_list');
    }

    #[AdminSecurity("is_granted('delete', request.get('_legacy_controller'))", message: 'You do not have permission to delete this.', redirectRoute: 'admin_business_entities_list')]
    public function bulkDeleteAction(
        Request $request
    ): Response {
        $businessEntityIds = $request->get('business_entities_bulk');

        $this->addFlash('success', $this->trans('Successfully deleted selected business entities.', [], 'Admin.Notifications.Success'));

        return $this->redirectToRoute('admin_business_entities_list');
    }

    protected function getBusinessEntitiesToolbarButtons(): array
    {
        $toolbarButtons = [];

        $toolbarButtons['add'] = [
            'href' => $this->generateUrl('admin_business_entities_create'),
            'desc' => $this->trans('Add new business entity', [], 'Admin.Orderscustomers.Feature'),
            'icon' => 'add_circle_outline',
        ];

        return $toolbarButtons;
    }

    private function getBusinessEntityViewToolbarButtons(int $businessEntityId): array
    {
        $toolbarButtons = [];

        $toolbarButtons['edit'] = [
            'href' => $this->generateUrl('admin_business_entities_edit', ['businessEntityId' => $businessEntityId]),
            'desc' => $this->trans('Edit', [], 'Admin.Orderscustomers.Feature'),
            'icon' => 'mode_edit',
        ];

        return $toolbarButtons;
    }

    private function getErrorMessages(): array
    {
        return [
            UnableToCreateBusinessEntityAddress::class => $this->trans(
                'An error occurred while creating the business entity.',
                [],
                'Admin.Orderscustomers.Notification'
            ),
            BusinessEntityBillingAddressConstraintException::class => [
                BusinessEntityBillingAddressConstraintException::MISSING_BILLING_ADDRESS => $this->trans(
                    'At least one billing address is required if you want to use default billing address as shipping address.',
                    [],
                    'Admin.Orderscustomers.Notification'
                ),
                BusinessEntityBillingAddressConstraintException::MISSING_SHIPPING_ADDRESS => $this->trans(
                    'At least one shipping address is required if you don\'t want to use default billing address as shipping address.',
                    [],
                    'Admin.Orderscustomers.Notification'
                ),
                BusinessEntityBillingAddressConstraintException::MISSING_DEFAULT_BILLING_ADDRESS => $this->trans(
                    'You must have one default billing address',
                    [],
                    'Admin.Orderscustomers.Notification'
                ),
                BusinessEntityBillingAddressConstraintException::MISSING_DEFAULT_SHIPPING_ADDRESS => $this->trans(
                    'You must have one default shipping address',
                    [],
                    'Admin.Orderscustomers.Notification'
                ),
            ],
        ];
    }
}
