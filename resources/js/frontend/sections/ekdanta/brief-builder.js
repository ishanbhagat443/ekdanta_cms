/**
 * Ekdanta · Brief Builder: a Forms module form, one step at a time.
 * Next is enabled once a step's required answers are in; the last step
 * shows a summary and a real wa.me link built from the answers (each
 * field's WhatsApp sentence). Tapping it saves the brief first (a
 * keepalive POST, so the save survives the tab switch), then lets the link
 * open WhatsApp, and the card turns to "sent". Without JS the whole form
 * shows and posts normally.
 */
export function message(form) {
    const data = new FormData(form);
    const parts = [form.dataset.waIntro || ''];
    form.querySelectorAll('[data-brief-field]').forEach((field) => {
        const name = field.dataset.briefField;
        const value = [...data.getAll(name), ...data.getAll(`${name}[]`)].map((v) => String(v).trim()).filter(Boolean).join(', ');
        if (value && field.dataset.wa) parts.push(field.dataset.wa.replaceAll('{value}', value));
    });
    return parts.filter(Boolean).join(' ');
}

function answered(field) {
    if (field.querySelector('input[type=checkbox], input[type=radio]')) return !!field.querySelector('input:checked');
    const input = field.querySelector('input, textarea');
    return !input || (input.value.trim() !== '' && input.checkValidity());
}

function init(card) {
    const form = card.querySelector('[data-brief-form]');
    const steps = [...card.querySelectorAll('[data-brief-step]')];
    const back = card.querySelector('[data-brief-back]');
    const next = card.querySelector('[data-brief-next]');
    const send = card.querySelector('[data-brief-send]');
    const sent = card.querySelector('[data-brief-sent]');
    const miles = [...card.querySelectorAll('.ek-brief-mile')];
    let step = 0;

    const valid = () => [...steps[step].querySelectorAll('[data-brief-field]')]
        .every((field) => !(field.hasAttribute('data-required') || field.querySelector('[required]')) || answered(field));

    const render = (focus = false) => {
        steps.forEach((s, i) => s.classList.toggle('is-current', i === step));
        card.style.setProperty('--step', card.classList.contains('is-sent') ? steps.length - 1 : step);
        miles.forEach((m, i) => m.classList.toggle('is-done', i <= step || card.classList.contains('is-sent')));
        card.querySelectorAll('[data-brief-num]').forEach((n) => { n.textContent = String(step + 1); });
        back.setAttribute('aria-disabled', String(step === 0));
        next.style.visibility = step === steps.length - 1 ? 'hidden' : '';
        next.setAttribute('aria-disabled', String(!valid()));
        if (step === steps.length - 1) {
            const values = steps.slice(0, -1).map((s) => [...s.querySelectorAll('input:checked')].map((i) => i.value).join(', ')).filter(Boolean);
            card.querySelector('[data-brief-summary]').textContent = values.join('  ·  ');
            const number = form.dataset.waNumber;
            send.href = number ? `https://wa.me/${number}?text=${encodeURIComponent(message(form))}` : '#';
        }
        if (focus) steps[step].querySelector('input, textarea')?.focus({ preventScroll: true });
    };

    back.addEventListener('click', () => { if (step > 0) { step--; render(true); } });
    next.addEventListener('click', () => {
        if (!valid()) { steps[step].querySelector('input')?.reportValidity?.(); return; }
        step++;
        render(true);
    });
    form.addEventListener('input', () => render());
    form.addEventListener('change', () => render());
    // Enter in a text box moves on instead of posting the whole form.
    form.addEventListener('submit', (e) => { e.preventDefault(); if (step < steps.length - 1) next.click(); else send.click(); });

    send.addEventListener('click', (e) => {
        if (!form.checkValidity()) { e.preventDefault(); form.reportValidity(); return; }
        fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' }, keepalive: true }).catch(() => {});
        if (!form.dataset.waNumber) e.preventDefault();
        setTimeout(() => { card.classList.add('is-sent'); render(); sent.focus({ preventScroll: true }); }, 250);
    });

    card.querySelector('[data-brief-restart]').addEventListener('click', () => {
        form.reset();
        window.gsmFillAutofill?.(form);
        step = 0;
        card.classList.remove('is-sent');
        render(true);
    });

    card.classList.add('is-js');
    render();
}

export default function initBriefBuilders(root = document) {
    root.querySelectorAll('[data-ek-brief]').forEach(init);
}
