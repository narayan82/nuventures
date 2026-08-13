const MIN_PIXELS_PER_YEAR = 140;
const TIMELINE_EDGE_PADDING = 0;
const CARDS_TOP = 74;
const CANVAS_BOTTOM = 32;
const SCROLL_STOP_DELAY = 180;
const MOBILE_BREAKPOINT = 550;
const MOBILE_DATE_COLUMN = 68;
const MOBILE_COLUMN_GAP = 12;
const MOBILE_MONTH_HEIGHT = 24;
const MOBILE_TIMELINE_TOP = 32;
const MOBILE_TIMELINE_BOTTOM = 32;

export function initNuJourneyTimelines() {
  document.querySelectorAll('[data-nu-journey-timeline]').forEach((timeline) => {
    const viewport = timeline.querySelector('[data-timeline-viewport]');
    const canvas = timeline.querySelector('[data-timeline-canvas]');
    const grid = timeline.querySelector('.nu-journey-timeline__grid');
    const stickyYears = timeline.querySelector('[data-timeline-sticky-years]');
    const years = timeline.querySelector('[data-timeline-years]');
    const monthLabels = Array.from(timeline.querySelectorAll('[data-timeline-month-label]'));
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

    const entryMonthMarkers = grid
      ? [...new Set(cards.map((card) => Number.parseInt(card.dataset.monthIndex, 10) || 0))]
        .map((monthIndex) => {
          const marker = document.createElement('span');

          marker.className = 'nu-journey-timeline__entry-month';
          marker.dataset.monthIndex = String(monthIndex);
          marker.setAttribute('aria-hidden', 'true');
          grid.append(marker);

          return marker;
        })
      : [];

    const layout = () => {
      const isMobile = window.matchMedia(`(max-width: ${MOBILE_BREAKPOINT}px)`).matches;

      if (isMobile) {
        monthLabels.forEach((label) => {
          label.style.left = '';
          const monthIndex = Number.parseInt(label.dataset.monthIndex, 10) || 0;
          label.style.top = `${MOBILE_TIMELINE_TOP + (monthIndex * MOBILE_MONTH_HEIGHT) + 7}px`;
        });
        const finalMonth = cards.reduce((latestMonth, card) => {
          const monthIndex = Number.parseInt(card.dataset.monthIndex, 10) || 0;
          const monthSpan = Math.max(1, Number.parseInt(card.dataset.monthSpan, 10) || 1);

          return Math.max(latestMonth, monthIndex + monthSpan);
        }, 1);
        const naturalHeight = MOBILE_TIMELINE_TOP
          + (finalMonth * MOBILE_MONTH_HEIGHT)
          + MOBILE_TIMELINE_BOTTOM;
        const mobileCardColumnWidth = Math.max(
          0,
          viewport.clientWidth - MOBILE_DATE_COLUMN - MOBILE_COLUMN_GAP - 20,
        );
        const cardWidth = mobileCardColumnWidth * 0.5;

        cards.forEach((card, cardIndex) => {
          const monthIndex = Number.parseInt(card.dataset.monthIndex, 10) || 0;
          const monthSpan = Math.max(1, Number.parseInt(card.dataset.monthSpan, 10) || 1);
          const columnIndex = cardIndex % 2;

          card.dataset.lane = String(columnIndex);
          card.style.left = `${
            MOBILE_DATE_COLUMN
            + MOBILE_COLUMN_GAP
            + (columnIndex * cardWidth)
          }px`;
          card.style.top = `${MOBILE_TIMELINE_TOP + (monthIndex * MOBILE_MONTH_HEIGHT)}px`;
          card.style.width = `${cardWidth}px`;
          card.style.height = `${monthSpan * MOBILE_MONTH_HEIGHT}px`;
        });

        canvas.style.setProperty('--timeline-width', '100%');
        canvas.style.setProperty('--timeline-height', `${naturalHeight}px`);
        canvas.style.setProperty('--mobile-month-height', `${MOBILE_MONTH_HEIGHT}px`);
        years.style.width = `${MOBILE_DATE_COLUMN}px`;
        years.style.height = `${naturalHeight}px`;
        years.style.marginLeft = '0';
        years.style.transform = 'none';
        stickyYears.style.setProperty('--timeline-height', `${naturalHeight}px`);
        timeline.style.setProperty('--mobile-month-height', `${MOBILE_MONTH_HEIGHT}px`);
        viewport.style.setProperty('--timeline-canvas-offset', '0px');
        viewport.scrollLeft = 0;
        timeline.classList.remove('nu-journey-timeline--overflowing', 'nu-journey-timeline--fits');
        wasOverflowing = false;

        if (!entriesRevealed) {
          entriesRevealed = true;

          requestAnimationFrame(() => {
            timeline.classList.add('is-entry-revealed');
          });
        }

        return;
      }

      years.style.height = '';
      monthLabels.forEach((label) => {
        label.style.top = '';
      });
      stickyYears.style.removeProperty('--timeline-height');
      timeline.style.removeProperty('--mobile-month-height');
      cards.forEach((card) => {
        card.style.height = '';
      });

      const lanes = [];
      const placedCards = [];
      const earliestYear = Number.parseInt(timeline.dataset.earliestYear, 10) || 0;
      const latestYear = Number.parseInt(timeline.dataset.latestYear, 10) || earliestYear;
      const yearSpan = Math.max(0, latestYear - earliestYear);
      const finalMonthExtent = cards.reduce((latestMonth, card) => {
        const monthIndex = Number.parseInt(card.dataset.monthIndex, 10) || 0;
        const monthSpan = Math.max(1, Number.parseInt(card.dataset.monthSpan, 10) || 1);

        return Math.max(latestMonth, monthIndex + monthSpan);
      }, 0);
      const timelineYearExtent = Math.max(yearSpan, finalMonthExtent / 12);
      const availableWidth = viewport.clientWidth;
      const usableWidth = Math.max(0, availableWidth - (TIMELINE_EDGE_PADDING * 2));
      const fittedYearSpacing = timelineYearExtent > 0
        ? usableWidth / timelineYearExtent
        : usableWidth;
      const yearSpacing = Math.max(MIN_PIXELS_PER_YEAR, fittedYearSpacing);
      const calculatedCanvasWidth = timelineYearExtent > 0
        ? (TIMELINE_EDGE_PADDING * 2) + (timelineYearExtent * yearSpacing)
        : Math.max(420, availableWidth);
      const timelineStart = timelineYearExtent > 0
        ? TIMELINE_EDGE_PADDING
        : calculatedCanvasWidth / 2;
      const monthWidth = yearSpacing / 12;
      let furthestCardEdge = 0;
      let furthestCardBottom = CARDS_TOP;

      entryMonthMarkers.forEach((marker) => {
        const monthIndex = Number.parseInt(marker.dataset.monthIndex, 10) || 0;

        marker.style.left = `${timelineStart + (monthIndex * monthWidth)}px`;
      });

      monthLabels.forEach((label) => {
        const monthIndex = Number.parseInt(label.dataset.monthIndex, 10) || 0;

        label.style.left = `${timelineStart + (monthIndex * monthWidth)}px`;
      });

      cards.forEach((card) => {
        const monthIndex = Number.parseInt(card.dataset.monthIndex, 10) || 0;
        const monthSpan = Math.max(1, Number.parseInt(card.dataset.monthSpan, 10) || 1);
        const cardStart = timelineStart + (monthIndex * monthWidth);
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

      const naturalWidth = Math.max(
        420,
        Math.ceil(calculatedCanvasWidth - 0.01),
        Math.ceil(furthestCardEdge + TIMELINE_EDGE_PADDING - 0.01),
      );
      const naturalHeight = Math.max(240, Math.ceil(furthestCardBottom + CANVAS_BOTTOM));
      canvas.style.setProperty('--timeline-width', `${naturalWidth}px`);
      canvas.style.setProperty('--timeline-canvas-width', `${naturalWidth}px`);
      canvas.style.setProperty('--timeline-year-width', `${yearSpacing}px`);
      canvas.style.setProperty('--timeline-edge-padding', `${timelineStart}px`);
      canvas.style.setProperty('--timeline-height', `${naturalHeight}px`);
      years.style.width = `${naturalWidth}px`;
      years.style.setProperty('--timeline-width', `${naturalWidth}px`);
      years.style.setProperty('--timeline-canvas-width', `${naturalWidth}px`);
      years.style.setProperty('--timeline-year-width', `${yearSpacing}px`);
      years.style.setProperty('--timeline-edge-padding', `${timelineStart}px`);

      const isOverflowing = naturalWidth > viewport.clientWidth + 1;
      const canvasOffset = isOverflowing ? 0 : (viewport.clientWidth - naturalWidth) / 2;

      viewport.style.setProperty('--timeline-canvas-offset', `${canvasOffset}px`);
      viewport.style.setProperty('--timeline-canvas-width', `${naturalWidth}px`);
      viewport.style.setProperty('--timeline-year-width', `${yearSpacing}px`);
      viewport.style.setProperty('--timeline-edge-padding', `${timelineStart}px`);
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
      if (window.matchMedia(`(max-width: ${MOBILE_BREAKPOINT}px)`).matches) {
        stickyYears.classList.remove('is-scroll-hidden');
        return;
      }

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
      dialog?.classList.toggle(
        'nu-journey-dialog--text-only',
        !dialogContent.querySelector('.nu-journey-dialog__image'),
      );
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
