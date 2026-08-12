const EDGE_TOLERANCE = 2;

export function initCompaniesCarousels() {
  document.querySelectorAll('[data-companies-carousel]').forEach((carousel) => {
    const track = carousel.querySelector('[data-companies-track]');
    const controls = carousel.querySelector('[data-companies-controls]');
    const previousButton = carousel.querySelector('[data-companies-previous]');
    const nextButton = carousel.querySelector('[data-companies-next]');
    let hasInitialised = false;

    if (!track || !controls || !previousButton || !nextButton) {
      return;
    }

    const getScrollStep = () => {
      const firstCard = track.firstElementChild;

      if (!firstCard) {
        return 0;
      }

      const styles = window.getComputedStyle(track);
      const gap = parseFloat(styles.columnGap || styles.gap) || 0;

      return firstCard.getBoundingClientRect().width + gap;
    };

    const updateState = () => {
      const cards = Array.from(track.children);
      const styles = window.getComputedStyle(track);
      const gap = parseFloat(styles.columnGap || styles.gap) || 0;
      const cardsWidth = cards.reduce(
        (total, card) => total + card.getBoundingClientRect().width,
        0,
      );
      const contentWidth = cardsWidth + Math.max(0, cards.length - 1) * gap;
      const wrapperWidth = Math.min(1200, track.clientWidth);
      const isOverflowing = contentWidth > wrapperWidth + EDGE_TOLERANCE;

      carousel.classList.toggle('is-carousel', isOverflowing);

      if (!hasInitialised) {
        track.scrollLeft = 0;
        hasInitialised = true;
      }

      const maximumScroll = Math.max(0, track.scrollWidth - track.clientWidth);

      controls.classList.toggle('has-controls', isOverflowing);
      previousButton.hidden = !isOverflowing;
      nextButton.hidden = !isOverflowing;
      previousButton.disabled = !isOverflowing || track.scrollLeft <= EDGE_TOLERANCE;
      nextButton.disabled = !isOverflowing || track.scrollLeft >= maximumScroll - EDGE_TOLERANCE;
    };

    const move = (direction) => {
      const scrollStep = getScrollStep();

      if (!scrollStep) {
        return;
      }

      track.scrollBy({
        left: direction * scrollStep,
        behavior: 'smooth',
      });
    };

    previousButton.addEventListener('click', () => move(-1));
    nextButton.addEventListener('click', () => move(1));
    track.addEventListener('scroll', updateState, { passive: true });
    window.addEventListener('resize', updateState);

    if ('ResizeObserver' in window) {
      const resizeObserver = new ResizeObserver(updateState);
      resizeObserver.observe(track);
    }

    updateState();
  });
}
