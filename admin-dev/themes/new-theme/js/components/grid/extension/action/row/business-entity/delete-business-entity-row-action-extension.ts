/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

import {Grid} from '@PSTypes/grid';
import GridMap from '@components/grid/grid-map';

const {$} = window;
export default class DeleteBusinessEntityRowActionExtension {
  /**
   * Extend grid
   *
   * @param {Grid} grid
   */
  extend(grid: Grid): void {
    grid
      .getContainer()
      .on('click', GridMap.rows.businessEntityDeleteAction, (event) => {
        event.preventDefault();

        const $button = $(event.currentTarget);
        const businessEntityId = $button.data('business-entity-id');
        const businessEntityName = $button.data('business-entity-name');
        const businessEntityCustomersCount = Number($button.data('business-entity-customers-count')) || 0;

        const $deleteBusinessEntitiesModal = $(
          GridMap.bulks.deleteBusinessEntityModal(grid.getId()),
        );
        $deleteBusinessEntitiesModal.modal('show');

        const $message = $deleteBusinessEntitiesModal.find('.js-delete-business-entity-message');
        const template = $message.data('template') || $message.text();

        if (businessEntityName) {
          $message.text(template.replace('%name%', businessEntityName));
        }

        const $customersWarning = $deleteBusinessEntitiesModal.find('.js-delete-business-entity-customers-warning');

        if (businessEntityCustomersCount > 0) {
          const warningTemplate = $customersWarning.data('template') || '';
          $customersWarning
            .text(warningTemplate.replace('%count%', String(businessEntityCustomersCount)))
            .removeClass('d-none');
        } else {
          $customersWarning.text('').addClass('d-none');
        }

        $deleteBusinessEntitiesModal.off(
          'click',
          GridMap.bulks.submitDeleteBusinessEntities,
        ).on(
          'click',
          GridMap.bulks.submitDeleteBusinessEntities,
          () => {
            const $businessEntitiesToDeleteInputBlock = $(
              GridMap.bulks.businessEntitiesToDelete,
            );

            const businessEntityInput = $businessEntitiesToDeleteInputBlock
              .data('prototype')
              .replace(
                /__name__/g,
                $businessEntitiesToDeleteInputBlock.children().length,
              );

            const $item = $($.parseHTML(businessEntityInput)[0]);
            $item.val(businessEntityId);

            $businessEntitiesToDeleteInputBlock.append($item);

            const $form = $deleteBusinessEntitiesModal.find('form');

            $form.attr('action', $button.data('business-entity-delete-url'));
            $form.submit();
          },
        );
      });
  }
}
