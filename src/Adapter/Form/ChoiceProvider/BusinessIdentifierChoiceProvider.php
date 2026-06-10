<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Adapter\Form\ChoiceProvider;

use PrestaShop\PrestaShop\Core\Form\FormChoiceProviderInterface;
use PrestaShopBundle\Entity\Repository\BusinessIdentifierRepository;
use Zone;

final class BusinessIdentifierChoiceProvider implements FormChoiceProviderInterface
{
    private const INTERNATIONAL_GROUP = 'International';

    public function __construct(
        private readonly BusinessIdentifierRepository $businessIdentifierRepository,
    ) {
    }

    public function getChoices(): array
    {
        $zoneNames = [];
        foreach (Zone::getZones() as $zone) {
            $zoneNames[(int) $zone['id_zone']] = $zone['name'];
        }

        $choices = [];
        foreach ($this->businessIdentifierRepository->getActiveBusinessIdentifiers() as $businessIdentifier) {
            $idZone = $businessIdentifier->getIdZone();
            $group = (null !== $idZone && isset($zoneNames[$idZone]))
                ? $zoneNames[$idZone]
                : self::INTERNATIONAL_GROUP;

            $choices[$group][$businessIdentifier->getLabel()] = $businessIdentifier->getId();
        }

        ksort($choices);

        return $choices;
    }
}
