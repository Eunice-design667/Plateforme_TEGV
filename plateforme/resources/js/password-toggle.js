/**
 * Adds a "show / hide" eye button to every password field.
 */
const EYE = '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>';
const EYE_OFF = '<path d="M10.6 5.1A10.9 10.9 0 0 1 12 5c6.4 0 10 7 10 7a17.6 17.6 0 0 1-2.6 3.6M6.6 6.6C3.9 8.4 2 12 2 12s3.6 7 10 7a9.7 9.7 0 0 0 5.4-1.6"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2M3 3l18 18"/>';

function icon(paths) {
    return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="size-5" aria-hidden="true">${paths}</svg>`;
}

function enhance(input) {
    if (input.dataset.passwordToggle) {
        return;
    }

    input.dataset.passwordToggle = '1';
    input.classList.add('pr-14');

    const wrapper = document.createElement('div');
    wrapper.className = 'relative';
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'absolute inset-y-0 right-1 flex w-11 items-center justify-center rounded-full text-muted hover:text-navy-800 focus-visible:outline-2 focus-visible:outline-gold-400';
    button.setAttribute('aria-label', 'Afficher le mot de passe');
    button.setAttribute('aria-pressed', 'false');
    button.innerHTML = icon(EYE);

    button.addEventListener('click', () => {
        const visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';
        button.innerHTML = icon(visible ? EYE_OFF : EYE);
        button.setAttribute('aria-label', visible ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
        button.setAttribute('aria-pressed', String(visible));
        input.focus({ preventScroll: true });
    });

    wrapper.appendChild(button);

    // Never leave a password readable when the form is sent.
    input.form?.addEventListener('submit', () => {
        input.type = 'password';
    });
}

export default function setupPasswordToggles() {
    document.querySelectorAll('input[type="password"]').forEach(enhance);
}
