/**
 * Screen fit: a pinned stage keeps its design box (.ek-art) at size u and
 * centred; this measures the gaps between that box and the screen (--ek-l,
 * --ek-r, --ek-t, --ek-b, --ek-dy) and anchors each part of the art
 * (data-ek-fit) so it keeps its place against the screen's edges: L left,
 * C centre, R right, S fills the screen, SX its width, W a row widened to
 * both edges, Z an image wall zooming to cover, T a sideways track of
 * full-screen panels; B (data-ek-v) sits on the bottom edge. The CSS that
 * moves them is scss/ekdanta/_fit.scss.
 *
 * The automatic rule sorts parts by where they sit; a section type lists
 * the parts it gets wrong (SectionType::fitParts → data-ek-fit-parts).
 * It replaced the "Ekdanta screen fit, v2" Theme Customizer snippet
 * (docs/screen-fit-v2).
 */
const SPLIT = 0.66; // a desktop part wider than 66 % of the frame keeps its whole section centred
const MEDIA = /^(svg|img|video|canvas|picture)$/i;
const parsed = new WeakMap();

const px = (v) => `${v.toFixed(2)}px`;

function partsOf(comp) {
    const section = comp.closest('[data-ek-fit-parts]');
    if (!section) return {};
    if (!parsed.has(section)) {
        try { parsed.set(section, JSON.parse(section.dataset.ekFitParts)); } catch { parsed.set(section, {}); }
    }
    return parsed.get(section);
}

function unit(comp) {
    const cs = getComputedStyle(comp);
    return parseFloat(cs.getPropertyValue('--u')) || 1; // --u is registered as a length: computed in px
}

/** The anchor a section gives this part by hand, or ''. */
export function override(el, parts) {
    for (const [selector, anchor] of Object.entries(parts)) if (el.matches(selector)) return anchor;
    return '';
}

/**
 * The automatic rule, for one part (design px): a full-width empty picture
 * layer fills (S, SX), a full-width part centres, others go by their middle.
 * Returns [anchor, makes the section centred].
 */
export function anchorOf({ x0, x1, y0, y1, text, media }, Dw, Dh) {
    const wide = x0 <= 2 && x1 >= Dw - 2;
    const tall = y0 <= 2 && y1 >= Dh - 2;
    if (wide && !text && media) return [tall ? 'S' : 'SX', false];
    if (wide) return ['C', false];
    const cx = (x0 + x1) / 2;
    return [cx < Dw * 0.42 ? 'L' : cx > Dw * 0.58 ? 'R' : 'C', x1 - x0 > Dw * SPLIT];
}

// T: inside a desktop panel, parts that start on the left stay at the screen's left and the
// others go right; inside a phone panel, the column stays centred.
function track(el, U, Dw, isD) {
    for (const panel of el.children) {
        for (const k of panel.children) {
            const a = !isD ? 'C' : k.offsetLeft / U < Dw * 0.42 ? 'L' : 'R';
            if (k.getAttribute('data-ek-in') !== a) k.setAttribute('data-ek-in', a);
        }
    }
}

