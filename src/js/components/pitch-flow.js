const CLOSE_DURATION = 360;
const FILE_SUCCESS_DURATION = 900;
const TOTAL_STEPS = 6;
const PROGRESS_DOTS = 5;
const STEP_TO_PROGRESS = [1, 2, 2, 3, 4, 5];
const MOBILE_PATTERN = /^\+?[\d\s()-]{10,20}$/;
const OTP_PATTERN = /^\d{4}$/;
const MOBILE_VIEWPORT = '(max-width: 550px)';
const RETURN_URL_KEY = 'nuventuresPitchReturnUrl';
const MAX_PITCH_FILE_SIZE = 10 * 1024 * 1024;
const PITCH_ANALYSIS_TIMEOUT = 90000;
const API_TO_FORM_FIELD = {
  company_name: 'companyName',
  founder_count: 'founderCount',
  company_website: 'websiteUrl',
  what_are_you_building: 'building',
  problem_and_customer: 'problem',
  raising_and_unlock: 'raise',
  hard_to_copy: 'moat',
};
const FORM_TO_API_FIELD = Object.fromEntries(
  Object.entries(API_TO_FORM_FIELD).map(([apiField, formField]) => [formField, apiField])
);

const isValidWebsite = (value) => {
  const displayValue = value.trim();
  const validationValue = /^https?:\/\//i.test(displayValue)
    ? displayValue
    : `https://${displayValue}`;

  try {
    const url = new URL(validationValue);
    return ['http:', 'https:'].includes(url.protocol) && url.hostname.includes('.');
  } catch {
    return false;
  }
};

