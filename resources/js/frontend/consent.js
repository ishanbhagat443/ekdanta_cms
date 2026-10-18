/**
 * The cookie bar (partials/cookie-bar). "Allow all" or "Only necessary" is
 * kept in a first-party cookie for 180 days; the server reads it
 * (tracking_allowed) to add analytics and ad tags. Choosing changes which
 * tags the page should have, so the page reloads when the server adds any
 * (data-reload). Scripts an editor marks <script type="text/plain"
 * data-tracking> run only after "Allow all". "Cookie settings" anywhere
 * ([data-cookie-settings]) brings the bar back. It never takes or traps focus.
 */
const NAME = 'cookie_consent';

export const choice = (doc = document) => (doc.cookie.match(/(?:^|;\s*)cookie_consent=(all|necessary)/) || [])[1] || '';

/** Turn the page's data-tracking placeholders into running scripts. */
export function activate(doc = document) {
    doc.querySelectorAll('script[type="text/plain"][data-tracking]').forEach((placeholder) => {
        const script = doc.createElement('script');
        [...placeholder.attributes].forEach((a) => { if (a.name !== 'type') script.setAttribute(a.name, a.value); });
        script.text = placeholder.text;
        placeholder.replaceWith(script);
    });
}

export default function initConsent(doc = document, win = window) {
    if (choice(doc) === 'all') activate(doc);
    const bar = doc.querySelector('[data-cookie-bar]');
    if (!bar) return;
    let opener = null;

    bar.addEventListener('click', (e) => {
        const button = e.target.closest('[data-cookie-choice]');
        if (!button) return;
        const before = choice(doc);
        const value = button.dataset.cookieChoice;
        doc.cookie = `${NAME}=${value}; Max-Age=${180 * 86400}; Path=/; SameSite=Lax${win.location.protocol === 'https:' ? '; Secure' : ''}`;
        bar.hidden = true;
        opener?.focus();
        opener = null;
        if (value === before) return;
        if (bar.dataset.reload === '1' && (value === 'all' || before === 'all')) win.location.reload();
        else if (value === 'all') activate(doc);
    });

    doc.addEventListener('click', (e) => {
        const settings = e.target.closest('[data-cookie-settings]');
        if (!settings) return;
        opener = settings;
        bar.hidden = false;
        bar.querySelector('[data-cookie-choice]').focus();
    });
}
