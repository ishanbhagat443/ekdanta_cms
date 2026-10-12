/**
 * Our Story: the beat index (the engine's steps, f) counts the numbers up
 * and pops the logo in with a little overshoot, as the design does. The
 * page is drawn with the final numbers; screen readers get them in text.
 * Markup: [data-story-count] (data-to, data-at = f where counting starts,
 * data-span, data-suffix) and [data-story-logo].
 */
const clamp = (v, a = 0, b = 1) => Math.max(a, Math.min(b, v));
const easeBack = (t) => { const c1 = 1.70158, c3 = c1 + 1; return 1 + c3 * Math.pow(t - 1, 3) + c1 * Math.pow(t - 1, 2); };

export function countAt(f, { to, at, span }) {
    return Math.round(to * clamp((f - at) / span));
}

export default function aboutStory(el, { device }) {
    const root = el.querySelector(`[data-ek-comp="${device}"]`) || el.querySelector('[data-ek-comp="mobile"]') || el;
    const fmt = new Intl.NumberFormat('en-IN');
    const counts = [...root.querySelectorAll('[data-story-count]')].map((e) => ({
        e, to: Number(e.dataset.to) || 0, at: Number(e.dataset.at) || 0, span: Number(e.dataset.span) || 1, suffix: e.dataset.suffix || '',
    }));
    const logo = root.querySelector('[data-story-logo]');

    return {
        update(state) {
            const f = state.f ?? 0;
            counts.forEach((c) => { c.e.textContent = fmt.format(countAt(f, c)) + c.suffix; });
            if (logo) logo.style.transform = `scale(${(0.85 + 0.15 * easeBack(clamp((f - 3.5) / 0.5))).toFixed(3)})`;
        },
    };
}
