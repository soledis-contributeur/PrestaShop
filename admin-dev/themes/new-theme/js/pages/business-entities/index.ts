/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */
import initBusinessEntityIdentifiers from '@pages/business-entity/identifiers';
import initBusinessEntityDuplicateIdentifierCheck from '@pages/business-entity/duplicate-identifier-check';
import DeleteBusinessEntityRowActionExtension from
  '@components/grid/extension/action/row/business-entity/delete-business-entity-row-action-extension';

$(() => {
  initBusinessEntityIdentifiers();
  initBusinessEntityDuplicateIdentifierCheck();

  if ($('#business_entity_grid').length) {
    const grid = new window.prestashop.component.Grid('business_entity');

    grid.addExtension(new window.prestashop.component.GridExtensions.FiltersResetExtension());
    grid.addExtension(new window.prestashop.component.GridExtensions.ReloadListExtension());
    grid.addExtension(new window.prestashop.component.GridExtensions.ExportToSqlManagerExtension());
    grid.addExtension(new window.prestashop.component.GridExtensions.SortingExtension());
    grid.addExtension(new window.prestashop.component.GridExtensions.LinkRowActionExtension());
    grid.addExtension(new window.prestashop.component.GridExtensions.SubmitGridActionExtension());
    grid.addExtension(new window.prestashop.component.GridExtensions.SubmitBulkActionExtension());
    grid.addExtension(new window.prestashop.component.GridExtensions.BulkActionCheckboxExtension());
    grid.addExtension(new window.prestashop.component.GridExtensions.FiltersSubmitButtonEnablerExtension());
    grid.addExtension(new window.prestashop.component.GridExtensions.ChoiceExtension());
    grid.addExtension(new window.prestashop.component.GridExtensions.ColumnTogglingExtension());
    grid.addExtension(new window.prestashop.component.GridExtensions.SubmitRowActionExtension());
    grid.addExtension(new DeleteBusinessEntityRowActionExtension());
  }

  if ($('#simple_customer_b2b_grid').length) {
    const customerGrid = new window.prestashop.component.Grid('simple_customer_b2b');
    customerGrid.addExtension(new window.prestashop.component.GridExtensions.SortingExtension());
    customerGrid.addExtension(new window.prestashop.component.GridExtensions.LinkRowActionExtension());
  }
});
