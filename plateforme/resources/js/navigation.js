/**
 * Phone "back" button (testers' feedback): "back" returns to the home screen, then
 * "back" again leaves the app.
 *
 * The history is kept to two entries, home + the current page: from home a link adds
 * an entry; from any other page, links and searches REPLACE the current entry. Phones
 * ignore history entries a page adds by itself (pushState without a tap), so nothing
 * relies on them. A form sent from a page still adds an entry: when "back" lands on such
 * an intermediate page, it keeps going back to the home screen by itself.
 *
 * Without "Rester connecté", reopening the app (new browsing session) logs out and
 * shows the first page again.
 */
const OPEN_KEY = 'tegv-session-ouverte';
const LAST_PAGE = 'tegv-derniere-page';
const EXITING = 'tegv-sortie';

function sessionMark(value) {
    try {
        if (value === undefined) {
            return sessionStorage.getItem(OPEN_KEY);
        }

        sessionStorage.setItem(OPEN_KEY, value);
    } catch {
        return '1';
    }
}

function logoutAndRestart() {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;

    fetch('/deconnexion', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': token ?? '', Accept: 'text/html' },
        credentials: 'same-origin',
    }).finally(() => location.replace('/'));
}

function handleEphemeralSession() {
    const body = document.body;

    if (body.dataset.ephemeral === undefined || sessionMark()) {
        if (body.dataset.homeUrl) {
            sessionMark('1');
        }

        return false;
    }

    // The app was reopened: the member did not choose "Rester connecté".
    body.style.visibility = 'hidden';
    logoutAndRestart();

    return true;
}

function homePath() {
    return new URL(document.body.dataset.homeUrl ?? '/accueil', location.href).pathname;
}

function memory(key, value) {
    try {
        if (value === undefined) {
            return sessionStorage.getItem(key);
        }

        value === null ? sessionStorage.removeItem(key) : sessionStorage.setItem(key, String(value));
    } catch {
        return null;
    }
}

/**
 * Keep going back by itself; at the first entry history.back() does nothing, so the
 * page shows again after a moment.
 */
function keepGoingBack() {
    document.body.style.visibility = 'hidden';
    history.back();
    setTimeout(() => {
        memory(EXITING, null);
        document.body.style.visibility = '';
    }, 900);
}

function setupBackButton() {
    const home = homePath();
    const onHome = location.pathname === home;

    const arrived = (cameBack) => {
        if (!cameBack) {
            memory(EXITING, null);

            return;
        }

        // "Back" pressed on the home screen = leave the app: go through every older entry.
        if (memory(EXITING) || memory(LAST_PAGE) === home) {
            memory(EXITING, '1');
            keepGoingBack();

            return;
        }

        // "Back" pressed on another page: continue until the home screen, and stop there.
        if (!onHome) {
            keepGoingBack();
        }
    };

    window.addEventListener('pagehide', () => memory(LAST_PAGE, location.pathname));

    // A page loaded through "back" (the pageshow event may already be past when this runs).
    arrived(performance.getEntriesByType('navigation')[0]?.type === 'back_forward');

    // A page restored from the browser's memory (back-forward cache).
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            arrived(true);
        }
    });
}

function isInternalLink(link, event) {
    if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
        return false;
    }

    if (link.target && link.target !== '_self') {
        return false;
    }

    if (link.hasAttribute('download') || link.getAttribute('href')?.startsWith('#')) {
        return false;
    }

    const url = new URL(link.href, location.href);

    return url.origin === location.origin && !/\/(deconnexion|visiteurs\/export|admin\/qr-code\/telecharger)/.test(url.pathname);
}

function setupLinearHistory() {
    const home = homePath();

    if (location.pathname === home) {
        return;
    }

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[href]');

        if (!isInternalLink(link, event)) {
            return;
        }

        event.preventDefault();
        location.replace(link.href);
    });

    // Searches and filters (GET forms) replace the page too.
    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || (form.method || 'get').toLowerCase() !== 'get' || event.defaultPrevented) {
            return;
        }

        event.preventDefault();
        const url = new URL(form.action || location.href, location.href);
        url.search = new URLSearchParams(new FormData(form)).toString();
        location.replace(url.href);
    });
}

export default function setupNavigation() {
    document.querySelectorAll('form[data-session-start]').forEach((form) =>
        form.addEventListener('submit', () => sessionMark('1')),
    );

    if (handleEphemeralSession()) {
        return;
    }

    if (document.body.dataset.homeUrl) {
        setupBackButton();
        setupLinearHistory();
    }
}
