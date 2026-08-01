export function initNupodFilter() {
  document.querySelectorAll('[data-nupod-list]').forEach((section) => {
    const speaker = section.querySelector('[data-nupod-speaker]');
    const sort = section.querySelector('[data-nupod-sort]');
    const grid = section.querySelector('[data-nupod-grid]');
    const empty = section.querySelector('[data-nupod-empty]');

    if (!speaker || !sort || !grid || !empty) {
      return;
    }

    const cards = [...grid.querySelectorAll('[data-nupod-card]')];

    const compareCards = (firstCard, secondCard) => {
      if (sort.value === 'oldest') {
        return firstCard.dataset.date.localeCompare(secondCard.dataset.date);
      }

      if (sort.value === 'az' || sort.value === 'za') {
        const direction = sort.value === 'za' ? -1 : 1;

        return firstCard.dataset.title.localeCompare(secondCard.dataset.title, undefined, {
          sensitivity: 'base',
        }) * direction;
      }

      return secondCard.dataset.date.localeCompare(firstCard.dataset.date);
    };

    const applyFilters = () => {
      const selectedSpeaker = speaker.value;
      let visibleCount = 0;

      [...cards]
        .sort(compareCards)
        .forEach((card) => {
          const speakers = (card.dataset.speakers || '').split(' ').filter(Boolean);
          const isVisible = !selectedSpeaker || speakers.includes(selectedSpeaker);

          card.hidden = !isVisible;
          grid.append(card);

          if (isVisible) {
            visibleCount += 1;
          }
        });

      empty.hidden = visibleCount !== 0;
    };

    speaker.addEventListener('change', applyFilters);
    sort.addEventListener('change', applyFilters);
    applyFilters();
  });
}
