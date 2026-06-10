<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShopBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use PrestaShopBundle\Entity\B2B\BusinessIdentifier;

class BusinessIdentifierRepository extends EntityRepository
{
    /**
     * @return BusinessIdentifier[]
     */
    public function getActiveBusinessIdentifiers(): array
    {
        return $this->findBy(['deleted' => false], ['label' => 'ASC']);
    }
}
