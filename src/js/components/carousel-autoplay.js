const DEFAULT_AUTOPLAY_DELAY = 5000;

export function createCarouselAutoplay({
  carousel,
  shouldPlay,
  advance,
  delay = DEFAULT_AUTOPLAY_DELAY,
}) {
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  let timer = 0;
  let isPointerDown = false;

  const clear = () => {
    if (timer) {
      window.clearTimeout(timer);
      timer = 0;
    }
  };

  const isPaused = () => (
    reducedMotion.matches
    || document.hidden
    || isPointerDown
  );

  const schedule = () => {
    clear();

    if (isPaused() || !shouldPlay()) {
      return;
    }

    timer = window.setTimeout(() => {
      timer = 0;

      if (!isPaused() && shouldPlay()) {
        advance();
      }

      schedule();
    }, delay);
  };

  carousel.addEventListener('pointerdown', () => {
    isPointerDown = true;
    clear();
  });
  window.addEventListener('pointerup', () => {
    if (!isPointerDown) {
      return;
    }

    isPointerDown = false;
    schedule();
  });
  window.addEventListener('pointercancel', () => {
    isPointerDown = false;
    schedule();
  });
  document.addEventListener('visibilitychange', schedule);

  if (typeof reducedMotion.addEventListener === 'function') {
    reducedMotion.addEventListener('change', schedule);
  } else if (typeof reducedMotion.addListener === 'function') {
    reducedMotion.addListener(schedule);
  }

  return {
    restart: schedule,
    stop: clear,
  };
}
