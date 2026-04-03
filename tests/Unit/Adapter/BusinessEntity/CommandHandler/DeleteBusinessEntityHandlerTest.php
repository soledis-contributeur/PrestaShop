<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Tests\Unit\Adapter\BusinessEntity\CommandHandler;

use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Adapter\BusinessEntity\CommandHandler\DeleteBusinessEntityHandler;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Command\DeleteBusinessEntityCommand;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Exception\BusinessEntityNotFoundException;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Exception\CannotDeleteBusinessEntityException;
use PrestaShopBundle\Entity\B2B\BusinessEntity;
use PrestaShopBundle\Entity\Repository\BusinessEntityRepository;
use Psr\Log\LoggerInterface;
use RuntimeException;

class DeleteBusinessEntityHandlerTest extends TestCase
{
    public function testItSoftDeletesAnExistingBusinessEntityAndLogs(): void
    {
        $businessEntity = new BusinessEntity();

        $repository = $this->createMock(BusinessEntityRepository::class);
        $repository->method('getBusinessEntityById')->with(7)->willReturn($businessEntity);
        $repository->expects($this->once())->method('delete')->with($businessEntity);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info')
            ->with(
                'Business entity deleted successfully',
                ['object_type' => 'BusinessEntity', 'object_id' => 7]
            );

        $handler = new DeleteBusinessEntityHandler($repository, $logger);
        $handler->handle(new DeleteBusinessEntityCommand(7));
    }

    public function testItThrowsWhenBusinessEntityNotFound(): void
    {
        $repository = $this->createMock(BusinessEntityRepository::class);
        $repository->method('getBusinessEntityById')->willReturn(null);
        $repository->expects($this->never())->method('delete');

        $handler = new DeleteBusinessEntityHandler($repository, $this->createMock(LoggerInterface::class));

        $this->expectException(BusinessEntityNotFoundException::class);

        $handler->handle(new DeleteBusinessEntityCommand(7));
    }

    public function testItWrapsRepositoryErrorsInCannotDeleteException(): void
    {
        $repository = $this->createMock(BusinessEntityRepository::class);
        $repository->method('getBusinessEntityById')->willReturn(new BusinessEntity());
        $repository->method('delete')->willThrowException(new RuntimeException('DB failure'));

        $handler = new DeleteBusinessEntityHandler($repository, $this->createMock(LoggerInterface::class));

        $this->expectException(CannotDeleteBusinessEntityException::class);

        $handler->handle(new DeleteBusinessEntityCommand(7));
    }
}
