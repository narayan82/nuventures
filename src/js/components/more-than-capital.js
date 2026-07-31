const AUTO_ADVANCE_DELAY = 4000;
const MOBILE_TEXT_TRANSITION = 220;
const STATE_ANGLES = [0, 90, 180, 270];

export function initMoreThanCapital() {
  document.querySelectorAll('[data-more-than-capital]').forEach((section) => {
    const tabs = [...section.querySelectorAll('[data-compass-state]')];
    const panels = [...section.querySelectorAll('[data-compass-panel]')];
    const positions = tabs.map((tab) => tab.closest('.more-than-capital__position'));
    const rotor = section.querySelector('[data-compass-rotor]');
    const previousButton = section.querySelector('[data-compass-previous]');
    const nextButton = section.querySelector('[data-compass-next]');

    if (!tabs.length || tabs.length !== panels.length || !rotor) {
      return;
    }

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const mobileLayout = window.matchMedia('(max-width: 550px)');
    let activeIndex = 0;
    let displayedIndex = 0;
    let currentRotation = 0;
    let autoTimer;
    let textTransitionTimer;
    let isVisible = !('IntersectionObserver' in window);
    let isPaused = false;

    const stopTimer = () => {
      window.clearTimeout(autoTimer);
    };

    const scheduleNext = () => {
      stopTimer();

      if (!isVisible || isPaused || document.hidden) {
        return;
      }

      autoTimer = window.setTimeout(() => {
        activateState((activeIndex + 1) % tabs.length, true);
      }, AUTO_ADVANCE_DELAY);
    };

    const getManualRotation = (targetAngle) => {
      const normalizedCurrent = ((currentRotation % 360) + 360) % 360;
      let delta = ((targetAngle - normalizedCurrent + 540) % 360) - 180;

      if (delta === -180) {
        delta = 180;
      }

      return currentRotation + delta;
    };

    const syncTextState = (index, isEntering = false) => {
      displayedIndex = index;

      tabs.forEach((tab, tabIndex) => {
        const isActive = tabIndex === index;
        tab.classList.toggle('is-active', isActive);
        tab.setAttribute('aria-selected', String(isActive));
        tab.setAttribute('tabindex', isActive ? '0' : '-1');

        const position = positions[tabIndex];
        position?.classList.toggle('is-active', isActive);
        position?.classList.toggle('is-entering', isActive && isEntering);
        position?.classList.remove('is-exiting');
      });

      panels.forEach((panel, panelIndex) => {
        const isActive = panelIndex === index;
        panel.classList.toggle('is-active', isActive);
        panel.classList.toggle('is-entering', isActive && isEntering);
        panel.classList.remove('is-exiting');
        panel.setAttribute('aria-hidden', String(!isActive));
      });

      if (isEntering) {
        window.requestAnimationFrame(() => {
          window.requestAnimationFrame(() => {
            positions[index]?.classList.remove('is-entering');
            panels[index]?.classList.remove('is-entering');
          });
        });
      }
    };

    function activateState(index, isAutomatic = false) {
      if (index === activeIndex && !isAutomatic) {
        scheduleNext();
        return;
      }

      stopTimer();
      window.clearTimeout(textTransitionTimer);

      const previousIndex = activeIndex;
      const previousDisplayedIndex = displayedIndex;

      if (isAutomatic) {
        currentRotation += 90;
      } else {
        currentRotation = getManualRotation(STATE_ANGLES[index]);
      }

      activeIndex = index;
      rotor.style.setProperty('--compass-rotation', `${currentRotation}deg`);

      if (mobileLayout.matches && !reducedMotion && previousIndex !== activeIndex) {
        positions.forEach((position) => position?.classList.remove('is-entering', 'is-exiting'));
        panels.forEach((panel) => panel.classList.remove('is-entering', 'is-exiting'));
        positions[previousDisplayedIndex]?.classList.add('is-exiting');
        panels[previousDisplayedIndex]?.classList.add('is-exiting');

        textTransitionTimer = window.setTimeout(() => {
          syncTextState(activeIndex, true);
        }, MOBILE_TEXT_TRANSITION);
      } else {
        syncTextState(activeIndex);
      }

      scheduleNext();
    }

    tabs.forEach((tab, index) => {
      tab.addEventListener('click', () => activateState(index));

      tab.addEventListener('keydown', (event) => {
        if (!['ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'Home', 'End'].includes(event.key)) {
          return;
        }

        event.preventDefault();

        let nextIndex = index;

        if (event.key === 'Home') {
          nextIndex = 0;
        } else if (event.key === 'End') {
          nextIndex = tabs.length - 1;
        } else if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
          nextIndex = (index + 1) % tabs.length;
        } else {
          nextIndex = (index - 1 + tabs.length) % tabs.length;
        }

        tabs[nextIndex].focus();
        activateState(nextIndex);
      });
    });

    previousButton?.addEventListener('click', () => {
      activateState((activeIndex - 1 + tabs.length) % tabs.length);
    });

    nextButton?.addEventListener('click', () => {
      activateState((activeIndex + 1) % tabs.length);
    });

    section.addEventListener('mouseenter', () => {
      isPaused = true;
      stopTimer();
    });

    section.addEventListener('mouseleave', () => {
      isPaused = false;
      scheduleNext();
    });

    section.addEventListener('focusin', () => {
      isPaused = true;
      stopTimer();
    });

    section.addEventListener('focusout', (event) => {
      if (section.contains(event.relatedTarget)) {
        return;
      }

      isPaused = false;
      scheduleNext();
    });

    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        stopTimer();
      } else {
        scheduleNext();
      }
    });

    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries) => {
        isVisible = entries.some((entry) => entry.isIntersecting);

        if (isVisible) {
          scheduleNext();
        } else {
          stopTimer();
        }
      }, {
        threshold: 0.25,
      });

      observer.observe(section);
    } else {
      scheduleNext();
    }

    if (reducedMotion) {
      section.classList.add('has-reduced-motion');
    }
  });
}
