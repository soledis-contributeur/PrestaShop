<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Tests\Unit\Core\Domain\BusinessEntity\Command;

use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Command\DeleteBusinessEntityCommand;
use PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Exception\BusinessEntityConstraintException;

class DeleteBusinessEntityCommandTest extends TestCase
{
    public function testItWrapsTheIdInABusinessEntityId(): void
    {
        $command = new DeleteBusinessEntityCommand(42);

        $this->assertSame(42, $command->getBusinessEntityId()->getValue());
    }

    public function testItRejectsNonPositiveId(): void
    {
        $this->expectException(BusinessEntityConstraintException::class);

        new DeleteBusinessEntityCommand(0);
    }
}