export function initPitchFlow() {
  document.querySelectorAll('[data-pitch-flow]').forEach((flow) => {
    const panel = flow.querySelector('.pitch-flow__panel');
    const backButton = flow.querySelector('[data-pitch-back]');
    const closeButtons = flow.querySelectorAll('[data-pitch-close]');
    const steps = [...flow.querySelectorAll('[data-pitch-step]')];
    const progressDots = [...flow.querySelectorAll('.pitch-flow__progress-dot')];
    const status = flow.querySelector('.pitch-flow__status');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const forms = [...flow.querySelectorAll('[data-pitch-form]')];
    const nameOutputs = [...flow.querySelectorAll('[data-pitch-name]')];
    const manualButton = flow.querySelector('[data-pitch-manual]');
    const uploadChoices = [...flow.querySelectorAll('[data-pitch-upload-choice]')];
    const manualWizard = flow.querySelector('[data-pitch-manual-wizard]');
    const manualQuestions = [...flow.querySelectorAll('[data-pitch-manual-question]')];
    const manualProgressDots = [...flow.querySelectorAll('.pitch-flow__manual-progress li')];
    const manualIntroNotes = [...flow.querySelectorAll('[data-pitch-manual-intro-note]')];
    const fileInput = flow.querySelector('[data-pitch-file]');
    const fileLabel = flow.querySelector('[data-pitch-file-label]');
    const summaryName = flow.querySelector('[data-pitch-summary-name]');
    const summaryForm = flow.querySelector('[data-pitch-summary-form]');
    const summaryItems = [...flow.querySelectorAll('[data-pitch-summary-item]')];
    const thanksName = flow.querySelector('[data-pitch-thanks-name]');
    const isEmbedded = flow.hasAttribute('data-pitch-embedded');
    const triggers = isEmbedded ? [...document.querySelectorAll('[data-pitch-trigger]')] : [];
    const state = {
      fullName: '',
      mobile: '',
      otp: '',
      pitchDeck: null,
      entryMethod: '',
      manual: {},
      summary: {},
      extractedAnswers: {},
      extraction: null,
    };
    let currentStep = 1;
    let currentManualQuestion = 0;
    let activeManualQuestions = [];
    let manualEntryActive = false;
    let analysisInProgress = false;
    let returnFocus = null;
    let lockedScrollY = 0;
    let focusScrollTimer = 0;

    if (!panel || !backButton || !steps.length) {
      return;
    }

    const syncVisualViewportHeight = () => {
      const viewportHeight = window.visualViewport?.height || window.innerHeight;
      flow.style.setProperty('--pitch-viewport-height', `${Math.round(viewportHeight)}px`);

      if (!isEmbedded && window.matchMedia(MOBILE_VIEWPORT).matches) {
        const activeField = document.activeElement?.closest?.('.pitch-flow__field');
        if (activeField && flow.contains(activeField)) {
          window.clearTimeout(focusScrollTimer);
          focusScrollTimer = window.setTimeout(() => {
            activeField.scrollIntoView({ block: 'center', inline: 'nearest' });
          }, 180);
        }
      }
    };

    const renderStep = (requestedStep) => {
      const nextStep = Math.min(TOTAL_STEPS, Math.max(1, Number(requestedStep) || 1));
      currentStep = nextStep;
      flow.dataset.currentStep = String(currentStep);

      steps.forEach((step) => {
        const isActive = Number(step.dataset.pitchStep) === currentStep;
        step.hidden = !isActive;
        step.classList.toggle('is-active', isActive);
        step.setAttribute('aria-hidden', isActive ? 'false' : 'true');
      });

      backButton.hidden = currentStep === 1;

      progressDots.forEach((dot, index) => {
        const activeDot = STEP_TO_PROGRESS[currentStep - 1] - 1;
        dot.classList.toggle('is-active', index === activeDot);
      });

      if (status) {
        status.textContent = `Step ${currentStep} of ${TOTAL_STEPS}`;
      }

      const activeStep = steps.find((step) => Number(step.dataset.pitchStep) === currentStep);
      const focusTarget = activeStep?.querySelector('input:not([type="file"]), button, [tabindex]');

      if (focusTarget) {
        window.requestAnimationFrame(() => focusTarget.focus({ preventScroll: true }));
      }
    };

    const showError = (form, message = '', isSuccess = false) => {
      const error = form.querySelector('[data-pitch-error]');
      if (error) {
        error.textContent = message;
        error.classList.toggle('is-success', isSuccess);
      }
    };

    const updateNames = () => {
      const firstName = state.fullName.trim().split(/\s+/)[0] || 'there';
      nameOutputs.forEach((output) => {
        output.textContent = firstName;
      });
    };

    const updateSummary = () => {
      const firstName = state.fullName.trim().split(/\s+/)[0] || 'there';

      if (summaryName) {
        summaryName.textContent = firstName;
      }
      if (thanksName) {
        thanksName.textContent = firstName;
      }
    };

    const applyAnswersToSummary = (answers = state.manual) => {
      summaryItems.forEach((item) => {
        const field = item.querySelector('input, textarea');
        const value = item.querySelector('[data-pitch-summary-value]');
        const answer = field ? answers[field.name] : '';

        if (!field || !value || (typeof answer !== 'string' && typeof answer !== 'number')) {
          return;
        }

        const displayValue = String(answer);
        field.value = displayValue;
        field.dataset.committedValue = displayValue;
        value.textContent = displayValue || '—';
      });
    };

    const showManualQuestion = (requestedIndex) => {
      if (!activeManualQuestions.length) {
        return;
      }

      currentManualQuestion = Math.min(
        activeManualQuestions.length - 1,
        Math.max(0, Number(requestedIndex) || 0)
      );

      const activeQuestion = activeManualQuestions[currentManualQuestion];
      manualQuestions.forEach((question) => {
        const isActive = question === activeQuestion;
        question.hidden = !isActive;
        question.classList.toggle('is-active', isActive);
        question.setAttribute('aria-hidden', isActive ? 'false' : 'true');
      });

      manualProgressDots.forEach((dot, index) => {
        dot.hidden = index >= activeManualQuestions.length;
        dot.classList.toggle('is-active', index === currentManualQuestion);
      });

      const activeInput = activeQuestion?.querySelector('input');
      window.requestAnimationFrame(() => activeInput?.focus({ preventScroll: true }));
    };

    const startManualEntry = (fieldNames = Object.values(API_TO_FORM_FIELD)) => {
      const isDeckFollowUp = Boolean(state.pitchDeck);

      manualIntroNotes.forEach((note) => {
        note.textContent = isDeckFollowUp
          ? "Sorry, we couldn't find answers to these questions."
          : 'Tell us a little more about your company.';
      });

      activeManualQuestions = fieldNames
        .map((fieldName) => manualQuestions.find(
          (question) => question.querySelector('input')?.name === fieldName
        ))
        .filter(Boolean);

      if (!activeManualQuestions.length) {
        applyAnswersToSummary();
        updateSummary();
        renderStep(5);
        return;
      }

      currentManualQuestion = 0;
      manualEntryActive = true;
      state.entryMethod = state.pitchDeck ? 'deck-and-manual' : 'manual';
      uploadChoices.forEach((choice) => {
        choice.hidden = true;
      });
      manualWizard.hidden = false;
      flow.classList.add('is-manual-entry');
      showManualQuestion(currentManualQuestion);
    };

    const leaveManualEntry = () => {
      manualEntryActive = false;
      manualWizard.hidden = true;
      uploadChoices.forEach((choice) => {
        choice.hidden = false;
      });
      flow.classList.remove('is-manual-entry');
      manualButton?.focus({ preventScroll: true });
    };

    const mergeExtractedAnswers = (answers = {}) => {
      state.extractedAnswers = Object.fromEntries(
        Object.keys(API_TO_FORM_FIELD).map((apiField) => [apiField, answers[apiField] ?? null])
      );

      Object.entries(API_TO_FORM_FIELD).forEach(([apiField, formField]) => {
        const answer = answers[apiField];
        if (answer === null || answer === undefined) {
          return;
        }

        const value = String(answer);
        state.manual[formField] = value;

        const manualInput = manualQuestions
          .find((question) => question.querySelector('input')?.name === formField)
          ?.querySelector('input');
        if (manualInput) {
          manualInput.value = value;
          manualInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
      });

      applyAnswersToSummary(state.manual);
    };

    const setAnalysisState = (active, message = '') => {
      analysisInProgress = active;
      const uploadStep = fileInput?.closest('[data-pitch-step]');
      const fileControl = fileLabel?.closest('.pitch-flow__file');

      fileInput.disabled = active;
      if (manualButton) {
        manualButton.disabled = active;
      }
      uploadStep?.setAttribute('aria-busy', active ? 'true' : 'false');
      fileControl?.classList.toggle('is-analysing', active);

      if (fileLabel && message) {
        fileLabel.textContent = message;
      }
    };

    const analysePitchDeck = async (selectedFile, form) => {
      if (analysisInProgress) {
        return;
      }

      const endpoint = flow.dataset.pitchAnalysisUrl;
      const pitchSession = flow.dataset.pitchSession;
      if (!endpoint || !pitchSession) {
        showError(form, 'Pitch analysis is temporarily unavailable. Please enter your details manually.');
        return;
      }

      const controller = new AbortController();
      const timeout = window.setTimeout(() => controller.abort(), PITCH_ANALYSIS_TIMEOUT);
      const formData = new FormData();
      formData.append('pitch_deck', selectedFile, selectedFile.name);
      formData.append('pitch_session', pitchSession);

      setAnalysisState(true, 'Uploading and analysing…');
      showError(form, 'This may take up to a minute.', true);

      try {
        const response = await fetch(endpoint, {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          signal: controller.signal,
        });
        const result = await response.json().catch(() => null);

        if (
          !response.ok
          || !result
          || typeof result.complete !== 'boolean'
          || !Array.isArray(result.missing_fields)
        ) {
          throw new Error(result?.message || 'We could not analyse that deck. Please try again.');
        }

        state.extraction = { ...result };
        mergeExtractedAnswers(result);
        setAnalysisState(false, 'Success! Deck analysed');
        showError(form, `${selectedFile.name} was analysed successfully.`, true);

        if (result.complete) {
          updateSummary();
          window.setTimeout(() => renderStep(5), reducedMotion ? 0 : FILE_SUCCESS_DURATION);
          return;
        }

        const missingFormFields = result.missing_fields
          .map((apiField) => API_TO_FORM_FIELD[apiField])
          .filter(Boolean);
        window.setTimeout(
          () => startManualEntry(missingFormFields),
          reducedMotion ? 0 : FILE_SUCCESS_DURATION
        );
      } catch (error) {
        const message = error?.name === 'AbortError'
          ? 'Pitch analysis timed out. Please try again or enter your details manually.'
          : error?.message || 'We could not analyse that deck. Please try again.';

        setAnalysisState(false, 'Select File');
        showError(form, message);
        state.pitchDeck = null;
        state.entryMethod = '';
        fileInput.value = '';
      } finally {
        window.clearTimeout(timeout);
      }
    };

    const lockPageScroll = () => {
      lockedScrollY = window.scrollY;
      document.body.style.position = 'fixed';
      document.body.style.top = `-${lockedScrollY}px`;
      document.body.style.width = '100%';
    };

    const unlockPageScroll = () => {
      document.body.style.position = '';
      document.body.style.top = '';
      document.body.style.width = '';
      window.scrollTo(0, lockedScrollY);
    };

    const openFlow = (trigger = null) => {
      returnFocus = trigger;
      syncVisualViewportHeight();
      flow.classList.remove('is-closing');
      flow.setAttribute('aria-hidden', 'false');

      if (isEmbedded) {
        lockPageScroll();
      }

      window.requestAnimationFrame(() => {
        window.requestAnimationFrame(() => {
          flow.classList.add('is-open');
          panel.focus({ preventScroll: true });
        });
      });
    };

    const validateStep = (form, stepNumber) => {
      const input = form.querySelector('[data-pitch-input]');
      const value = input?.value.trim() || '';

      if (stepNumber === 1) {
        if (value.length < 2) {
          return 'Please enter your full name.';
        }
        state.fullName = value;
        updateNames();
      }

      if (stepNumber === 2) {
        if (!MOBILE_PATTERN.test(value) || value.replace(/\D/g, '').length < 10) {
          return 'Please enter a valid mobile number.';
        }
        state.mobile = value;
      }

      if (stepNumber === 3) {
        if (!OTP_PATTERN.test(value)) {
          return 'Please enter any four digits for the mocked OTP.';
        }
        state.otp = value;
      }

      return '';
    };

    const hasValidStepValue = (stepNumber, value) => {
      const trimmedValue = value.trim();

      if (stepNumber === 1) {
        return trimmedValue.length >= 2;
      }
      if (stepNumber === 2) {
        return MOBILE_PATTERN.test(trimmedValue) && trimmedValue.replace(/\D/g, '').length >= 10;
      }
      if (stepNumber === 3) {
        return OTP_PATTERN.test(trimmedValue);
      }

      return false;
    };

    const setSendReady = (form, ready) => {
      form.querySelector('.pitch-flow__send')?.classList.toggle('is-ready', ready);
    };

    const closeFlow = () => {
      if (!flow.classList.contains('is-open')) {
        return;
      }

      flow.classList.remove('is-open');
      flow.classList.add('is-closing');

      const finishClose = () => {
        if (!isEmbedded) {
          const storedReturnUrl = window.sessionStorage.getItem(RETURN_URL_KEY);
          window.sessionStorage.removeItem(RETURN_URL_KEY);
          window.location.assign(storedReturnUrl || flow.dataset.closeUrl || '/');
          return;
        }

        flow.classList.remove('is-closing');
        flow.setAttribute('aria-hidden', 'true');
        unlockPageScroll();
        returnFocus?.focus({ preventScroll: true });
      };

      if (reducedMotion) {
        finishClose();
        return;
      }

      window.setTimeout(finishClose, CLOSE_DURATION);
    };

    backButton.addEventListener('click', () => {
      if (currentStep === 4 && manualEntryActive) {
        if (currentManualQuestion > 0) {
          showManualQuestion(currentManualQuestion - 1);
        } else {
          leaveManualEntry();
        }
        return;
      }

      renderStep(currentStep - 1);
    });

    forms.forEach((form) => {
      const stepNumber = Number(form.dataset.pitchForm);

      form.addEventListener('input', (event) => {
        const input = event.target.closest('[data-pitch-input]');
        if (!input) {
          return;
        }

        state[input.name] = input.value;
        showError(form);
        setSendReady(form, hasValidStepValue(stepNumber, input.value));
      });

      if (stepNumber <= 3) {
        form.addEventListener('submit', (event) => {
          event.preventDefault();
          const error = validateStep(form, stepNumber);

          showError(form, error);
          if (!error) {
            renderStep(stepNumber + 1);
          }
        });
      }
    });

    fileInput?.addEventListener('change', () => {
      if (analysisInProgress) {
        return;
      }

      const selectedFile = fileInput.files?.[0] || null;
      const form = fileInput.closest('form');

      if (!selectedFile) {
        state.pitchDeck = null;
        state.entryMethod = '';
        if (fileLabel) {
          fileLabel.textContent = 'Select File';
        }
        showError(form, 'Please select a PDF pitch deck.');
        return;
      }

      const isPdf = selectedFile.name.toLowerCase().endsWith('.pdf')
        && (!selectedFile.type || selectedFile.type === 'application/pdf');

      if (!isPdf) {
        fileInput.value = '';
        showError(form, 'Only PDF pitch decks are supported.');
        return;
      }

      if (selectedFile.size <= 0) {
        fileInput.value = '';
        showError(form, 'The selected PDF is empty.');
        return;
      }

      if (selectedFile.size > MAX_PITCH_FILE_SIZE) {
        fileInput.value = '';
        showError(form, 'The PDF must be 10 MB or smaller.');
        return;
      }

      state.pitchDeck = selectedFile;
      state.entryMethod = 'deck';
      manualEntryActive = false;
      flow.classList.remove('is-manual-entry');
      analysePitchDeck(selectedFile, form);
    });

    manualButton?.addEventListener('click', () => {
      startManualEntry();
    });

    manualQuestions.forEach((question) => {
      const input = question.querySelector('input');
      const characterCount = question.querySelector('[data-pitch-character-count]');

      input?.addEventListener('input', () => {
        state.manual[input.name] = input.value;
        showError(question);
        let isReady = Boolean(input.value.trim());

        if (input.type === 'number') {
          isReady = Number(input.value) >= 1;
        } else if (input.type === 'url') {
          isReady = isValidWebsite(input.value);
        }

        setSendReady(question, isReady);
        if (characterCount) {
          characterCount.textContent = String(input.value.length);
        }
      });

      question.addEventListener('submit', (event) => {
        event.preventDefault();
        const value = input?.value.trim() || '';
        let error = '';

        if (!value) {
          error = 'Please answer this question.';
        } else if (input?.type === 'number' && Number(value) < 1) {
          error = 'Please enter at least one founder.';
        } else if (input?.type === 'url' && !isValidWebsite(value)) {
          error = 'Please enter a valid company website or domain.';
        }

        showError(question, error);
        if (error) {
          return;
        }

        state.manual[input.name] = value;

        const apiField = FORM_TO_API_FIELD[input.name];
        if (apiField && state.extraction) {
          const storedValue = apiField === 'founder_count' ? Number(value) : value;
          state.extraction[apiField] = storedValue;
          state.extractedAnswers[apiField] = storedValue;
          state.extraction.missing_fields = state.extraction.missing_fields
            .filter((missingField) => missingField !== apiField);
          state.extraction.complete = state.extraction.missing_fields.length === 0;
        }

        if (currentManualQuestion < activeManualQuestions.length - 1) {
          showManualQuestion(currentManualQuestion + 1);
          return;
        }

        applyAnswersToSummary();
        updateSummary();
        renderStep(5);
      });
    });

    summaryForm?.addEventListener('submit', (event) => {
      event.preventDefault();
      summaryForm.querySelectorAll('[data-pitch-edit-cancel]').forEach((cancelButton) => {
        if (cancelButton.closest('[data-pitch-summary-item]')?.classList.contains('is-editing')) {
          cancelButton.click();
        }
      });
      state.summary = Object.fromEntries(new FormData(summaryForm).entries());
      updateSummary();
      renderStep(6);
    });

    summaryItems.forEach((item) => {
      const editButton = item.querySelector('[data-pitch-edit]');
      const value = item.querySelector('[data-pitch-summary-value]');
      const field = item.querySelector('input, textarea');
      const actions = document.createElement('div');
      const saveButton = document.createElement('button');
      const cancelButton = document.createElement('button');
      let initialValue = '';

      if (!editButton || !value || !field) {
        return;
      }

      actions.className = 'pitch-flow__edit-actions';
      actions.hidden = true;
      saveButton.type = 'button';
      saveButton.className = 'pitch-flow__edit-save';
      saveButton.dataset.pitchEditSave = '';
      saveButton.textContent = 'Save';
      cancelButton.type = 'button';
      cancelButton.className = 'pitch-flow__edit-cancel';
      cancelButton.dataset.pitchEditCancel = '';
      cancelButton.textContent = 'Cancel';
      actions.append(saveButton, cancelButton);
      item.append(actions);
      field.dataset.committedValue = field.value;

      const finishEditing = (saveChanges) => {
        if (saveChanges) {
          const committedValue = field.value.trim();
          field.value = committedValue;
          field.dataset.committedValue = committedValue;
          value.textContent = committedValue || '—';
        } else {
          field.value = initialValue;
        }

        field.hidden = true;
        value.hidden = false;
        editButton.hidden = false;
        actions.hidden = true;
        item.classList.remove('is-editing');
      };

      editButton.addEventListener('click', () => {
        initialValue = field.dataset.committedValue ?? field.value;
        field.value = initialValue;
        value.hidden = true;
        field.hidden = false;
        editButton.hidden = true;
        actions.hidden = false;
        item.classList.add('is-editing');
        field.focus({ preventScroll: true });

        if (field instanceof HTMLInputElement) {
          field.select();
        }
      });

      saveButton.addEventListener('click', () => {
        finishEditing(true);
        editButton.focus({ preventScroll: true });
      });

      cancelButton.addEventListener('click', () => {
        finishEditing(false);
        editButton.focus({ preventScroll: true });
      });

      field.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
          event.preventDefault();
          finishEditing(false);
          editButton.focus({ preventScroll: true });
        }

        if (event.key === 'Enter' && field instanceof HTMLInputElement) {
          event.preventDefault();
        }
      });
    });

    closeButtons.forEach((button) => {
      button.addEventListener('click', closeFlow);
    });

    window.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && flow.classList.contains('is-open')) {
        closeFlow();
      }
    });

    flow.addEventListener('pitch:go-to-step', (event) => {
      renderStep(event.detail?.step);
    });

    renderStep(flow.dataset.currentStep);
    syncVisualViewportHeight();
    flow.addEventListener('focusin', (event) => {
      if (
        !isEmbedded
        && window.matchMedia(MOBILE_VIEWPORT).matches
        && event.target.matches('input, textarea')
      ) {
        window.clearTimeout(focusScrollTimer);
        focusScrollTimer = window.setTimeout(() => {
          event.target.closest('.pitch-flow__field')?.scrollIntoView({
            block: 'center',
            inline: 'nearest',
          });
        }, 280);
      }
    });
    window.addEventListener('resize', syncVisualViewportHeight, { passive: true });
    window.visualViewport?.addEventListener('resize', syncVisualViewportHeight, { passive: true });

    if (isEmbedded) {
      triggers.forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
          if (window.matchMedia(MOBILE_VIEWPORT).matches) {
            window.sessionStorage.setItem(RETURN_URL_KEY, window.location.href);
            return;
          }

          event.preventDefault();
          openFlow(trigger);
        });
      });
    } else {
      openFlow();
    }
  });
}
