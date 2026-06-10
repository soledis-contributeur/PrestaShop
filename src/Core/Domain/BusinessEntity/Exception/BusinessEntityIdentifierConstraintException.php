<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

namespace PrestaShop\PrestaShop\Core\Domain\BusinessEntity\Exception;

use Exception;

class BusinessEntityIdentifierConstraintException extends Exception
{
    public const MISSING_IDENTIFIER = 1;
}