function tag(comp, stage, art) {
    const isD = comp.classList.contains('ek-comp--d');
    const U = unit(comp);
    const Dw = isD ? 1440 : 390;
    const Dh = isD ? 900 : 844;
    const parts = partsOf(comp);
    // The gaps between the design box and the stage (the screen), in screen px.
    const sw = stage.clientWidth;
    const sh = stage.clientHeight;
    const aw = art.offsetWidth;
    const ah = art.offsetHeight;
    const l = art.offsetLeft;
    const t = art.offsetTop;
    const r = sw - l - aw;
    const b = sh - t - ah;
    const s = art.style;
    s.setProperty('--ek-l', px(l));
    s.setProperty('--ek-r', px(r));
    s.setProperty('--ek-t', px(t));
    s.setProperty('--ek-b', px(b));
    s.setProperty('--ek-dy', px((sh - ah) / 2 - t)); // 0 when the box is already centred
    s.setProperty('--ek-cover', Math.max(sw / aw, sh / ah).toFixed(4));

    const found = [];
    let centred = !isD;
    for (const el of art.children) {
        if (/^(SCRIPT|STYLE|TEMPLATE)$/.test(el.tagName)) continue;
        let a = override(el, parts);
        const fixed = !!a;
        const x0 = el.offsetLeft / U;
        const x1 = x0 + el.offsetWidth / U;
        const y0 = el.offsetTop / U;
        const y1 = y0 + el.offsetHeight / U;
        // a part that sits on the frame's bottom edge sits on the screen's bottom edge
        const v = y1 >= Dh - 6 && y0 > Dh * 0.3 ? 'B' : '';
        if (v !== (el.getAttribute('data-ek-v') || '')) {
            if (v) el.setAttribute('data-ek-v', v);
            else el.removeAttribute('data-ek-v');
        }
        if (!a) {
            const media = [...el.children].every((k) => MEDIA.test(k.tagName));
            let wide;
            [a, wide] = anchorOf({ x0, x1, y0, y1, text: !!el.textContent.trim(), media }, Dw, Dh);
            if (wide) centred = true;
        }
        // design widths, measured once before the CSS widens anything
        if (a === 'W' && !el.style.getPropertyValue('--ek-w')) el.style.setProperty('--ek-w', Math.round(el.offsetWidth / U));
        if (a === 'T' && !el.style.getPropertyValue('--ek-pw') && el.firstElementChild) el.style.setProperty('--ek-pw', Math.round(el.firstElementChild.offsetWidth / U));
        found.push([el, a, fixed, v]);
    }

    const dy = (sh - ah) / 2 - t;
    // how far the CSS moves a part, in screen px
    const moved = (p) => [p[1] === 'L' || p[1] === 'W' ? -l : p[1] === 'R' ? r : p[1] === 'S' || p[1] === 'SX' ? 0 : (r - l) / 2, p[3] === 'B' ? b : dy];
    let beam = null;
    let rig = null;
    let copy = null;
    for (const p of found) {
        if (centred && !p[2] && (p[1] === 'L' || p[1] === 'R')) p[1] = 'C';
        if (p[0].getAttribute('data-ek-fit') !== p[1]) p[0].setAttribute('data-ek-fit', p[1]);
        if (p[1] === 'T') track(p[0], U, Dw, isD);
        if (p[0].getAttribute('data-m') === 'beam') beam = p;
        else if (p[0].querySelector('[data-m="rig"]')) rig = p;
        else if (!copy && p[0].textContent.trim()) copy = p;
    }
    // Home hero: the beam fills the screen; its tip follows the torch rig, and the lit
    // copy of the headline inside it stays over the headline.
    if (beam) {
        const h = rig ? moved(rig) : [0, 0];
        const c = copy ? moved(copy) : [0, 0];
        s.setProperty('--ek-hx', px(l + h[0]));
        s.setProperty('--ek-hy', px(t + h[1]));
        s.setProperty('--ek-cx', px(l + c[0]));
        s.setProperty('--ek-cy', px(t + c[1]));
    }
}

let timer = 0;
const watched = new WeakSet();

function run() {
    timer = 0;
    document.querySelectorAll('.ek-comp').forEach((comp) => {
        const art = comp.querySelector('.ek-stage > .ek-art');
        if (!art) return;
        if (!watched.has(art)) { // re-sort if a section adds or replaces its parts
            watched.add(art);
            new MutationObserver(later).observe(art, { childList: true });
        }
        if (art.offsetWidth) tag(comp, art.parentElement, art); // hidden stages are sorted once they show
    });
}

function later() {
    if (!timer) timer = setTimeout(run, 60);
}

export default function initFit() {
    ['resize', 'orientationchange', 'load', 'pageshow'].forEach((e) => addEventListener(e, later));
    window.visualViewport?.addEventListener('resize', later);
    document.fonts?.ready.then(later);
    run(); // at once, before the first frame where it can: a later move counts as layout shift
}
