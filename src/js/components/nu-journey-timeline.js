const CANVAS_GUTTER = 48;
const CARDS_TOP = 74;
const CANVAS_BOTTOM = 32;
const SCROLL_STOP_DELAY = 180;

export function initNuJourneyTimelines() {
  document.querySelectorAll('[data-nu-journey-timeline]').forEach((timeline) => {
    const viewport = timeline.querySelector('[data-timeline-viewport]');
    const canvas = timeline.querySelector('[data-timeline-canvas]');
    const stickyYears = timeline.querySelector('[data-timeline-sticky-years]');
    const years = timeline.querySelector('[data-timeline-years]');
    const cards = Array.from(timeline.querySelectorAll('[data-timeline-entry]'));
    const triggers = Array.from(timeline.querySelectorAll('[data-timeline-dialog-trigger]'));
    const templates = Array.from(timeline.querySelectorAll('[data-timeline-dialog-template]'));
    const dialog = timeline.querySelector('[data-timeline-dialog]');
    const dialogContent = timeline.querySelector('[data-timeline-dialog-content]');
    const dialogClose = timeline.querySelector('[data-timeline-dialog-close]');
    const dialogPrevious = timeline.querySelector('[data-timeline-dialog-previous]');
    const dialogNext = timeline.querySelector('[data-timeline-dialog-next]');

    if (!viewport || !canvas || !stickyYears || !years || !cards.length) {
      return;
    }

    let frame = 0;
    let wasOverflowing = null;
    let scrollTimer = 0;
    let scrollFrameRequested = false;
    let activeEntryIndex = 0;
    let scrollPosition = 0;
    let bodyStyles = {};
    let entriesRevealed = false;

    timeline.classList.add('is-entry-reveal-ready');
    cards.forEach((card, cardIndex) => {
      card.style.setProperty('--entry-reveal-delay', `${cardIndex * 120}ms`);
    });

    const layout = () => {
      const styles = getComputedStyle(canvas);
      const yearSpacing = Number.parseFloat(styles.getPropertyValue('--year-spacing')) || 105;
      const lanes = [];
      const placedCards = [];
      let furthestCardEdge = 0;
      let furthestCardBottom = CARDS_TOP;

      cards.forEach((card) => {
        const monthWidth = yearSpacing / 12;
        const monthIndex = Number.parseInt(card.dataset.monthIndex, 10) || 0;
        const monthSpan = Math.max(1, Number.parseInt(card.dataset.monthSpan, 10) || 1);
        const cardStart = CANVAS_GUTTER + (monthIndex * monthWidth);
        const cardWidth = monthSpan * monthWidth;

        card.style.width = `${cardWidth}px`;
        const cardEnd = cardStart + card.offsetWidth;
        const cardHeight = card.offsetHeight;
        let laneIndex = lanes.findIndex((lane) => cardStart >= lane.lastEnd);
        let cardTop;

        if (laneIndex === -1) {
          laneIndex = lanes.length;
          cardTop = furthestCardBottom;
          lanes.push({
            lastEnd: cardEnd,
            lastTop: cardTop,
            lastHeight: cardHeight,
          });
        } else {
          const lane = lanes[laneIndex];
          cardTop = lane.lastTop + (lane.lastHeight * 1.2);
          let adjustedForCollision = true;

          while (adjustedForCollision) {
            adjustedForCollision = false;

            placedCards.forEach((placedCard) => {
              const overlapsHorizontally = cardStart < placedCard.end && cardEnd > placedCard.start;
              const overlapsVertically = cardTop < placedCard.bottom && (cardTop + cardHeight) > placedCard.top;

              if (overlapsHorizontally && overlapsVertically) {
                cardTop = placedCard.bottom;
                adjustedForCollision = true;
              }
            });
          }

          lane.lastEnd = cardEnd;
          lane.lastTop = cardTop;
          lane.lastHeight = cardHeight;
        }

        const cardBottom = cardTop + cardHeight;

        card.dataset.lane = String(laneIndex);
        card.style.left = `${cardStart}px`;
        card.style.top = `${cardTop}px`;
        placedCards.push({
          start: cardStart,
          end: cardEnd,
          top: cardTop,
          bottom: cardBottom,
        });
        furthestCardEdge = Math.max(furthestCardEdge, cardEnd);
        furthestCardBottom = Math.max(furthestCardBottom, cardBottom);
      });

      const naturalWidth = Math.max(420, Math.ceil(furthestCardEdge + CANVAS_GUTTER));
      const naturalHeight = Math.max(240, Math.ceil(furthestCardBottom + CANVAS_BOTTOM));
      canvas.style.setProperty('--timeline-width', `${naturalWidth}px`);
      canvas.style.setProperty('--timeline-height', `${naturalHeight}px`);
      years.style.width = `${naturalWidth}px`;

      const isOverflowing = naturalWidth > viewport.clientWidth;
      const canvasOffset = isOverflowing ? 0 : (viewport.clientWidth - naturalWidth) / 2;

      viewport.style.setProperty('--timeline-canvas-offset', `${canvasOffset}px`);
      years.style.marginLeft = `${canvasOffset}px`;
      years.style.transform = `translateX(${-viewport.scrollLeft}px)`;
      timeline.classList.toggle('nu-journey-timeline--overflowing', isOverflowing);
      timeline.classList.toggle('nu-journey-timeline--fits', !isOverflowing);

      if (isOverflowing && wasOverflowing !== true) {
        viewport.scrollLeft = 0;
      }

      wasOverflowing = isOverflowing;

      if (!entriesRevealed) {
        entriesRevealed = true;

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
          timeline.classList.add('is-entry-revealed');
        } else {
          requestAnimationFrame(() => {
            timeline.classList.add('is-entry-revealed');
          });
        }
      }
    };

    const scheduleLayout = () => {
      cancelAnimationFrame(frame);
      frame = requestAnimationFrame(layout);
    };

    const resizeObserver = new ResizeObserver(scheduleLayout);
    resizeObserver.observe(viewport);
    cards.forEach((card) => resizeObserver.observe(card));

    cards.forEach((card) => {
      card.querySelectorAll('img').forEach((image) => {
        if (!image.complete) {
          image.addEventListener('load', scheduleLayout, { once: true });
        }
      });
    });

    window.addEventListener('orientationchange', scheduleLayout);
    window.addEventListener('scroll', () => {
      if (!scrollFrameRequested) {
        scrollFrameRequested = true;

        requestAnimationFrame(() => {
          stickyYears.classList.toggle('is-scroll-hidden', window.scrollY > 0);
          scrollFrameRequested = false;
        });
      }

      window.clearTimeout(scrollTimer);
      scrollTimer = window.setTimeout(() => {
        stickyYears.classList.remove('is-scroll-hidden');
      }, SCROLL_STOP_DELAY);
    }, { passive: true });
    viewport.addEventListener('scroll', () => {
      years.style.transform = `translateX(${-viewport.scrollLeft}px)`;
    }, { passive: true });

    const lockPageScroll = () => {
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

    const unlockPageScroll = () => {
      Object.assign(document.body.style, bodyStyles);
      window.scrollTo(0, scrollPosition);
    };

    const renderDialogEntry = (entryIndex) => {
      if (!dialogContent || !templates.length) {
        return;
      }

      activeEntryIndex = (entryIndex + templates.length) % templates.length;
      dialogContent.replaceChildren(templates[activeEntryIndex].content.cloneNode(true));
    };

    const openDialog = (entryIndex) => {
      if (!dialog || typeof dialog.showModal !== 'function') {
        return;
      }

      renderDialogEntry(entryIndex);
      lockPageScroll();
      dialog.showModal();
      dialogClose?.focus();
    };

    const closeDialog = () => {
      if (dialog?.open) {
        dialog.close();
      }
    };

    triggers.forEach((trigger, entryIndex) => {
      trigger.addEventListener('click', () => openDialog(entryIndex));
    });

    dialogClose?.addEventListener('click', closeDialog);
    dialogPrevious?.addEventListener('click', () => renderDialogEntry(activeEntryIndex - 1));
    dialogNext?.addEventListener('click', () => renderDialogEntry(activeEntryIndex + 1));

    dialog?.addEventListener('click', (event) => {
      if (event.target === dialog) {
        closeDialog();
      }
    });

    dialog?.addEventListener('keydown', (event) => {
      if (event.key === 'ArrowLeft') {
        renderDialogEntry(activeEntryIndex - 1);
      } else if (event.key === 'ArrowRight') {
        renderDialogEntry(activeEntryIndex + 1);
      }
    });

    dialog?.addEventListener('close', () => {
      unlockPageScroll();
      triggers[activeEntryIndex]?.focus();
    });

    scheduleLayout();
  });
}
