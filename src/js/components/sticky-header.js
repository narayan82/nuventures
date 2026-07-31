const SCROLL_STOP_DELAY = 180;

export function initStickyHeader() {
  const header = document.querySelector('.site-header');

  if (!header) {
    return;
  }

  let scrollTimer;
  let frameRequested = false;
  let touchStartY = 0;

  const showHeader = () => {
    header.classList.remove('is-scroll-hidden');
  };

  const menuIsOpen = () => document.querySelector('[data-slide-menu].is-open');

  const scheduleHeaderReturn = () => {
    window.clearTimeout(scrollTimer);
    scrollTimer = window.setTimeout(showHeader, SCROLL_STOP_DELAY);
  };

  const handleScroll = () => {
    if (!frameRequested) {
      frameRequested = true;

      window.requestAnimationFrame(() => {
        if (window.scrollY <= 0 || menuIsOpen()) {
          showHeader();
        } else {
          header.classList.add('is-scroll-hidden');
        }

        frameRequested = false;
      });
    }

    scheduleHeaderReturn();
  };

  window.addEventListener('scroll', handleScroll, { passive: true });

  window.addEventListener(
    'touchstart',
    (event) => {
      touchStartY = event.touches[0]?.clientY ?? 0;
    },
    { passive: true },
  );

  window.addEventListener(
    'touchmove',
    (event) => {
      const currentY = event.touches[0]?.clientY ?? touchStartY;

      if (Math.abs(currentY - touchStartY) > 3 && window.scrollY > 0 && !menuIsOpen()) {
        header.classList.add('is-scroll-hidden');
        scheduleHeaderReturn();
      }
    },
    { passive: true },
  );

  window.addEventListener('touchend', scheduleHeaderReturn, { passive: true });
}
