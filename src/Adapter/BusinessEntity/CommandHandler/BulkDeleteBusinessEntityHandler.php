<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Adapter\BusinessEntity\CommandHandler;

use Doctrine\ORM\Exception\ORMException;
use PrestaShop\PrestaShop\Core\CommandBus\Attributes\AsCommandHandler;
use PrestaShop\PrestaShop\Core\Context\ShopContext;
use PrestaShop\PrestaShop\Core\Domain\AbstractBulkCommandHandler;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Command\BulkDeleteBusinessEntityCommand;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\CommandHandler\BulkDeleteBusinessEntityHandlerInterface;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Exception\BulkDeleteBusinessEntityException;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Exception\BusinessEntityException;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Exception\BusinessEntityNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Exception\CannotDeleteBusinessEntityException;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\ValueObject\BusinessEntityId;
use PrestaShop\PrestaShop\Core\Domain\Exception\BulkCommandExceptionInterface;
use PrestaShopBundle\Entity\Repository\BusinessEntityRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommandHandler]
final class BulkDeleteBusinessEntityHandler extends AbstractBulkCommandHandler implements BulkDeleteBusinessEntityHandlerInterface
{
    public function __construct(
        private readonly BusinessEntityRepository $businessEntityRepository,
        private readonly ShopContext $shopContext,
        #[Autowire(service: 'prestashop.adapter.legacy.logger')]
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(BulkDeleteBusinessEntityCommand $command): void
    {
        $this->handleBulkAction($command->getBusinessEntityIds(), BusinessEntityException::class);
    }

    /**
     * Soft delete a single business entity. Mirrors DeleteBusinessEntityHandler on purpose:
     * handlers never call other handlers, so the read and delete are duplicated here.
     *
     * @throws BusinessEntityNotFoundException
     * @throws CannotDeleteBusinessEntityException
     */
    protected function handleSingleAction(mixed $id, mixed $command): void
    {
        $businessEntityId = $id->getValue();

        $shopIds = $this->shopContext->isAllShopContext() ? null : $this->shopContext->getAssociatedShopIds();
        $businessEntity = $this->businessEntityRepository->findById($businessEntityId, $shopIds);

        if (null === $businessEntity) {
            throw new BusinessEntityNotFoundException(sprintf('Business entity with id %d was not found.', $businessEntityId));
        }

        try {
            $this->businessEntityRepository->delete($businessEntity);
        } catch (ORMException $e) {
            throw new CannotDeleteBusinessEntityException('Could not delete business entity', 0, $e);
        }

        $this->logger->info(
            'Business entity deleted successfully',
            [
                'object_type' => 'BusinessEntity',
                'object_id' => $businessEntityId,
            ]
        );
    }

    protected function buildBulkException(array $caughtExceptions): BulkCommandExceptionInterface
    {
        return new BulkDeleteBusinessEntityException($caughtExceptions);
    }

    protected function supports($id): bool
    {
        return $id instanceof BusinessEntityId;
    }
}
