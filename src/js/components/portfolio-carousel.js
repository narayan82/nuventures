import { createCarouselAutoplay } from './carousel-autoplay.js';

const EDGE_TOLERANCE = 2;

export function initPortfolioCarousels() {
  document.querySelectorAll('[data-portfolio-journeys]').forEach((carousel) => {
    const track = carousel.querySelector('[data-portfolio-journeys-track]');
    const previousButton = carousel.querySelector('[data-portfolio-journeys-previous]');
    const nextButton = carousel.querySelector('[data-portfolio-journeys-next]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let isOverflowing = false;
    let autoplay = null;

    if (!track || !previousButton || !nextButton) {
      return;
    }

    const getScrollStep = () => {
      const firstCard = track.firstElementChild;
      const styles = window.getComputedStyle(track);
      const gap = parseFloat(styles.columnGap || styles.gap) || 0;

      return firstCard ? firstCard.getBoundingClientRect().width + gap : 0;
    };

    const updateState = () => {
      isOverflowing = track.scrollWidth > track.clientWidth + EDGE_TOLERANCE;
      const maximumScroll = Math.max(0, track.scrollWidth - track.clientWidth);

      previousButton.hidden = !isOverflowing;
      nextButton.hidden = !isOverflowing;
      previousButton.disabled = !isOverflowing || track.scrollLeft <= EDGE_TOLERANCE;
      nextButton.disabled = !isOverflowing || track.scrollLeft >= maximumScroll - EDGE_TOLERANCE;
      autoplay?.restart();
    };

    const move = (direction) => {
      const step = getScrollStep();

      if (step) {
        track.scrollBy({
          left: direction * step,
          behavior: reducedMotion ? 'auto' : 'smooth',
        });
      }
    };

    autoplay = createCarouselAutoplay({
      carousel,
      shouldPlay: () => track.scrollWidth > track.clientWidth + EDGE_TOLERANCE,
      advance: () => {
        const maximumScroll = Math.max(0, track.scrollWidth - track.clientWidth);

        if (track.scrollLeft >= maximumScroll - EDGE_TOLERANCE) {
          track.scrollTo({ left: 0, behavior: 'smooth' });
          return;
        }

        move(1);
      },
    });

    previousButton.addEventListener('click', () => move(-1));
    nextButton.addEventListener('click', () => move(1));
    track.addEventListener('scroll', updateState, { passive: true });
    window.addEventListener('resize', updateState);

    if ('ResizeObserver' in window) {
      new ResizeObserver(updateState).observe(track);
    }

    updateState();
  });
}
