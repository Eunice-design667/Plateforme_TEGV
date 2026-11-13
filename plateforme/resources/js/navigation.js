/**
 * Phone "back" button (testers' feedback):
 * - on any page other than the home screen, "back" first returns to the home screen;
 * - on the home screen, "back" leaves the app (it skips the pages seen before);
 * - moving between the main sections does not pile pages up in the history.
 * Without "Rester connecté", reopening the app (new browsing session) logs out and
 * shows the first page again.
 */
const OPEN_KEY = 'tegv-session-ouverte';
const GUARD = 'tegv-retour-accueil';

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

/**
 * Small per-tab memory (sessionStorage), safe when storage is blocked.
 */
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

const DEPTH = 'tegv-profondeur'; // how many pages away from the home screen the current page is
const LEAVING = 'tegv-sortie-en-cours';
const GOING_HOME = 'tegv-retour-en-cours';
const NAV_TARGET = 'tegv-rubrique-suivante';

/**
 * Reached through "back" (not by a click): keep going back until the app is left.
 * At the very first entry history.back() does nothing, so the page shows again after a moment.
 */
function continueLeaving() {
    memory(LEAVING, '1');
    document.body.style.visibility = 'hidden';
    history.back();
    setTimeout(() => {
        memory(LEAVING, null);
        document.body.style.visibility = '';
    }, 800);
}

/**
 * Every page other than home holds two history entries ("base" + "page"). From a page's
 * base entry, the home entry sits 2 × depth − 1 steps back.
 */
function goHome(depth) {
    if (!depth) {
        location.replace(document.body.dataset.homeUrl ?? '/accueil');

        return;
    }

    memory(GOING_HOME, '1');
    history.go(-(2 * depth - 1));
}

function setupBackButton() {
    const onHome = location.pathname === homePath();
    const navigation = performance.getEntriesByType('navigation')[0];

    window.addEventListener('pageshow', (event) => {
        const cameBack = event.persisted || navigation?.type === 'back_forward';

        if (!cameBack) {
            memory(LEAVING, null);

            return;
        }

        if (onHome && memory(GOING_HOME)) {
            memory(GOING_HOME, null);
            memory(DEPTH, '0');

            return;
        }

        continueLeaving();
    });

    if (onHome) {
        if (navigation?.type !== 'back_forward') {
            memory(DEPTH, '0');
        }

        return;
    }

    let depth = history.state?.depth ?? null;

    if (history.state?.[GUARD] !== 'page' && navigation?.type !== 'back_forward') {
        const replacing = memory(NAV_TARGET + '-profondeur');
        const previous = memory(DEPTH);
        depth = replacing !== null ? Number(replacing) : previous !== null ? Number(previous) + 1 : null;
        memory(NAV_TARGET + '-profondeur', null);

        history.replaceState({ ...history.state, [GUARD]: 'base', depth }, '');
        history.pushState({ [GUARD]: 'page', depth }, '');
    }

    memory(DEPTH, depth === null ? null : String(depth));

    window.addEventListener('popstate', () => {
        if (history.state?.[GUARD] !== 'base') {
            return;
        }

        const target = memory(NAV_TARGET);

        if (memory(LEAVING)) {
            history.back();
        } else if (target) {
            memory(NAV_TARGET, null);
            location.replace(target);
        } else {
            goHome(history.state.depth);
        }
    });
}

function setupSectionNavigation() {
    const home = homePath();

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[data-nav]');

        if (!link || event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.button !== 0) {
            return;
        }

        const target = new URL(link.href, location.href);

        if (target.origin !== location.origin || target.pathname === location.pathname || location.pathname === home) {
            return;
        }

        event.preventDefault();
        const depth = history.state?.depth;

        if (target.pathname === home) {
            history.state?.[GUARD] === 'page' ? (memory(GOING_HOME, '1'), history.go(-(2 * (depth ?? 1)))) : location.replace(target.href);

            return;
        }

        // Replace this section by the next one at the same place in the history.
        if (history.state?.[GUARD] === 'page') {
            memory(NAV_TARGET, target.href);
            memory(NAV_TARGET + '-profondeur', depth ?? null);
            history.back();
        } else {
            location.replace(target.href);
        }
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
        setupSectionNavigation();
    }
}
