/**
 * Weak or lost connection (test plan P-05):
 * - a banner tells the member when the phone is offline, and when it is back;
 * - forms marked with data-draft are saved on the phone while typing and restored
 *   when the member comes back, so nothing is lost if sending fails;
 * - sending while offline is stopped with a clear message instead of a browser error.
 * Passwords, files, the CSRF token and honeypot fields are never stored.
 */
const PREFIX = 'tegv-brouillon:';
const PENDING = 'tegv-brouillons-envoyes';
const SKIPPED_TYPES = ['password', 'file', 'hidden', 'submit', 'button'];

const storage = {
    get(key) {
        try {
            return JSON.parse(localStorage.getItem(key) ?? 'null');
        } catch {
            return null;
        }
    },
    set(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch {
            // Private browsing or full storage: drafts are a convenience only.
        }
    },
    remove(key) {
        try {
            localStorage.removeItem(key);
        } catch {
            // ignore
        }
    },
};

function savableFields(form) {
    return [...form.elements].filter(
        (field) => field.name && !field.name.endsWith('[]') && !SKIPPED_TYPES.includes(field.type) && field.name !== 'website',
    );
}

function snapshot(form) {
    const values = {};

    for (const field of savableFields(form)) {
        if (field.type === 'radio') {
            if (field.checked) {
                values[field.name] = field.value;
            }
        } else if (field.type === 'checkbox') {
            values[field.name] = field.checked;
        } else {
            values[field.name] = field.value;
        }
    }

    return values;
}

function hasContent(values) {
    return Object.values(values).some((value) => typeof value === 'string' && value.trim() !== '');
}

function restore(form, values) {
    let restored = false;

    for (const field of savableFields(form)) {
        if (!(field.name in values)) {
            continue;
        }

        const value = values[field.name];

        if (field.type === 'radio') {
            if (field.value === value && !field.checked) {
                field.checked = true;
                field.dispatchEvent(new Event('change', { bubbles: true }));
            }
        } else if (field.type === 'checkbox') {
            if (field.checked !== value) {
                field.checked = value;
                field.dispatchEvent(new Event('change', { bubbles: true }));
            }
        } else if (field.value.trim() === '' && typeof value === 'string' && value !== '') {
            // Only fill empty fields: what the server sends back (old values) wins.
            field.value = value;
            field.dispatchEvent(new Event('input', { bubbles: true }));
            restored = true;
        }
    }

    return restored;
}

export function toast(message, tone = 'info') {
    const colors = {
        info: 'background:#1f2a63;color:#fff',
        warning: 'background:#c41c14;color:#fff',
        success: 'background:#f5c22a;color:#151c45',
    };
    let box = document.getElementById('tegv-toast');

    if (!box) {
        box = document.createElement('div');
        box.id = 'tegv-toast';
        box.setAttribute('role', 'status');
        box.setAttribute('aria-live', 'polite');
        box.style.cssText =
            'position:fixed;left:16px;right:16px;margin:0 auto;width:fit-content;bottom:calc(84px + env(safe-area-inset-bottom));z-index:80;' +
            'max-width:28rem;text-align:center;padding:12px 18px;border-radius:16px;font:600 14px/1.4 Montserrat,system-ui,sans-serif;' +
            'box-shadow:0 12px 30px -10px rgb(0 0 0 / .45);transition:opacity .3s';
        document.body.appendChild(box);
    }

    box.style.cssText += ';' + colors[tone];
    box.textContent = message;
    box.style.opacity = '1';
    clearTimeout(box.hideTimer);

    if (tone !== 'warning' || navigator.onLine) {
        box.hideTimer = setTimeout(() => (box.style.opacity = '0'), 5000);
    }
}

function setupDraft(form) {
    const key = PREFIX + form.dataset.draft;
    const saved = storage.get(key);

    if (saved && restore(form, saved)) {
        toast('Votre saisie précédente a été restaurée.', 'success');
    }

    const save = () => {
        const values = snapshot(form);

        if (hasContent(values)) {
            storage.set(key, values);
        } else {
            storage.remove(key);
        }
    };

    form.addEventListener('input', save);
    form.addEventListener('change', save);

    form.addEventListener('submit', (event) => {
        save();

        if (!navigator.onLine) {
            event.preventDefault();
            event.stopImmediatePropagation();
            toast('Pas de connexion : votre saisie est gardée sur ce téléphone. Réessayez dès que le réseau revient.', 'warning');

            return;
        }

        // Cleared on the next page if the form is not shown again (i.e. sending worked).
        const pending = storage.get(PENDING) ?? [];
        storage.set(PENDING, [...new Set([...pending, form.dataset.draft])]);
    }, true);
}

function clearSentDrafts() {
    const pending = storage.get(PENDING) ?? [];
    const stillShown = new Set([...document.querySelectorAll('form[data-draft]')].map((form) => form.dataset.draft));
    const hasErrors = document.querySelector('[data-has-errors]') !== null;

    for (const draft of pending) {
        if (!stillShown.has(draft) || !hasErrors) {
            storage.remove(PREFIX + draft);
        }
    }

    storage.remove(PENDING);
}

export default function setupOfflineSupport() {
    clearSentDrafts();
    document.querySelectorAll('form[data-draft]').forEach(setupDraft);

    window.addEventListener('offline', () =>
        toast('Vous êtes hors connexion. Vos saisies sont gardées sur ce téléphone.', 'warning'),
    );
    window.addEventListener('online', () => toast('Connexion rétablie. Vous pouvez envoyer.', 'success'));

    if (!navigator.onLine) {
        toast('Vous êtes hors connexion. Vos saisies sont gardées sur ce téléphone.', 'warning');
    }
}
