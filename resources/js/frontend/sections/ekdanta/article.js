/**
 * Ekdanta · Article: the reading rail follows the heading in view (its
 * contents link gets aria-current, the bar moves: --act) and counts down
 * the minutes left as a reading line 60 % down the screen passes the text.
 * On phones and tablets a slim bar ([data-now]) shows the part being read,
 * the minutes left and how far along, while the text is on screen.
 * Copy-link buttons ([data-copy-link]) copy the address and say so.
 * Markup: [data-article] (data-minutes, data-left "{minutes} min left",
 * data-done, data-read "{percent}% read") > [data-toc] links,
 * [data-article-text], [data-ring], [data-left], [data-read], [data-now].
 */

/** Share of the text read: the reading line's place between its top and bottom. */
export function progress(top, height, viewport) {
    return Math.max(0, Math.min(1, (viewport * 0.6 - top) / Math.max(1, height)));
}

/** Index of the last heading that has reached the line (-1: none yet). */
export function active(tops, line) {
    let at = -1;
    tops.forEach((t, i) => { if (t <= line) at = i; });
    return at;
}

export function minutesLeft(total, p) {
    return Math.max(0, Math.ceil(total * (1 - p) - 0.001));
}

function initRail(root) {
    const text = root.querySelector('[data-article-text]');
    const toc = root.querySelector('[data-toc]');
    const links = toc ? [...toc.querySelectorAll('a[href^="#"]')] : [];
    const heads = links.map((a) => document.getElementById(decodeURIComponent(a.hash.slice(1))));
    const ring = root.querySelector('[data-ring]');
    const left = root.querySelector('[data-left]');
    const read = root.querySelector('[data-read]');
    const now = root.querySelector('[data-now]');
    const total = Number(root.dataset.minutes) || 1;
    const C = ring ? Number(ring.getAttribute('stroke-dasharray')) : 0;
    let current = null;

    const draw = () => {
        const vh = innerHeight;
        // The design's line: 260 of 900 px down the screen.
        const at = active(heads.map((h) => (h ? h.getBoundingClientRect().top : Infinity)), vh * 0.29);
        if (at !== current) {
            current = at;
            links.forEach((a, i) => (i === at ? a.setAttribute('aria-current', 'true') : a.removeAttribute('aria-current')));
            toc?.style.setProperty('--act', String(at));
        }
        if (!text) return;
        const box = text.getBoundingClientRect();
        const p = progress(box.top, box.height, vh);
        ring?.setAttribute('stroke-dashoffset', (C * (1 - p)).toFixed(2));
        const leftText = p >= 1 ? root.dataset.done : root.dataset.left.replace('{minutes}', minutesLeft(total, p));
        if (left) left.textContent = leftText;
        if (read) read.textContent = root.dataset.read.replace('{percent}', Math.round(p * 100));
        if (now) {
            const link = links[Math.max(0, at)];
            now.querySelector('[data-now-num]').textContent = link?.querySelector('.ek-ab__num')?.textContent || String(Math.max(0, at) + 1).padStart(2, '0');
            now.querySelector('[data-now-title]').textContent = link ? [...link.childNodes].filter((n) => n.nodeType === 3).map((n) => n.textContent).join('').trim() : '';
            now.querySelector('[data-now-left]').textContent = leftText;
            now.querySelector('[data-now-bar]').style.transform = `scaleX(${p.toFixed(3)})`;
            now.classList.toggle('is-on', box.top < -60 && box.bottom > vh * 0.4);
        }
    };

    let queued = false;
    const queue = () => {
        if (queued) return;
        queued = true;
        requestAnimationFrame(() => { queued = false; draw(); });
    };
    addEventListener('scroll', queue, { passive: true });
    addEventListener('resize', queue);
    draw();
}

function initCopy(button) {
    const label = button.getAttribute('aria-label');
    let timer;
    button.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(button.dataset.copyLink || location.href);
        } catch {
            return; // no clipboard (insecure page, or refused): say nothing
        }
        const status = button.closest('[data-share]')?.querySelector('[data-copy-status]');
        button.setAttribute('aria-label', button.dataset.copied);
        if (status) status.textContent = button.dataset.copied;
        clearTimeout(timer);
        timer = setTimeout(() => {
            button.setAttribute('aria-label', label);
            if (status) status.textContent = '';
        }, 1800);
    });
}

export default function initArticles() {
    document.querySelectorAll('[data-article]').forEach(initRail);
    document.querySelectorAll('[data-copy-link]').forEach(initCopy);
}
