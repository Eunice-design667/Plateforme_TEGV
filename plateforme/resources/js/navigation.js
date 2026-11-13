/**
 * Leaving the app easily (testers' feedback):
 * - moving between the main sections replaces the page in the history instead of piling
 *   it up, so "back" from any section returns to the home screen, and "back" again leaves;
 * - "Quitter l'application" closes the installed app when the phone allows it, otherwise
 *   it goes back to the home screen and explains how to close;
 * - without "Rester connecté", reopening the app (new browsing session) logs out and
 *   shows the first page again.
 */
const OPEN_KEY = 'tegv-session-ouverte';

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

function setupSectionNavigation() {
    const homePath = new URL(document.body.dataset.homeUrl ?? '/accueil', location.href).pathname;

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[data-nav]');

        if (!link || event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.button !== 0) {
            return;
        }

        const target = new URL(link.href, location.href);

        if (target.origin !== location.origin || target.pathname === location.pathname) {
            return;
        }

        // From the home screen, keep it in the history so "back" returns to it.
        if (location.pathname === homePath && target.pathname !== homePath) {
            return;
        }

        event.preventDefault();
        location.replace(target.href);
    });
}

function setupQuit() {
    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-quit]')) {
            return;
        }

        window.close();

        // Browsers only let a page close a window it opened itself.
        setTimeout(() => {
            const standalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone;
            alert(standalone
                ? 'Pour quitter, utilisez le bouton Accueil ou le geste de retour de votre téléphone.'
                : 'Pour quitter, fermez simplement cet onglet.');
        }, 300);
    });
}

export default function setupNavigation() {
    document.querySelectorAll('form[data-session-start]').forEach((form) =>
        form.addEventListener('submit', () => sessionMark('1')),
    );

    if (handleEphemeralSession()) {
        return;
    }

    setupSectionNavigation();
    setupQuit();
}
