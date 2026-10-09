// Runs the real public/js/gsm-animations.js against the real Anime.js bundle
// in jsdom. Run with `npm run test:js` (anime keeps a timer alive, hence --test-force-exit).
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { JSDOM } from 'jsdom';

const read = (path) => readFileSync(new URL('../../' + path, import.meta.url), 'utf8');
const ANIME = read('node_modules/animejs/dist/bundles/anime.umd.min.js');
const SCRIPT = read('public/js/gsm-animations.js');

/** Boot one page and return the params of every anime.animate() call. */
function boot(body) {
    const { window } = new JSDOM(`<!doctype html><html class="js-enabled"><body>${body}</body></html>`, {
        runScripts: 'outside-only',
        pretendToBeVisual: true,
    });
    // Everything is on screen, so the observer reports each section at once.
    window.IntersectionObserver = class {
        constructor(callback) { this.callback = callback; }
        observe(target) { this.callback([{ target, isIntersecting: true }]); }
        unobserve() {}
    };
    window.eval(ANIME);
    const calls = [];
    const animate = window.anime.animate;
    window.anime.animate = (targets, params) => { calls.push(params); return animate(targets, params); };
    window.eval(SCRIPT);
    window.document.dispatchEvent(new window.Event('DOMContentLoaded'));

    return { window, calls };
}

test('"Springy" eases with a real v4 spring, not linearly', () => {
    const { window, calls } = boot('<section data-anim="fade-up" data-anim-ease="spring"></section>');
    assert.equal(calls.length, 1);
    assert.notEqual(typeof calls[0].ease, 'string', 'v3 spring strings run linear in v4');

    // The bug report: halfway through, a 0 → 10 px move sat at exactly 5 px.
    const box = { x: 0 };
    const probe = window.anime.animate(box, { x: 10, ease: calls[0].ease, autoplay: false });
    probe.seek(probe.duration / 2);
    assert.ok(Math.abs(box.x - 5) > 0.5, `expected a spring curve at 50 %, got ${box.x}`);
    window.close();
});

test('named easings still pass through as strings', () => {
    const { window, calls } = boot('<section data-anim="fade-up" data-anim-ease="outBack"></section>');
    assert.equal(calls[0].ease, 'outBack');
    window.close();
});
