export function initPortfolioFilters() {
  document.querySelectorAll('[data-portfolio-companies]').forEach((section) => {
    const search = section.querySelector('[data-portfolio-search]');
    const stage = section.querySelector('[data-portfolio-stage]');
    const city = section.querySelector('[data-portfolio-city]');
    const sort = section.querySelector('[data-portfolio-sort]');
    const grid = section.querySelector('[data-portfolio-grid]');
    const count = section.querySelector('[data-portfolio-count]');
    const empty = section.querySelector('[data-portfolio-empty]');

    if (!search || !stage || !city || !sort || !grid || !count || !empty) {
      return;
    }

    const cards = Array.from(grid.querySelectorAll('[data-company-name]'));

    const applyFilters = () => {
      const searchQuery = search.value.trim().toLocaleLowerCase();
      const selectedStage = stage.value;
      const selectedCity = city.value;
      const direction = sort.value === 'desc' ? -1 : 1;
      let visibleCount = 0;

      cards
        .sort((first, second) => (
          first.dataset.companyName.localeCompare(second.dataset.companyName) * direction
        ))
        .forEach((card) => {
          const matchesSearch = card.dataset.companyName.includes(searchQuery);
          const matchesStage = !selectedStage || card.dataset.companyStage === selectedStage;
          const matchesCity = !selectedCity || card.dataset.companyCity === selectedCity;
          const isVisible = matchesSearch && matchesStage && matchesCity;

          card.hidden = !isVisible;
          grid.appendChild(card);

          if (isVisible) {
            visibleCount += 1;
          }
        });

      count.textContent = String(visibleCount);
      empty.hidden = visibleCount !== 0;
    };

    search.addEventListener('input', applyFilters);
    stage.addEventListener('change', applyFilters);
    city.addEventListener('change', applyFilters);
    sort.addEventListener('change', applyFilters);
    applyFilters();
  });
}
