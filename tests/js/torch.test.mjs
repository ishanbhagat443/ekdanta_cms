// Home Hero (Torch): a keyboard user reaching the buttons before the beam has
// opened sees them. Run with `npm run test:js`.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { JSDOM } from 'jsdom';
import torchBeam from '../../resources/js/frontend/sections/ekdanta/torch-beam.js';

test('focus inside the hero opens the beam and shows the buttons at once', () => {
    const { window } = new JSDOM('<section><div class="ek-comp"><a href="#contact">Become a big brand</a></div></section>');
    const el = window.document.querySelector('section');
    const root = el.firstElementChild;
    torchBeam(el, { reduced: false, config: {}, root });

    el.querySelector('a').dispatchEvent(new window.FocusEvent('focusin', { bubbles: true }));

    assert.equal(root.style.getPropertyValue('--cta'), '1.000');
    assert.equal(root.style.getPropertyValue('--bk'), '0.0000', 'the beam is open');
});
