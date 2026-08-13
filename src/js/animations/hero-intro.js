import { MarkerHighlighter } from '../vendor/marker-highlight.js';

const TYPING_DURATION = 1800;
const MARKER_PAUSE = 320;
const MARKER_DURATION = 650;
const MARKER_GAP = 160;
const SWAP_OUT_DURATION = 220;
const SWAP_MARKER_PAUSE = 300;

const initialSentence = `
  We back
  <button class="hero-intro__marker-wrap hero-intro__detail-trigger" type="button" data-hero-detail-trigger><mark class="hero-intro__marker">bold founders</mark>.</button> Built by
  <button class="hero-intro__marker-wrap hero-intro__detail-trigger" type="button" data-hero-technologies-trigger><mark class="hero-intro__marker">operators</mark>.</button>
  <button class="hero-intro__marker-wrap hero-intro__detail-trigger" type="button" data-hero-future-trigger><mark class="hero-intro__marker">Conviction</mark></button>
  for the long term.
`;

const detailSentence = `
  <strong class="hero-intro__detail-lead">Bold founders</strong>
  see what others miss drawing on an
  <button class="hero-intro__marker-wrap hero-intro__detail-trigger" type="button" data-hero-technologies-trigger><mark class="hero-intro__marker">operator</mark></button>
  mindset to build.
`;

const technologiesSentence = `
  <strong class="hero-intro__detail-lead">Operator perspective</strong>
  shaped by scaling &amp; navigating companies with
  <button class="hero-intro__marker-wrap hero-intro__detail-trigger" type="button" data-hero-future-trigger><mark class="hero-intro__marker">conviction</mark>.</button>
`;

const futureSentence = `
  <strong class="hero-intro__detail-lead">Conviction</strong>
  means backing founders with capital and trust as they build with 
  <button class="hero-intro__marker-wrap hero-intro__detail-trigger" type="button" data-hero-reset-trigger><mark class="hero-intro__marker">NuVentures.</mark></button>
`;

