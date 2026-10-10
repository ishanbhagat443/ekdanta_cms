// The motion engine (resources/js/frontend/motion) against the real Anime.js
// in jsdom. Run with `npm run test:js` (anime keeps a timer alive, hence --test-force-exit).
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { JSDOM } from 'jsdom';

const dom = new JSDOM('<!doctype html><html class="js-enabled"><body></body></html>', { pretendToBeVisual: true });
const { window } = dom;
for (const key of ['window', 'document', 'requestAnimationFrame', 'cancelAnimationFrame', 'getComputedStyle',
    'HTMLElement', 'SVGElement', 'Element', 'Node', 'NodeList', 'HTMLCollection', 'CustomEvent', 'Intl']) {
    globalThis[key] ??= window[key];
}
globalThis.window = window;
globalThis.CustomEvent = window.CustomEvent; // Node has its own, which jsdom rejects
globalThis.document = window.document;
globalThis.innerWidth = 1440;
globalThis.innerHeight = 900;
// Everything is on screen, so observers report each target at once.
const observed = [];
globalThis.IntersectionObserver = window.IntersectionObserver = class {
    constructor(callback) { this.callback = callback; }
    observe(target) { observed.push(target); this.callback([{ target, isIntersecting: true }]); }
    unobserve() {}
    disconnect() {}
};
globalThis.ResizeObserver = class { observe() {} };
globalThis.matchMedia = () => ({ matches: false, addEventListener() {} });
globalThis.addEventListener = window.addEventListener.bind(window);
globalThis.location = window.location;

const { Scene, stepState, scaleKeyframes, easeOf } = await import('../../resources/js/frontend/motion/scene.js');
const { animate } = await import('animejs');

function section(html) {
    const el = document.createElement('section');
    el.innerHTML = html;
    document.body.append(el);
    return el;
}

/** A scene with 1 screen px per design px and its top at 0, so update(y) means px = y. */
function scene(el, config, env = {}) {
    const s = new Scene(el, config, { device: 'desktop', reduced: false, u: 1, ...env });
    s.top = 0;
    s.scale = 1;
    s.travel = 1000;
    return s;
}

test('steps hold on each item for the dwell, then ease on', () => {
    const st = { range: [0, 300], dwell: 0.5 };
    assert.equal(stepState(0, st, 4).f, 0);
    assert.equal(stepState(40, st, 4).f, 0, 'still dwelling on item 0');
    const mid = stepState(75, st, 4).f;
    assert.ok(mid > 0 && mid < 1, `easing between items, got ${mid}`);
    assert.equal(stepState(400, st, 4).f, 3, 'clamped to the last item');
    assert.equal(stepState(75, st, 4, true).f, Math.round(mid), 'reduced motion: whole steps only');
    assert.equal(stepState(150, { ...st, ease: 'linear' }, 4).f, 1.5, 'linear: an even glide, no dwell');
});

test('design px scale with the design unit, other values do not', () => {
    assert.deepEqual(scaleKeyframes({ translateY: [40, 0], opacity: [0, 1], rotate: [-8, 0] }, 0.5),
        { translateY: [20, 0], opacity: [0, 1], rotate: [-8, 0] });
    assert.deepEqual(scaleKeyframes({ x: [{ to: 100 }] }, 2), { x: [{ to: 200 }] });
});

test('steps(), cubicBezier() and spring() become real easing functions', () => {
    for (const name of ['steps(4)', 'cubicBezier(0.2,0.8,0.2,1)', 'spring(80,10)', 'spring']) {
        assert.equal(typeof easeOf(name), name.startsWith('spring') ? 'object' : 'function', name);
    }
    assert.equal(easeOf('outCubic'), 'outCubic');

    // The Phase 1 "Springy is linear" bug: halfway through, not at exactly 5.
    const box = { x: 0 };
    const probe = animate(box, { x: 10, ease: easeOf('spring'), autoplay: false });
    probe.seek(probe.duration / 2);
    assert.ok(Math.abs(box.x - 5) > 0.5, `expected a spring curve at 50 %, got ${box.x}`);
});

test('scrub tracks on one property hand over in scroll order', () => {
    const el = section('<h2 data-m="head">Heading</h2>');
    const head = el.querySelector('[data-m="head"]');
    const s = scene(el, {
        tracks: [
            { part: 'head', trigger: 'scrub', keyframes: { opacity: [0, 1] }, range: [-200, 0], ease: 'linear' },
            { part: 'head', trigger: 'scrub', keyframes: { opacity: [1, 0.2] }, range: [400, 600], ease: 'linear' },
        ],
    });
    const at = (px) => { s.update(px); return Number(head.style.opacity); };
    assert.equal(at(-500), 0, 'before its range: the start state');
    assert.equal(at(-100), 0.5);
    assert.equal(at(200), 1, 'between the two tracks: the first one\'s end');
    assert.ok(Math.abs(at(500) - 0.6) < 1e-6);
    assert.ok(Math.abs(at(900) - 0.2) < 1e-6);
    assert.equal(at(-100), 0.5, 'scrolling back up reverses');
});

