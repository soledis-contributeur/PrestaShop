<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

namespace PrestaShopBundle\Form\Admin\Sell\BusinessEntity;

use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class BusinessEntityIdentifierType extends TranslatorAwareType
{
    public const FIELD_BUSINESS_IDENTIFIER_ID = 'business_identifier_id';
    public const FIELD_VALUE = 'value';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(self::FIELD_BUSINESS_IDENTIFIER_ID, HiddenType::class)
            ->add(self::FIELD_VALUE, TextType::class, [
                'label' => false,
                'required' => true,
            ]);
    }
}
