<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

namespace PrestaShopBundle\Controller\Admin\Sell\BusinessEntity;

use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerB2BController extends PrestaShopAdminController
{
    public function toggleStatusAction(Request $request, int $customerB2bId): Response
    {
        return $this->redirectToRoute('admin_business_entities_view', [
            'businessEntityId' => $request->get('businessEntityId'),
        ]);
    }

    public function viewAction(int $customerB2bId): Response
    {
        return $this->render('@PrestaShop/Admin/Sell/BusinessEntity/CustomerB2B/view.html.twig', [
            'customerB2bId' => $customerB2bId,
        ]);
    }
}
