// Native Blade forms keep CSRF, browser validation and Laravel redirects.
document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    const input = document.getElementById(button.dataset.passwordToggle);
    if (!input) return;
    button.hidden = false;
    const label = button.getAttribute('aria-label').replace(/^Mostrar /, '');
    button.addEventListener('click', () => {
        const visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(visible));
        button.setAttribute('aria-label', `${visible ? 'Ocultar' : 'Mostrar'} ${label}`);
        button.querySelector('[data-eye]').hidden = visible;
        button.querySelector('[data-eye-off]').hidden = !visible;
    });
});

document.querySelectorAll('form[data-auth-form]').forEach((form) => {
    const button = form.querySelector('[data-loading-text]');
    if (!button) return;
    const label = button.querySelector('[data-submit-label]');
    const original = label.textContent;
    const reset = () => {
        button.disabled = false;
        button.removeAttribute('aria-busy');
        form.removeAttribute('aria-busy');
        label.textContent = original;
        button.querySelector('.auth-loading').hidden = true;
    };
    form.addEventListener('submit', (event) => {
        if (button.disabled) { event.preventDefault(); return; }
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        form.setAttribute('aria-busy', 'true');
        label.textContent = button.dataset.loadingText;
        button.querySelector('.auth-loading').hidden = false;
    });
    window.addEventListener('pageshow', reset);
});

const firstError = document.querySelector('[aria-invalid="true"]');
if (firstError) firstError.focus();
else document.querySelector('[data-error-summary]')?.focus();

// Preserve old recovery bookmarks without retaining the inaccessible 3D form.
if (window.location.pathname === '/login' && window.location.hash === '#forgot') {
    window.location.replace(document.querySelector('[data-password-request]').href);
}