test('scrub stagger is in scroll px, one child after another', () => {
    const el = section('<ul data-m="cards"><li>a</li><li>b</li></ul>');
    const [a, b] = el.querySelectorAll('li');
    const s = scene(el, { tracks: [{ part: 'cards', children: true, trigger: 'scrub', keyframes: { opacity: [0, 1] }, range: [0, 100], stagger: 100, ease: 'linear' }] });
    s.update(150);
    assert.equal(Number(a.style.opacity), 1);
    assert.equal(Number(b.style.opacity), 0.5);
    assert.equal(s.focusEnd.get(b), 200, 'keyboard focus knows where the second card is shown');
});

test('steps mark the current item and tell assistive tech', () => {
    const el = section('<ol data-m="list"><li>one</li><li>two</li><li>three</li></ol><p aria-live="polite">Question <span data-ek-step-num="2">01</span> of 03</p>');
    const s = scene(el, { steps: { part: 'list', range: [0, 200], dwell: 0.2 } });
    let heard = null;
    el.addEventListener('ek:step', (e) => { heard = e.detail.index; });
    s.update(200);
    const items = el.querySelectorAll('li');
    assert.ok(items[2].classList.contains('is-active'));
    assert.equal(items[2].getAttribute('aria-current'), 'step');
    assert.equal(items[0].hasAttribute('aria-current'), false);
    assert.equal(heard, 2);
    assert.equal(el.querySelector('[data-ek-step-num]').textContent, '03', 'the live step number follows');
    assert.equal(window.EkMotion, undefined, 'scene.js alone has no global side effects');
});

test('reduced motion shows every scrubbed part in its final state (or as drawn, for reduce "start")', () => {
    const el = section('<p data-m="line">Line</p><span data-m="sticker">*</span><h1 data-m="title">Title</h1>');
    const s = scene(el, {
        tracks: [
            { part: 'line', trigger: 'scrub', keyframes: { opacity: [0, 1] }, range: [300, 600] },
            { part: 'sticker', trigger: 'loop', keyframes: { rotate: [0, 360] }, reduce: 'hide' },
            { part: 'title', trigger: 'scrub', keyframes: { opacity: [1, 0] }, range: [0, 400], reduce: 'start' },
        ],
    }, { reduced: true });
    s.update(0);
    s.update(500);
    assert.equal(Number(el.querySelector('[data-m="line"]').style.opacity), 1);
    assert.equal(el.querySelector('[data-m="sticker"]').style.visibility, 'hidden');
    assert.equal(el.querySelector('[data-m="title"]').style.opacity, '', 'reduce "start": a part that leaves stays as drawn');
});

test('reduced motion "step" tracks switch item by item, without tweening, and keep the stage', () => {
    const el = section('<ul data-m="cards"><li>a</li><li>b</li></ul>');
    const [a, b] = el.querySelectorAll('li');
    const s = scene(el, { stage: { pin: true, length: 1000 }, tracks: [
        { part: 'cards', children: true, trigger: 'scrub', keyframes: { opacity: [0, 1] }, range: [0, 300], stagger: 420, reduce: 'step' },
    ] }, { reduced: true });
    assert.equal(s.pinned, true, 'the stage stays pinned so each item can still be reached');
    s.update(100);
    assert.equal(Number(a.style.opacity), 0, 'before its midpoint: as it starts');
    s.update(200);
    assert.equal(Number(a.style.opacity), 1, 'past its midpoint: done, no in-between');
    assert.equal(Number(b.style.opacity), 0);
    s.update(600);
    assert.equal(Number(b.style.opacity), 1);
});

test('count-up keeps the real number for screen readers', () => {
    const el = section('<strong data-m="stat">10,000+</strong>');
    const s = scene(el, { tracks: [{ part: 'stat', trigger: 'scrub', keyframes: { $count: 'en-IN' }, range: [0, 100], ease: 'linear' }] });
    s.update(50);
    const stat = el.querySelector('[data-m="stat"]');
    assert.equal(stat.querySelector('.visually-hidden').textContent, '10,000+');
    assert.equal(stat.querySelector('[aria-hidden="true"]').textContent, '5,000+');
    s.update(100);
    assert.equal(stat.querySelector('[aria-hidden="true"]').textContent, '10,000+');

    // Rebuilt (EkMotion.refresh): still counts to the real number, not to what it last showed.
    s.destroy();
    const again = scene(el, { tracks: [{ part: 'stat', trigger: 'scrub', keyframes: { $count: 'en-IN' }, range: [0, 100], ease: 'linear' }] });
    again.update(100);
    assert.equal(stat.querySelector('.visually-hidden').textContent, '10,000+');
    assert.equal(stat.querySelector('[aria-hidden="true"]').textContent, '10,000+');
});

test('older [data-anim] sections still animate, with a real spring', async () => {
    const el = section('<p>Old section</p>');
    el.setAttribute('data-anim', 'fade-up');
    el.setAttribute('data-anim-ease', 'spring');
    await import('../../resources/js/frontend/motion/index.js');

    assert.ok(el.hasAttribute('data-anim-done'), 'the CSS stops hiding it; Anime owns the styles');
    assert.ok(observed.includes(el));
    assert.equal(typeof window.EkMotion.get(el).progress, 'number');
    assert.equal(document.documentElement.classList.contains('ek-ready'), true);
});
