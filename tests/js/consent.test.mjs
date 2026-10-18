// The cookie bar's choice, kept in a cookie; tracking placeholders run only
// after "Allow all". Run with `npm run test:js`.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { JSDOM } from 'jsdom';
import initConsent, { choice } from '../../resources/js/frontend/consent.js';

const page = (reload = '0') => {
    const dom = new JSDOM(`<!doctype html><body>
        <button type="button" data-cookie-settings>Cookie settings</button>
        <script type="text/plain" data-tracking data-x="1">window.tracked = true;</script>
        <div data-cookie-bar data-reload="${reload}" hidden>
            <button type="button" data-cookie-choice="all">Allow all</button>
            <button type="button" data-cookie-choice="necessary">Only necessary</button>
        </div></body>`, { url: 'https://ekdanta.test/' });
    const win = { location: { protocol: 'https:', reloads: 0, reload() { this.reloads++; } } };
    initConsent(dom.window.document, win);
    return { doc: dom.window.document, win };
};

test('"Cookie settings" brings the bar back and moves focus into it', () => {
    const { doc } = page();
    doc.querySelector('[data-cookie-settings]').click();
    assert.equal(doc.querySelector('[data-cookie-bar]').hidden, false);
    assert.equal(doc.activeElement.dataset.cookieChoice, 'all');
});

test('"Only necessary" is kept and runs nothing; "Allow all" runs the marked scripts', () => {
    const { doc, win } = page();
    doc.querySelector('[data-cookie-settings]').click();
    doc.querySelector('[data-cookie-choice="necessary"]').click();
    assert.equal(choice(doc), 'necessary');
    assert.equal(doc.querySelector('[data-cookie-bar]').hidden, true);
    assert.equal(doc.activeElement.hasAttribute('data-cookie-settings'), true, 'focus goes back to the button that opened it');
    assert.equal(doc.querySelectorAll('script[type="text/plain"]').length, 1);

    doc.querySelector('[data-cookie-choice="all"]').click();
    assert.equal(choice(doc), 'all');
    assert.equal(doc.querySelectorAll('script[type="text/plain"]').length, 0);
    assert.equal(doc.querySelector('script[data-x="1"]').text, 'window.tracked = true;');
    assert.equal(win.location.reloads, 0, 'no server tags on this page');
});

test('with tags only the server adds, a changed choice reloads the page', () => {
    const { doc, win } = page('1');
    doc.querySelector('[data-cookie-choice="all"]').click();
    assert.equal(win.location.reloads, 1);
    doc.querySelector('[data-cookie-choice="all"]').click();
    assert.equal(win.location.reloads, 1, 'the same choice again changes nothing');
    doc.querySelector('[data-cookie-choice="necessary"]').click();
    assert.equal(win.location.reloads, 2, 'and back: the tags go');
});
