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
    const infoButtons = Array.from(grid.querySelectorAll('[data-company-info]'));

    const closeDescriptions = (exceptButton = null) => {
      infoButtons.forEach((button) => {
        if (button === exceptButton) {
          return;
        }

        button.setAttribute('aria-expanded', 'false');
        button.closest('.portfolio-companies__item')?.classList.remove('is-description-visible');
      });
    };

    infoButtons.forEach((button) => {
      button.addEventListener('click', () => {
        const isOpen = button.getAttribute('aria-expanded') === 'true';

        closeDescriptions(button);
        button.setAttribute('aria-expanded', String(!isOpen));
        button.closest('.portfolio-companies__item')?.classList.toggle('is-description-visible', !isOpen);
      });
    });

    document.addEventListener('click', (event) => {
      if (!section.contains(event.target) || !event.target.closest('.portfolio-companies__item')) {
        closeDescriptions();
      }
    });

    section.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        closeDescriptions();
      }
    });

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
          let companyStages = [];
          let companyCities = [];

          try {
            companyStages = JSON.parse(card.dataset.companyStages || '[]');
          } catch {
            companyStages = [];
          }

          try {
            companyCities = JSON.parse(card.dataset.companyCities || '[]');
          } catch {
            companyCities = [];
          }

          const matchesSearch = card.dataset.companyName.includes(searchQuery);
          const matchesStage = !selectedStage || companyStages.includes(selectedStage);
          const matchesCity = !selectedCity || companyCities.includes(selectedCity);
          const isVisible = matchesSearch && matchesStage && matchesCity;

          card.hidden = !isVisible;
          if (!isVisible) {
            const infoButton = card.querySelector('[data-company-info]');
            infoButton?.setAttribute('aria-expanded', 'false');
            card.classList.remove('is-description-visible');
          }
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
