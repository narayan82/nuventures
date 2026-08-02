const STORAGE_KEY = 'nuventures_cookie_consent';
const VALID_PREFERENCES = new Set(['all', 'essential']);

export function initCookieConsent() {
  const banner = document.querySelector('[data-cookie-consent]');
  let memoryPreference = null;

  const readPreference = () => {
    try {
      const storedPreference = window.localStorage.getItem(STORAGE_KEY);
      return VALID_PREFERENCES.has(storedPreference) ? storedPreference : memoryPreference;
    } catch {
      return memoryPreference;
    }
  };

  const notifyPreference = (preference) => {
    document.documentElement.dataset.cookieConsent = preference || 'unset';
    window.dispatchEvent(new CustomEvent('nuventures:cookie-consent', {
      detail: { preference },
    }));
  };

  const showBanner = () => {
    if (banner) {
      banner.hidden = false;
    }
  };

  const hideBanner = () => {
    if (banner) {
      banner.hidden = true;
    }
  };

  const savePreference = (preference) => {
    if (!VALID_PREFERENCES.has(preference)) {
      return false;
    }

    memoryPreference = preference;
    try {
      window.localStorage.setItem(STORAGE_KEY, preference);
    } catch {
      // The in-memory value keeps the current visit functional.
    }

    hideBanner();
    notifyPreference(preference);
    return true;
  };

  const resetPreference = () => {
    memoryPreference = null;
    try {
      window.localStorage.removeItem(STORAGE_KEY);
    } catch {
      // The banner can still be reset for the current visit.
    }

    notifyPreference(null);
    showBanner();
  };

  const hasOptionalConsent = () => readPreference() === 'all';

  const loadOptionalScript = (source, attributes = {}) => {
    if (!hasOptionalConsent() || !source) {
      return null;
    }

    const script = document.createElement('script');
    script.src = source;
    Object.entries(attributes).forEach(([name, value]) => {
      script.setAttribute(name, String(value));
    });
    document.head.append(script);
    return script;
  };

  window.NuVenturesCookieConsent = {
    getPreference: readPreference,
    hasOptionalConsent,
    loadOptionalScript,
    reset: resetPreference,
    setPreference: savePreference,
  };

  banner?.querySelectorAll('[data-cookie-consent-choice]').forEach((button) => {
    button.addEventListener('click', () => {
      savePreference(button.dataset.cookieConsentChoice);
    });
  });

  const preference = readPreference();
  notifyPreference(preference);

  if (!preference) {
    showBanner();
  }
}
