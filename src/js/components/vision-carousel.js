const EDGE_TOLERANCE = 2;

export function initVisionCarousels() {
  document.querySelectorAll('[data-vision-carousel]').forEach((carousel) => {
    const track = carousel.querySelector('[data-vision-track]');
    const controls = carousel.querySelector('[data-vision-controls]');
    const previousButton = carousel.querySelector('[data-vision-previous]');
    const nextButton = carousel.querySelector('[data-vision-next]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const desktopLayout = window.matchMedia('(min-width: 901px)').matches;
    const mobileLayout = window.matchMedia('(max-width: 550px)').matches;
    const backgroundScene = carousel.closest('.space-field')?.querySelector('.space-field__scene');
    let backgroundBoostTimer;
    let mobileShuttleSettleTimer;
    let isTouchingTrack = false;
    let hasBoostedForGesture = false;

    if (!track || !controls || !previousButton || !nextButton) {
      return;
    }

    const getScrollStep = () => {
      const firstSlide = track.firstElementChild;

      if (!firstSlide) {
        return 0;
      }

      const styles = window.getComputedStyle(track);
      const gap = parseFloat(styles.columnGap || styles.gap) || 0;

      return firstSlide.getBoundingClientRect().width + gap;
    };

    const updateState = () => {
      const isOverflowing = track.scrollWidth > track.clientWidth + EDGE_TOLERANCE;
      const maximumScroll = Math.max(0, track.scrollWidth - track.clientWidth);

      controls.hidden = !isOverflowing;
      previousButton.disabled = !isOverflowing || track.scrollLeft <= EDGE_TOLERANCE;
      nextButton.disabled = !isOverflowing || track.scrollLeft >= maximumScroll - EDGE_TOLERANCE;
    };

    const boostBackground = () => {
      if (!mobileLayout || reducedMotion || !backgroundScene || !('getAnimations' in backgroundScene)) {
        return;
      }

      const backgroundAnimation = backgroundScene.getAnimations()[0];

      if (!backgroundAnimation) {
        return;
      }

      window.clearTimeout(backgroundBoostTimer);

      if ('updatePlaybackRate' in backgroundAnimation) {
        backgroundAnimation.updatePlaybackRate(15.4);
      } else {
        backgroundAnimation.playbackRate = 15.4;
      }

      backgroundBoostTimer = window.setTimeout(() => {
        if ('updatePlaybackRate' in backgroundAnimation) {
          backgroundAnimation.updatePlaybackRate(1);
        } else {
          backgroundAnimation.playbackRate = 1;
        }
      }, 1100);
    };

    const settleMobileShuttle = () => {
      if (!mobileLayout || !carousel.classList.contains('is-vision-revealed')) {
        return;
      }

      window.clearTimeout(mobileShuttleSettleTimer);
      carousel.classList.add('is-mobile-shuttle-settled');
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

    previousButton.addEventListener('click', () => {
      settleMobileShuttle();
      boostBackground();
      move(-1);
    });
    nextButton.addEventListener('click', () => {
      settleMobileShuttle();
      boostBackground();
      move(1);
    });
    track.addEventListener('touchstart', () => {
      isTouchingTrack = true;
      hasBoostedForGesture = false;
    }, { passive: true });
    track.addEventListener('touchend', () => {
      isTouchingTrack = false;
    }, { passive: true });
    track.addEventListener('scroll', () => {
      updateState();
      settleMobileShuttle();

      if (isTouchingTrack && !hasBoostedForGesture) {
        hasBoostedForGesture = true;
        boostBackground();
      }
    }, { passive: true });
    window.addEventListener('resize', updateState);

    if ('ResizeObserver' in window) {
      const resizeObserver = new ResizeObserver(updateState);
      resizeObserver.observe(track);
    }

    updateState();

    if ((!desktopLayout && !mobileLayout) || reducedMotion) {
      return;
    }

    carousel.classList.add(desktopLayout ? 'has-vision-motion' : 'has-mobile-vision-motion');

    const reveal = () => {
      const wasAlreadyRevealed = carousel.classList.contains('is-vision-revealed');

      carousel.classList.add('is-vision-revealed');

      if (mobileLayout && !wasAlreadyRevealed) {
        window.clearTimeout(mobileShuttleSettleTimer);
        mobileShuttleSettleTimer = window.setTimeout(() => {
          carousel.classList.add('is-mobile-shuttle-settled');
        }, 2300);
      }
    };

    if (!('IntersectionObserver' in window)) {
      reveal();
      return;
    }

    const revealObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.intersectionRatio >= 0.25) {
          reveal();
        } else if (!entry.isIntersecting) {
          const bounds = carousel.getBoundingClientRect();
          const isVerticallyOutsideViewport = bounds.bottom <= 0 || bounds.top >= window.innerHeight;

          if (isVerticallyOutsideViewport) {
            window.clearTimeout(mobileShuttleSettleTimer);
            carousel.classList.remove('is-mobile-shuttle-settled');
            carousel.classList.remove('is-vision-revealed');
          }
        }
      });
    }, {
      threshold: [0, 0.25],
    });

    revealObserver.observe(carousel);
  });
}
