<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

namespace PrestaShop\PrestaShop\Core\Domain\BusinessEntity\CommandHandler;

use PrestaShop\PrestaShop\Adapter\BusinessEntity\Repository\BusinessEntityRepository;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Command\GetPendingCountCommand;

class GetPendingCountCommandHandler
{
    private $businessEntityRepository;

    public function __construct(BusinessEntityRepository $businessEntityRepository)
    {
        $this->businessEntityRepository = $businessEntityRepository;
    }

    /**
     * Handle the GetPendingCountCommand
     *
     * @param GetPendingCountCommand $command
     *
     * @return int The number of pending business entities
     */
    public function handle(GetPendingCountCommand $command): int
    {
        return $this->businessEntityRepository->getPendingCount();
    }
}
