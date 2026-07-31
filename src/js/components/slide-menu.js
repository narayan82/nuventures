const OPEN_CLASS = 'is-open';
const TRANSITION_DURATION = 300;

export function initSlideMenu() {
  const trigger = document.querySelector('.site-header__menu-button');
  const menu = document.querySelector('[data-slide-menu]');

  if (!trigger || !menu) {
    return;
  }

  const closeButton = menu.querySelector('[data-slide-menu-close]');
  const backdrop = menu.querySelector('[data-slide-menu-backdrop]');
  const panel = menu.querySelector('.slide-menu__panel');
  const focusableElements = [...menu.querySelectorAll('a[href], button:not([disabled])')]
    .filter((element) => element !== backdrop);
  let scrollPosition = 0;
  let closeTimer;
  let bodyStyles = {};

  const lockScroll = () => {
    scrollPosition = window.scrollY;
    const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;

    bodyStyles = {
      position: document.body.style.position,
      top: document.body.style.top,
      left: document.body.style.left,
      right: document.body.style.right,
      width: document.body.style.width,
      paddingRight: document.body.style.paddingRight,
    };

    document.body.style.position = 'fixed';
    document.body.style.top = `-${scrollPosition}px`;
    document.body.style.left = '0';
    document.body.style.right = '0';
    document.body.style.width = '100%';

    if (scrollbarWidth > 0) {
      document.body.style.paddingRight = `${scrollbarWidth}px`;
    }
  };

  const unlockScroll = () => {
    Object.assign(document.body.style, bodyStyles);
    window.scrollTo(0, scrollPosition);
  };

  const openMenu = () => {
    window.clearTimeout(closeTimer);
    lockScroll();
    menu.classList.add(OPEN_CLASS);
    menu.setAttribute('aria-hidden', 'false');
    trigger.setAttribute('aria-expanded', 'true');
    closeButton?.focus();
  };

  const closeMenu = () => {
    if (!menu.classList.contains(OPEN_CLASS)) {
      return;
    }

    menu.classList.remove(OPEN_CLASS);
    menu.setAttribute('aria-hidden', 'true');
    trigger.setAttribute('aria-expanded', 'false');
    unlockScroll();
    trigger.focus();

    closeTimer = window.setTimeout(() => {
      if (panel) {
        panel.scrollTop = 0;
      }
    }, TRANSITION_DURATION);
  };

  const keepFocusInside = (event) => {
    if (event.key !== 'Tab' || !menu.classList.contains(OPEN_CLASS)) {
      return;
    }

    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];

    if (event.shiftKey && document.activeElement === firstElement) {
      event.preventDefault();
      lastElement.focus();
    } else if (!event.shiftKey && document.activeElement === lastElement) {
      event.preventDefault();
      firstElement.focus();
    }
  };

  trigger.addEventListener('click', openMenu);
  closeButton?.addEventListener('click', closeMenu);
  backdrop?.addEventListener('click', closeMenu);

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeMenu();
      return;
    }

    keepFocusInside(event);
  });
}
