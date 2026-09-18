'use strict';

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-instrument-browser]').forEach(browser => {
    const items = [...browser.querySelectorAll('[data-instrument-item]')];
    const search = browser.querySelector('[data-instrument-search]');
    const filters = [...browser.querySelectorAll('[data-instrument-filter]')];
    const filterForm = browser.querySelector('[data-instrument-filter-form]');
    const reset = browser.querySelector('[data-instrument-reset]');
    const count = browser.querySelector('[data-instrument-results]');
    const empty = browser.querySelector('[data-instrument-empty]');
    const kind = browser.dataset.instrumentBrowser === 'families' ? 'families' : 'variants';
    const attributes = new Map(items.map(item => [item, JSON.parse(item.dataset.attributes || '{}')]));

    function filterItems() {
      const query = search.value.trim().toLocaleLowerCase();
      let shown = 0;
      items.forEach(item => {
        const visible = item.dataset.search.toLocaleLowerCase().includes(query)
          && filters.every(filter => !filter.value || attributes.get(item)[filter.dataset.instrumentFilter] === filter.value);
        item.hidden = !visible;
        shown += Number(visible);
      });
      count.textContent = `Showing ${shown} of ${items.length} ${kind}`;
      empty.hidden = shown !== 0;
      if (reset) reset.hidden = query === '' && filters.every(filter => !filter.value);
    }
    search.addEventListener('input', filterItems);
    filters.forEach(filter => filter.addEventListener('change', filterItems));
    filterForm.addEventListener('submit', event => { event.preventDefault(); filterItems(); });
    browser.querySelector('[data-instrument-filter-submit]').hidden = true;
    if (reset) reset.addEventListener('click', () => {
      search.value = '';
      filters.forEach(filter => { filter.value = ''; });
      filterItems();
      search.focus();
    });
    filterItems();

    const quoteForm = browser.querySelector('[data-instrument-quote]');
    if (!quoteForm) return;
    const checkboxes = [...quoteForm.querySelectorAll('[data-instrument-select]')];
    const error = quoteForm.querySelector('[data-instrument-selection-error]');
    function updateSelection() {
      const selected = checkboxes.filter(checkbox => checkbox.checked).length;
      browser.querySelectorAll('[data-instrument-selected-count]').forEach(element => { element.textContent = selected; });
      checkboxes.forEach(checkbox => checkbox.closest('[data-instrument-item]').classList.toggle('instrument-selected', checkbox.checked));
      if (selected) error.hidden = true;
    }
    checkboxes.forEach(checkbox => checkbox.addEventListener('change', updateSelection));
    quoteForm.addEventListener('submit', event => {
      if (checkboxes.some(checkbox => checkbox.checked)) return;
      event.preventDefault();
      error.hidden = false;
      error.setAttribute('role', 'alert');
      const firstVisible = checkboxes.find(checkbox => !checkbox.closest('[data-instrument-item]').hidden);
      if (firstVisible) firstVisible.focus();
      else search.focus();
    });
    updateSelection();
  });
});
