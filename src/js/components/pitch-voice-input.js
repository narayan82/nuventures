const LISTENING_MESSAGE = 'Listening… release to stop';
const ERROR_DISPLAY_DURATION = 2400;

const joinTranscript = (existingText, transcript) => {
  const base = existingText.trimEnd();
  const addition = transcript.trimStart();

  if (!base) {
    return addition;
  }

  return addition ? `${base} ${addition}` : base;
};

export function initPitchVoiceInput() {
  if (!window.matchMedia('(max-width: 550px)').matches) {
    return;
  }

  const Recognition = window.SpeechRecognition || window.webkitSpeechRecognition;

  document.querySelectorAll('.pitch-voice-button[data-voice-target]').forEach((button) => {
    const targetId = button.dataset.voiceTarget;
    const target = targetId ? document.getElementById(targetId) : null;
    const field = target?.closest('.pitch-flow__field');
    const helper = button.closest('.pitch-flow__voice')?.querySelector('[data-voice-helper]');
    const originalHelperText = helper?.textContent || 'Tap & hold to record';
    let recognition = null;
    let startingText = '';
    let isListening = false;
    let helperResetTimer = 0;
    let pendingErrorMessage = '';
    let targetWasReadOnly = false;

    const setHelperText = (message) => {
      if (helper) {
        helper.textContent = message;
      }
    };

    const restoreHelperText = () => {
      window.clearTimeout(helperResetTimer);
      setHelperText(originalHelperText);
    };

    const showTemporaryMessage = (message) => {
      window.clearTimeout(helperResetTimer);
      setHelperText(message);
      helperResetTimer = window.setTimeout(restoreHelperText, ERROR_DISPLAY_DURATION);
    };

    const setListeningState = (active) => {
      isListening = active;
      button.classList.toggle('is-listening', active);
      button.setAttribute('aria-pressed', active ? 'true' : 'false');
      target?.classList.toggle('is-voice-listening', active);
      field?.classList.toggle('is-voice-listening', active);

      if (active) {
        window.clearTimeout(helperResetTimer);
        setHelperText(LISTENING_MESSAGE);
        targetWasReadOnly = target.readOnly;
        target.readOnly = true;

        try {
          const end = target.value.length;
          target.setSelectionRange(end, end);
        } catch {
          // Number-like inputs may not support selection ranges.
        }

        target.blur();
        window.getSelection()?.removeAllRanges();
        button.focus({ preventScroll: true });
      } else {
        target.readOnly = targetWasReadOnly;
        restoreHelperText();
      }
    };

    const stopRecognition = () => {
      if (!recognition || !isListening) {
        return;
      }

      try {
        recognition.stop();
      } catch {
        setListeningState(false);
      }
    };

    if (!Recognition || !target) {
      button.disabled = true;
      button.classList.add('is-unsupported');
      button.setAttribute('aria-label', 'Voice input is not supported in this browser');
      setHelperText('Voice input is not supported in this browser.');
      return;
    }

    button.addEventListener('pointerdown', (event) => {
      if (isListening || event.button !== 0) {
        return;
      }

      event.preventDefault();
      restoreHelperText();
      startingText = target.value;
      pendingErrorMessage = '';
      recognition = new Recognition();
      recognition.lang = 'en-IN';
      recognition.continuous = true;
      recognition.interimResults = true;

      recognition.addEventListener('result', (resultEvent) => {
        let finalTranscript = '';
        let interimTranscript = '';

        for (let index = 0; index < resultEvent.results.length; index += 1) {
          const transcript = resultEvent.results[index][0]?.transcript || '';
          if (resultEvent.results[index].isFinal) {
            finalTranscript += transcript;
          } else {
            interimTranscript += transcript;
          }
        }

        target.value = joinTranscript(startingText, `${finalTranscript} ${interimTranscript}`);
        target.dispatchEvent(new Event('input', { bubbles: true }));
        target.blur();
        window.getSelection()?.removeAllRanges();
      });

      recognition.addEventListener('error', (errorEvent) => {
        if (errorEvent.error === 'not-allowed' || errorEvent.error === 'service-not-allowed') {
          pendingErrorMessage = 'Microphone permission was denied. Type your answer instead.';
        } else if (errorEvent.error === 'no-speech') {
          pendingErrorMessage = 'No speech was detected. Hold the mic and try again.';
        } else if (errorEvent.error !== 'aborted') {
          pendingErrorMessage = 'Voice input stopped. Type your answer or try again.';
        }
      });

      recognition.addEventListener('end', () => {
        setListeningState(false);
        recognition = null;
        if (pendingErrorMessage) {
          showTemporaryMessage(pendingErrorMessage);
          pendingErrorMessage = '';
        }
      });

      try {
        button.setPointerCapture(event.pointerId);
        recognition.start();
        setListeningState(true);
      } catch {
        recognition = null;
        setListeningState(false);
        showTemporaryMessage('Voice input could not start. Type your answer instead.');
      }
    });

    ['pointerup', 'pointercancel', 'lostpointercapture'].forEach((eventName) => {
      button.addEventListener(eventName, stopRecognition);
    });

    button.addEventListener('contextmenu', (event) => {
      event.preventDefault();
    });
  });
}
