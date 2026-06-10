/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

const SELECTORS = {
  container: '.js-business-entity-identifiers',
  picker: '.js-business-entity-identifier-selector',
  row: '.js-business-entity-identifier',
  rowLabel: '.js-business-entity-identifier-label',
  deleteButton: '.js-business-entity-identifier-delete',
};

function getRowTypeId(row: HTMLElement): string {
  const hidden = row.querySelector<HTMLInputElement>('input[type="hidden"]');

  return hidden?.value ?? '';
}

function togglePickerOption(picker: HTMLSelectElement, typeId: string, disabled: boolean): void {
  if (!typeId) {
    return;
  }

  const option = picker.querySelector<HTMLOptionElement>(`option[value="${typeId}"]`);

  if (option) {
    option.disabled = disabled;
  }
}

function setupRow(row: HTMLElement, picker: HTMLSelectElement, labelByTypeId: Map<string, string>): void {
  const typeId = getRowTypeId(row);

  const label = row.querySelector<HTMLElement>(SELECTORS.rowLabel);

  if (label) {
    label.textContent = labelByTypeId.get(typeId) ?? `#${typeId}`;
  }

  togglePickerOption(picker, typeId, true);

  row.querySelector<HTMLElement>(SELECTORS.deleteButton)?.addEventListener('click', () => {
    togglePickerOption(picker, typeId, false);
    row.remove();
  });
}

export default function initBusinessEntityIdentifiers(): void {
  const container = document.querySelector<HTMLElement>(SELECTORS.container);
  const picker = document.querySelector<HTMLSelectElement>(SELECTORS.picker);

  if (!container || !picker) {
    return;
  }

  const labelByTypeId = new Map<string, string>();
  picker.querySelectorAll<HTMLOptionElement>('option').forEach((option) => {
    if (option.value) {
      labelByTypeId.set(option.value, option.textContent ?? '');
    }
  });

  container.querySelectorAll<HTMLElement>(SELECTORS.row).forEach((row) => {
    setupRow(row, picker, labelByTypeId);
  });

  picker.addEventListener('change', () => {
    const typeId = picker.value;

    if (!typeId) {
      return;
    }

    const index = Number(container.dataset.index ?? '0');
    const wrapper = document.createElement('div');
    wrapper.innerHTML = (container.dataset.prototype ?? '').replace(/__name__/g, String(index));

    const row = wrapper.firstElementChild as HTMLElement | null;

    if (row) {
      const hidden = row.querySelector<HTMLInputElement>('input[type="hidden"]');

      if (hidden) {
        hidden.value = typeId;
      }
      setupRow(row, picker, labelByTypeId);
      container.appendChild(row);
    }

    container.dataset.index = String(index + 1);
    picker.value = '';
  });
}
