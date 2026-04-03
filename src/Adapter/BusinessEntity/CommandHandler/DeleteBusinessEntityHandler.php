<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Adapter\BusinessEntity\CommandHandler;

use Exception;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsCommandHandler;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Command\DeleteBusinessEntityCommand;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\CommandHandler\DeleteBusinessEntityHandlerInterface;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Exception\BusinessEntityNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Exception\CannotDeleteBusinessEntityException;
use PrestaShopBundle\Entity\Repository\BusinessEntityRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommandHandler]
final class DeleteBusinessEntityHandler implements DeleteBusinessEntityHandlerInterface
{
    public function __construct(
        private readonly BusinessEntityRepository $businessEntityRepository,
        #[Autowire(service: 'prestashop.adapter.legacy.logger')]
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(DeleteBusinessEntityCommand $command): void
    {
        $businessEntityId = $command->getBusinessEntityId()->getValue();
        $businessEntity = $this->businessEntityRepository->getBusinessEntityById($businessEntityId);

        if (null === $businessEntity) {
            throw new BusinessEntityNotFoundException(sprintf(
                'Business entity with id "%d" was not found.',
                $businessEntityId
            ));
        }

        try {
            $this->businessEntityRepository->delete($businessEntity);
        } catch (Exception $e) {
            throw new CannotDeleteBusinessEntityException(
                sprintf('An error occurred while deleting business entity with id "%d".', $businessEntityId),
                0,
                $e
            );
        }

        $this->logger->info(
            'Business entity deleted successfully',
            [
                'object_type' => 'BusinessEntity',
                'object_id' => $businessEntityId,
            ]
        );
    }
}
