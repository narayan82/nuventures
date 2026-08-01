const INITIAL_VISIBLE_COUNT = 11;
const LOAD_MORE_BATCH_SIZE = 8;

export function initInitiativesFilter() {
  document.querySelectorAll('[data-initiatives-list]').forEach((section) => {
    const page = section.closest('.initiatives-page');
    const search = page?.querySelector('[data-initiatives-search]');
    const category = page?.querySelector('[data-initiatives-category]');
    const sort = page?.querySelector('[data-initiatives-sort]');
    const grid = section.querySelector('[data-initiatives-grid]');
    const empty = section.querySelector('[data-initiatives-empty]');
    const loadMore = section.querySelector('[data-initiatives-load-more]');

    if (!search || !category || !sort || !grid || !empty || !loadMore) {
      return;
    }

    const cards = [...grid.querySelectorAll('[data-initiative-card]')];
    let visibleLimit = INITIAL_VISIBLE_COUNT;

    const compareCards = (firstCard, secondCard) => {
      if (sort.value === 'oldest') {
        return firstCard.dataset.date.localeCompare(secondCard.dataset.date);
      }

      return secondCard.dataset.date.localeCompare(firstCard.dataset.date);
    };

    const applyFilters = (resetLimit = false) => {
      if (resetLimit) {
        visibleLimit = INITIAL_VISIBLE_COUNT;
      }

      const searchQuery = search.value.trim().toLocaleLowerCase();
      const selectedCategory = category.value;
      const matches = cards
        .filter((card) => {
          const title = (card.dataset.title || '').toLocaleLowerCase();
          const categories = (card.dataset.categories || '').split(' ').filter(Boolean);
          const matchesSearch = title.includes(searchQuery);
          const matchesCategory = !selectedCategory || categories.includes(selectedCategory);

          return matchesSearch && matchesCategory;
        })
        .sort(compareCards);

      cards.forEach((card) => {
        card.hidden = true;
      });

      matches.forEach((card, index) => {
        card.hidden = index >= visibleLimit;
        grid.append(card);
      });

      empty.hidden = matches.length !== 0;
      loadMore.hidden = matches.length <= visibleLimit;
    };

    search.addEventListener('input', () => applyFilters(true));
    category.addEventListener('change', () => applyFilters(true));
    sort.addEventListener('change', () => applyFilters(true));
    loadMore.addEventListener('click', () => {
      visibleLimit += LOAD_MORE_BATCH_SIZE;
      applyFilters();
    });

    applyFilters();
  });
}
