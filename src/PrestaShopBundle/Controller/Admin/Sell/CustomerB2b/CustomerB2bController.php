<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

namespace PrestaShopBundle\Controller\Admin\Sell\CustomerB2b;

use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;
use PrestaShopBundle\Security\Attribute\AdminSecurity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CustomerB2bController manages the "Sell > Customers B2B" page.
 */
class CustomerB2bController extends PrestaShopAdminController
{
    #[AdminSecurity("is_granted('read', request.get('_legacy_controller'))")]
    public function listAction(): Response
    {
        return $this->render('@PrestaShop/Admin/Sell/CustomerB2b/list.html.twig');
    }

    #[AdminSecurity("is_granted('update', request.get('_legacy_controller'))")]
    public function toggleStatusAction(Request $request, int $customerB2bId): Response
    {
        return $this->redirectToRoute('admin_business_entities_view', [
            'businessEntityId' => $request->get('businessEntityId'),
        ]);
    }

    #[AdminSecurity("is_granted('read', request.get('_legacy_controller'))")]
    public function viewAction(int $customerB2bId): Response
    {
        return $this->render('@PrestaShop/Admin/Sell/BusinessEntity/CustomerB2B/view.html.twig', [
            'customerB2bId' => $customerB2bId,
        ]);
    }

    #[AdminSecurity("is_granted('create', request.get('_legacy_controller'))", message: 'You do not have permission to add this.', redirectRoute: 'admin_business_entities_list')]
    public function createAction(): Response
    {
        return $this->redirectToRoute('admin_business_entities_list');
    }
}
