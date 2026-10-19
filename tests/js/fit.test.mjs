// Screen fit's automatic rule and a section's own anchors. Run with `npm run test:js`.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { JSDOM } from 'jsdom';
import { anchorOf, override } from '../../resources/js/frontend/motion/fit.js';

test('parts are anchored by where they sit in the design frame', () => {
    const part = (x0, x1, more = {}) => anchorOf({ x0, x1, y0: 100, y1: 300, text: true, media: false, ...more }, 1440, 900);
    assert.deepEqual(part(96, 600), ['L', false]);
    assert.deepEqual(part(880, 1344), ['R', false]);
    assert.deepEqual(part(500, 940), ['C', false]);
    assert.deepEqual(part(0, 1440), ['C', false], 'a full-width part with text centres');
    assert.deepEqual(part(0, 1440, { text: false, media: true, y0: 0, y1: 900 }), ['S', false], 'a full-frame picture layer fills the screen');
    assert.deepEqual(part(0, 1440, { text: false, media: true }), ['SX', false]);
    assert.deepEqual(part(96, 1100), ['L', true], 'a part wider than 66 % keeps the section centred');
});

test('a section\'s own anchors win, by selector', () => {
    const { window } = new JSDOM('<div class="ek-comp ek-comp--d"><div class="ek-art"><div data-m="beam"></div><div data-m="wk-text"></div></div></div>');
    const [beam, text] = window.document.querySelectorAll('.ek-art > div');
    const parts = { '[data-m="beam"]': 'S', '.ek-comp--m [data-m="wk-text"]': 'C' };
    assert.equal(override(beam, parts), 'S');
    assert.equal(override(text, parts), '', 'a phone-only anchor does not apply to the desktop layout');
});
