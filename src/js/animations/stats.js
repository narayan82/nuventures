const COUNT_DURATION = 900;

export function initStats() {
  const section = document.querySelector('[data-stats]');

  if (!section) {
    return;
  }

  const values = [...section.querySelectorAll('[data-stat-value]')];
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const activeValues = new WeakSet();
  let hasPlayed = false;

  if (reducedMotion) {
    return;
  }

  const easeOutCubic = (progress) => 1 - ((1 - progress) ** 3);

  const animateValue = (value) => {
    if (activeValues.has(value)) {
      return false;
    }

    activeValues.add(value);
    const target = Number(value.dataset.statValue);
    const prefix = value.dataset.statPrefix || '';
    const suffix = value.dataset.statSuffix || '';
    const startTime = performance.now();

    const update = (currentTime) => {
      const progress = Math.min((currentTime - startTime) / COUNT_DURATION, 1);
      const easedProgress = easeOutCubic(progress);

      value.textContent = `${prefix}${Math.round(target * easedProgress)}${suffix}`;

      if (progress < 1) {
        window.requestAnimationFrame(update);
      } else {
        activeValues.delete(value);
      }
    };

    window.requestAnimationFrame(update);
    return true;
  };

  const animateValues = () => {
    if (hasPlayed) {
      return;
    }

    hasPlayed = true;
    values.forEach(animateValue);
  };

  values.forEach((value) => {
    const item = value.closest('.home-stats__item');

    item?.addEventListener('mouseenter', () => {
      if (!hasPlayed) {
        return;
      }

      animateValue(value);
    });
  });

  if (!('IntersectionObserver' in window)) {
    animateValues();
    return;
  }

  const observer = new IntersectionObserver((entries) => {
    if (entries.some((entry) => entry.isIntersecting)) {
      observer.disconnect();
      animateValues();
    }
  }, {
    threshold: 0.35,
  });

  observer.observe(section);
}