export function initHeroIntro() {
  const hero = document.querySelector('[data-hero-intro]');

  if (!hero) {
    return;
  }

  const copy = hero.querySelector('.hero-intro__copy');
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const pendingTimers = new Set();
  let isDetailActive = false;
  let isTechnologiesActive = false;
  let isFutureActive = false;

  if (!copy) {
    return;
  }

  const schedule = (callback, delay) => {
    const timer = window.setTimeout(() => {
      pendingTimers.delete(timer);
      callback();
    }, delay);

    pendingTimers.add(timer);
  };

  const clearPendingTimers = () => {
    pendingTimers.forEach((timer) => window.clearTimeout(timer));
    pendingTimers.clear();
  };

  const showFinalMarkerState = () => {
    copy.querySelectorAll('.hero-intro__marker').forEach((marker) => {
      marker.classList.add('is-marker-ready');
    });
  };

  const prepareTypedText = () => {
    const textNodes = [];
    const walker = document.createTreeWalker(copy, NodeFilter.SHOW_TEXT);

    while (walker.nextNode()) {
      if (walker.currentNode.nodeValue.trim()) {
        textNodes.push(walker.currentNode);
      }
    }

    textNodes.forEach((textNode) => {
      const fragment = document.createDocumentFragment();

      [...textNode.nodeValue].forEach((character) => {
        if (/\s/.test(character)) {
          fragment.append(document.createTextNode(character));
          return;
        }

        const characterNode = document.createElement('span');
        characterNode.className = 'hero-intro__typed-char';
        characterNode.textContent = character;
        fragment.append(characterNode);
      });

      textNode.replaceWith(fragment);
    });

    return [...copy.querySelectorAll('.hero-intro__typed-char')];
  };

  const finishTyping = () => {
    copy.querySelectorAll('.hero-intro__typed-char').forEach((character) => {
      character.replaceWith(document.createTextNode(character.textContent));
    });

    copy.normalize();
    hero.classList.remove('is-typing');
  };

  const playTyping = (characters, onComplete) => {
    const characterDelay = characters.length > 1
      ? TYPING_DURATION / (characters.length - 1)
      : 0;

    characters.forEach((character, index) => {
      schedule(() => {
        character.classList.add('is-visible');
      }, index * characterDelay);
    });

    schedule(() => {
      finishTyping();
      onComplete();
    }, TYPING_DURATION);
  };

  if (reducedMotion) {
    showFinalMarkerState();
    hero.classList.add('is-visible');
  }

  let hasPlayed = false;
  const typedCharacters = reducedMotion ? [] : prepareTypedText();

  if (!reducedMotion) {
    hero.classList.add('is-animating', 'is-typing');
  }

  const drawMarker = (wrapper) => {
    const marker = wrapper.querySelector('.hero-intro__marker');

    if (!marker) {
      return;
    }

    marker.classList.add('is-marker-ready');

    new MarkerHighlighter(wrapper, {
      drawingMode: 'highlight',
      animationSpeed: MARKER_DURATION,
      height: 0.88,
      offset: 0.12,
      padding: 0.10,
      highlight: {
        amplitude: 0.1,
        wavelength: 2,
        roughEnds: 8.35,
      },
    });

    schedule(() => {
      wrapper.querySelectorAll('.highlight').forEach((highlight) => {
        highlight.classList.replace('highlight', 'hero-intro__marker-static');
      });
    }, MARKER_DURATION + 80);
  };

  const playMarkers = (wrappers, initialDelay) => {
    const markerStep = MARKER_DURATION + MARKER_GAP;

    wrappers.forEach((wrapper, index) => {
      schedule(() => {
        drawMarker(wrapper);
      }, initialDelay + (index * markerStep));
    });
  };

  const playSequence = () => {
    if (hasPlayed) {
      return;
    }

    hasPlayed = true;
    hero.classList.add('is-visible');

    playTyping(typedCharacters, () => {
      playMarkers(
        [...copy.querySelectorAll('.hero-intro__marker-wrap')],
        MARKER_PAUSE,
      );
    });
  };

  if (reducedMotion) {
    // The final initial state is already visible.
  } else if (!('IntersectionObserver' in window)) {
    playSequence();
  } else {
    const observer = new IntersectionObserver((entries) => {
      if (entries.some((entry) => entry.isIntersecting)) {
        observer.disconnect();
        playSequence();
      }
    }, {
      threshold: 0.25,
    });

    observer.observe(hero);
  }

  const swapSentence = (sentence) => {
    clearPendingTimers();

    const showSentence = () => {
      copy.querySelectorAll('.highlight, .hero-intro__marker-static').forEach((highlight) => {
        highlight.remove();
      });
      copy.innerHTML = sentence;

      if (reducedMotion) {
        showFinalMarkerState();
        return;
      }

      copy.classList.remove('is-swap-exiting');
      hero.classList.add('is-typing');
      const sentenceCharacters = prepareTypedText();

      playTyping(sentenceCharacters, () => {
        copy.classList.remove('is-swap-entering', 'is-swap-visible');
        playMarkers(
          [...copy.querySelectorAll('.hero-intro__marker-wrap')],
          SWAP_MARKER_PAUSE,
        );
      });
    };

    if (reducedMotion) {
      showSentence();
      return;
    }

    copy.classList.add('is-swap-exiting');
    schedule(showSentence, SWAP_OUT_DURATION);
  };

  hero.addEventListener('click', (event) => {
    const resetTrigger = event.target.closest('[data-hero-reset-trigger]');

    if (resetTrigger) {
      isDetailActive = false;
      isTechnologiesActive = false;
      isFutureActive = false;
      swapSentence(initialSentence);
      return;
    }

    const futureTrigger = event.target.closest('[data-hero-future-trigger]');

    if (futureTrigger) {
      if (isFutureActive) {
        return;
      }

      isFutureActive = true;
      swapSentence(futureSentence);
      return;
    }

    const technologiesTrigger = event.target.closest('[data-hero-technologies-trigger]');

    if (technologiesTrigger) {
      if (isTechnologiesActive) {
        return;
      }

      isTechnologiesActive = true;
      swapSentence(technologiesSentence);
      return;
    }

    const detailTrigger = event.target.closest('[data-hero-detail-trigger]');

    if (!detailTrigger || isDetailActive || isTechnologiesActive || isFutureActive) {
      return;
    }

    isDetailActive = true;
    swapSentence(detailSentence);
  });
}
