/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

const {$} = window;

const SELECTORS = {
  container: '.js-business-entity-identifiers',
  row: '.js-business-entity-identifier',
  valueInput: '.flex-grow-1 input',
  defaultBillingAddress: '#business_entity_default_billing_address',
  billingAddresses: 'ul.billing_addresses li',
  countrySelect: '.js-address-country-select',
  modal: '#business_entity_duplicate_identifier_modal',
  modalList: '.js-duplicate-identifier-list',
  confirmButton: '.js-confirm-duplicate-identifier',
};

interface IdentifierInput {
  businessIdentifierId: string;
  value: string;
}

interface DuplicateResult {
  hasDuplicates: boolean;
  duplicates: Array<{value: string; businessEntityName: string}>;
}

function collectIdentifiers(container: HTMLElement): IdentifierInput[] {
  const identifiers: IdentifierInput[] = [];

  container.querySelectorAll<HTMLElement>(SELECTORS.row).forEach((row) => {
    const hidden = row.querySelector<HTMLInputElement>('input[type="hidden"]');
    const valueInput = row.querySelector<HTMLInputElement>(SELECTORS.valueInput);
    const businessIdentifierId = hidden?.value ?? '';
    const value = (valueInput?.value ?? '').trim();

    if (businessIdentifierId !== '' && value !== '') {
      identifiers.push({businessIdentifierId, value});
    }
  });

  return identifiers;
}

function getBillingCountryId(): string {
  const defaultIndex = document.querySelector<HTMLInputElement>(SELECTORS.defaultBillingAddress)?.value ?? '';

  let countrySelect: HTMLSelectElement | null = null;

  if (defaultIndex !== '') {
    countrySelect = document.querySelector<HTMLSelectElement>(
      `${SELECTORS.billingAddresses} div.card[data-address-index="${defaultIndex}"] ${SELECTORS.countrySelect}`,
    );
  }

  if (!countrySelect) {
    countrySelect = document.querySelector<HTMLSelectElement>(
      `${SELECTORS.billingAddresses} ${SELECTORS.countrySelect}`,
    );
  }

  return countrySelect?.value ?? '';
}

function buildRequestBody(identifiers: IdentifierInput[], countryId: string, businessEntityId: string): string {
  const params = new URLSearchParams();
  params.set('countryId', countryId);

  if (businessEntityId !== '') {
    params.set('businessEntityId', businessEntityId);
  }

  identifiers.forEach((identifier, index) => {
    params.set(`identifiers[${index}][businessIdentifierId]`, identifier.businessIdentifierId);
    params.set(`identifiers[${index}][value]`, identifier.value);
  });

  return params.toString();
}

function showDuplicateModal(result: DuplicateResult): void {
  const list = document.querySelector<HTMLElement>(SELECTORS.modalList);

  if (list) {
    list.innerHTML = '';
    result.duplicates.forEach((duplicate) => {
      const item = document.createElement('li');
      item.textContent = `${duplicate.value} — ${duplicate.businessEntityName}`;
      list.appendChild(item);
    });
  }

  $(SELECTORS.modal).modal('show');
}

export default function initBusinessEntityDuplicateIdentifierCheck(): void {
  const container = document.querySelector<HTMLElement>(SELECTORS.container);

  if (!container) {
    return;
  }

  const url = container.dataset.checkDuplicateUrl ?? '';
  const businessEntityId = container.dataset.businessEntityId ?? '';
  const form = container.closest('form');

  if (url === '' || !form) {
    return;
  }

  let confirmed = false;

  document.querySelector<HTMLElement>(SELECTORS.confirmButton)?.addEventListener('click', () => {
    confirmed = true;
    $(SELECTORS.modal).modal('hide');
    form.submit();
  });

  form.addEventListener('submit', (event) => {
    if (confirmed) {
      return;
    }

    const identifiers = collectIdentifiers(container);
    const countryId = getBillingCountryId();

    if (identifiers.length === 0 || (countryId === '' && businessEntityId === '')) {
      return;
    }

    event.preventDefault();

    fetch(url, {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: buildRequestBody(identifiers, countryId, businessEntityId),
    })
      .then((response) => response.json())
      .then((result: DuplicateResult) => {
        if (result.hasDuplicates) {
          showDuplicateModal(result);
        } else {
          form.submit();
        }
      })
      .catch(() => {
        form.submit();
      });
  });
}
