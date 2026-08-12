export function initAboutTeamSort() {
  document.querySelectorAll('[data-about-team]').forEach((team) => {
    const search = team.querySelector('[data-about-team-search]');
    const select = team.querySelector('[data-about-team-sort]');
    const grid = team.querySelector('[data-about-team-grid]');

    if (!search || !select || !grid) {
      return;
    }

    const cards = [...grid.children];

    const applyFilters = () => {
      const searchQuery = search.value.trim().toLocaleLowerCase();
      const direction = select.value === 'za' ? -1 : 1;
      const sortedCards = [...cards];

      if (select.value) {
        sortedCards.sort((firstCard, secondCard) => {
          const firstName = firstCard.dataset.personName || '';
          const secondName = secondCard.dataset.personName || '';

          return firstName.localeCompare(secondName, undefined, {
            sensitivity: 'base',
          }) * direction;
        });
      }

      sortedCards.forEach((card) => {
        const personName = (card.dataset.personName || '').toLocaleLowerCase();
        card.hidden = !personName.includes(searchQuery);
        grid.append(card);
      });
    };

    search.addEventListener('input', applyFilters);
    select.addEventListener('change', applyFilters);
    applyFilters();
  });
}
