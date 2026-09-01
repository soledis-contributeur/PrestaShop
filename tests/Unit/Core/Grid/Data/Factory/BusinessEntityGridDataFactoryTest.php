<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\Unit\Core\Grid\Data\Factory;

use PHPUnit\Framework\TestCase;
use PrestaShop\PrestaShop\Core\Grid\Data\Factory\BusinessEntityGridDataFactory;
use PrestaShop\PrestaShop\Core\Grid\Data\Factory\GridDataFactoryInterface;
use PrestaShop\PrestaShop\Core\Grid\Data\GridData;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use ValueError;

class BusinessEntityGridDataFactoryTest extends TestCase
{
    public function testItAddsTranslatedStatusLabelAndBadgeTypeToEachRecordAndKeepsRawStatus(): void
    {
        $records = new RecordCollection([
            ['id_business_entity' => 1, 'status' => 'active', 'name' => 'Active Corp'],
            ['id_business_entity' => 2, 'status' => 'pending', 'name' => 'Pending Corp'],
            ['id_business_entity' => 3, 'status' => 'inactive', 'name' => 'Inactive Corp'],
            ['id_business_entity' => 4, 'status' => 'rejected', 'name' => 'Rejected Corp'],
        ]);

        $query = 'SELECT be.id_business_entity FROM ps_business_entity be';

        $inner = $this->createMock(GridDataFactoryInterface::class);
        $inner->method('getData')->willReturn(new GridData($records, 4, $query));

        $data = $this->buildFactory($inner)->getData($this->createMock(SearchCriteriaInterface::class));
        $result = iterator_to_array($data->getRecords());

        $this->assertSame('Active', $result[0]['status_label']);
        $this->assertSame('active', $result[0]['status'], 'raw status is preserved');
        $this->assertSame('success', $result[0]['status_badge_type']);

        $this->assertSame('Pending', $result[1]['status_label']);
        $this->assertSame('info', $result[1]['status_badge_type']);

        $this->assertSame('Inactive', $result[2]['status_label']);
        $this->assertSame('light-info', $result[2]['status_badge_type']);

        $this->assertSame('Rejected', $result[3]['status_label']);
        $this->assertSame('danger', $result[3]['status_badge_type']);

        $this->assertSame(4, $data->getRecordsTotal());
    }

    public function testItRejectsAStatusOutsideTheEnumInsteadOfDegrading(): void
    {
        // The status column is an ENUM NOT NULL, so an out-of-domain value can only come from a
        // schema/enum desync. Failing loudly is the deliberate choice: the previous tryFrom() fallback
        // rendered an unlabelled, uncoloured badge instead of surfacing the inconsistency.
        $records = new RecordCollection([
            ['id_business_entity' => 1, 'status' => 'unknown_value'],
        ]);

        $inner = $this->createMock(GridDataFactoryInterface::class);
        $inner->method('getData')->willReturn(new GridData($records, 1, 'SELECT 1'));

        $this->expectException(ValueError::class);

        $this->buildFactory($inner)->getData($this->createMock(SearchCriteriaInterface::class));
    }

    public function testItBuildsADeletionConfirmationMessageNamingEachEntity(): void
    {
        $records = new RecordCollection([
            ['id_business_entity' => 1, 'status' => 'active', 'name' => 'Tan Emporium'],
            ['id_business_entity' => 2, 'status' => 'pending', 'name' => 'Acme Industries'],
        ]);

        $inner = $this->createMock(GridDataFactoryInterface::class);
        $inner->method('getData')->willReturn(new GridData($records, 2, 'SELECT 1'));

        $data = $this->buildFactory($inner)->getData($this->createMock(SearchCriteriaInterface::class));
        $result = iterator_to_array($data->getRecords());

        $this->assertStringContainsString('<strong>Tan Emporium</strong>', $result[0]['delete_confirm_message']);
        $this->assertStringContainsString('<strong>Acme Industries</strong>', $result[1]['delete_confirm_message']);
        $this->assertStringNotContainsString(
            '%name%',
            $result[0]['delete_confirm_message'],
            'the placeholder must be replaced, not shown to the merchant'
        );
        $this->assertStringEndsWith(
            '<br>This action is irreversible.',
            $result[0]['delete_confirm_message'],
            'the mock-up puts the irreversibility warning on its own line'
        );
    }

    /**
     * AC2 spells the sentence out. Pin it whole, plus the translation domains: a wrong domain makes
     * the string silently untranslatable while every other assertion here still passes.
     */
    public function testTheDeletionMessageIsTheOneAC2AsksForAndUsesTheRightDomains(): void
    {
        $records = new RecordCollection([
            ['id_business_entity' => 1, 'status' => 'active', 'name' => 'Acme Industries'],
        ]);

        $inner = $this->createMock(GridDataFactoryInterface::class);
        $inner->method('getData')->willReturn(new GridData($records, 1, 'SELECT 1'));

        $domains = [];
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(
            static function (string $id, array $parameters = [], ?string $domain = null) use (&$domains): string {
                $domains[$id] = $domain;

                return strtr($id, $parameters);
            }
        );

        $factory = new BusinessEntityGridDataFactory($inner, $translator);
        $message = iterator_to_array($factory->getData($this->createMock(SearchCriteriaInterface::class))->getRecords())[0]['delete_confirm_message'];

        $this->assertSame(
            'Are you sure you want to delete <strong>Acme Industries</strong> from the list of business entities?'
            . '<br>This action is irreversible.',
            $message
        );

        $this->assertSame(
            'Admin.Orderscustomers.Feature',
            $domains['Are you sure you want to delete %name% from the list of business entities?']
        );
        $this->assertSame('Admin.Notifications.Warning', $domains['This action is irreversible.']);
    }

    public function testItEscapesTheEntityNameInTheDeletionMessage(): void
    {
        // The confirmation modal assigns the message with innerHTML, so a name carrying markup
        // must reach the merchant as text and never as live HTML.
        $records = new RecordCollection([
            ['id_business_entity' => 1, 'status' => 'active', 'name' => '<img src=x onerror=alert(1)>'],
        ]);

        $inner = $this->createMock(GridDataFactoryInterface::class);
        $inner->method('getData')->willReturn(new GridData($records, 1, 'SELECT 1'));

        $data = $this->buildFactory($inner)->getData($this->createMock(SearchCriteriaInterface::class));
        $message = iterator_to_array($data->getRecords())[0]['delete_confirm_message'];

        $this->assertStringNotContainsString('<img', $message);
        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $message);
    }

    public function testItForwardsTheInnerRecordsTotalAndQueryUnchanged(): void
    {
        $query = 'SELECT be.id_business_entity FROM ps_business_entity be WHERE be.deleted = 0';

        $inner = $this->createMock(GridDataFactoryInterface::class);
        $inner->method('getData')->willReturn(new GridData(new RecordCollection([]), 42, $query));

        $data = $this->buildFactory($inner)->getData($this->createMock(SearchCriteriaInterface::class));

        $this->assertSame(42, $data->getRecordsTotal());
        $this->assertSame($query, $data->getQuery(), 'the SQL query must reach "Show SQL query" untouched');
    }

    private function buildFactory(GridDataFactoryInterface $inner): BusinessEntityGridDataFactory
    {
        $translator = $this->createMock(TranslatorInterface::class);
        // trans() echoes the source string with its parameters applied, so we can assert both that
        // the label went through translation and that the placeholders were actually replaced.
        $translator->method('trans')->willReturnCallback(
            static fn (string $id, array $parameters = []): string => strtr($id, $parameters)
        );

        return new BusinessEntityGridDataFactory($inner, $translator);
    }
}
