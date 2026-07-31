const ROTATE_AMPLITUDE = 9;
const HOVER_SCALE = 1.025;

export function initInvestorCardTilt() {
  const supportsTilt = window.matchMedia(
    '(hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference)',
  ).matches;

  if (!supportsTilt) {
    return;
  }

  document.querySelectorAll('[data-investor-tilt]').forEach((card) => {
    let animationFrame;

    const updateTilt = (event) => {
      const bounds = card.getBoundingClientRect();
      const horizontalPosition = (event.clientX - bounds.left) / bounds.width;
      const verticalPosition = (event.clientY - bounds.top) / bounds.height;
      const rotateY = (horizontalPosition - 0.5) * ROTATE_AMPLITUDE * 2;
      const rotateX = (verticalPosition - 0.5) * ROTATE_AMPLITUDE * -2;

      window.cancelAnimationFrame(animationFrame);
      animationFrame = window.requestAnimationFrame(() => {
        card.style.setProperty('--investor-tilt-x', `${rotateX.toFixed(2)}deg`);
        card.style.setProperty('--investor-tilt-y', `${rotateY.toFixed(2)}deg`);
        card.style.setProperty('--investor-tilt-scale', HOVER_SCALE);
        card.classList.add('is-tilting');
      });
    };

    const resetTilt = () => {
      window.cancelAnimationFrame(animationFrame);
      card.style.setProperty('--investor-tilt-x', '0deg');
      card.style.setProperty('--investor-tilt-y', '0deg');
      card.style.setProperty('--investor-tilt-scale', '1');
      card.classList.remove('is-tilting');
    };

    card.addEventListener('pointermove', updateTilt);
    card.addEventListener('pointerleave', resetTilt);
    card.addEventListener('blur', resetTilt);
  });
}
