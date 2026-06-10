<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Tests\Unit\Core\Form\IdentifiableObject\DataHandler;

use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Command\AddBusinessEntityCommand;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Command\EditBusinessEntityCommand;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\ValueObject\BusinessEntityBillingAddress;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\ValueObject\BusinessEntityGeneralInformation;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\ValueObject\BusinessEntityId;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\ValueObject\BusinessEntityIdentifierData;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\DataHandler\BusinessEntityFormDataHandler;
use PrestaShopBundle\Entity\Enum\BusinessEntityStatus;

class BusinessEntityFormDataHandlerTest extends TestCase
{
    public function testItPassesIdentifiersToAddCommand(): void
    {
        $identifiers = [new BusinessEntityIdentifierData(1, 'FR123')];

        $billingAddress = $this->createMock(BusinessEntityBillingAddress::class);
        $billingAddress->method('isDefault')->willReturn(true);

        $capturedCommand = null;
        $commandBus = $this->createMock(CommandBusInterface::class);
        $commandBus->method('handle')->willReturnCallback(function ($command) use (&$capturedCommand): BusinessEntityId {
            $capturedCommand = $command;

            return new BusinessEntityId(7);
        });

        $handler = new BusinessEntityFormDataHandler($commandBus);
        $result = $handler->create([
            'general_information' => new BusinessEntityGeneralInformation('BE', 'BE SAS', 'REF', false, BusinessEntityStatus::PENDING, 1, 3),
            'identifiers' => $identifiers,
            'billingAddressAsShippingAddress' => true,
            'billing_address' => [$billingAddress],
            'shipping_address' => [],
        ]);

        $this->assertInstanceOf(AddBusinessEntityCommand::class, $capturedCommand);
        $this->assertSame($identifiers, $capturedCommand->getIdentifiers());
        $this->assertSame(7, $result->getValue());
    }

    public function testItPassesIdentifiersToEditCommand(): void
    {
        $identifiers = [new BusinessEntityIdentifierData(1, 'FR123')];

        $capturedCommand = null;
        $commandBus = $this->createMock(CommandBusInterface::class);
        $commandBus->method('handle')->willReturnCallback(function ($command) use (&$capturedCommand) {
            $capturedCommand = $command;

            return null;
        });

        $handler = new BusinessEntityFormDataHandler($commandBus);
        $result = $handler->update(7, [
            'general_information' => new BusinessEntityGeneralInformation('BE', 'BE SAS', 'REF', false, BusinessEntityStatus::PENDING, 1, 3),
            'identifiers' => $identifiers,
        ]);

        $this->assertInstanceOf(EditBusinessEntityCommand::class, $capturedCommand);
        $this->assertSame($identifiers, $capturedCommand->getIdentifiers());
        $this->assertSame(7, $result->getValue());
    }
}
