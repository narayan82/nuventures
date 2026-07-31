const EDGE_TOLERANCE = 2;

export function initLatestUpdatesCarousels() {
  document.querySelectorAll('[data-latest-updates-carousel]').forEach((carousel) => {
    const track = carousel.querySelector('[data-latest-updates-track]');
    const controls = carousel.querySelector('[data-latest-updates-controls]');
    const previousButton = carousel.querySelector('[data-latest-updates-previous]');
    const nextButton = carousel.querySelector('[data-latest-updates-next]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

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
      const isOverflowing = track.scrollWidth > track.clientWidth + EDGE_TOLERANCE;
      const maximumScroll = Math.max(0, track.scrollWidth - track.clientWidth);

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
        behavior: reducedMotion ? 'auto' : 'smooth',
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
