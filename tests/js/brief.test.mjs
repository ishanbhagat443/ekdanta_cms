// The Brief Builder's WhatsApp message, built in the browser from the answers
// (it must read like the server's Form::whatsappMessage). Run with `npm run test:js`.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { JSDOM } from 'jsdom';

const { window } = new JSDOM('<!doctype html><body></body>');
globalThis.FormData = window.FormData;
const { message } = await import('../../resources/js/frontend/sections/ekdanta/brief-builder.js');

test('the message is the greeting, then each answered field\'s sentence', () => {
    window.document.body.innerHTML = `<form data-wa-intro="Hi Ekdanta!">
        <div data-brief-field="services" data-wa="I would like help with: {value}.">
            <input type="checkbox" name="services[]" value="Branding" checked><input type="checkbox" name="services[]" value="SEO" checked><input type="checkbox" name="services[]" value="Website">
        </div>
        <div data-brief-field="your_name" data-wa="My name is {value}."><input name="your_name" value="  Asha "></div>
        <div data-brief-field="company" data-wa="Company: {value}."><input name="company" value=""></div>
        <div data-brief-field="notes" data-wa=""><input name="notes" value="not in the message"></div>
    </form>`;
    assert.equal(message(window.document.querySelector('form')), 'Hi Ekdanta! I would like help with: Branding, SEO. My name is Asha.');
});

test('Send waits for the details, then for the consent box, which stays out of the message', async () => {
    const { missing } = await import('../../resources/js/frontend/sections/ekdanta/brief-builder.js');
    window.document.body.innerHTML = `<form data-wa-intro="Hi!"><fieldset>
        <div data-brief-field="your_name" data-wa="My name is {value}."><input name="your_name" required value=""></div>
        <div data-brief-field="company" data-wa="Company: {value}."><input name="company" value=""></div>
        <div data-brief-field="consent"><input class="ek-consent-input" type="checkbox" name="consent" value="wide" required></div>
    </fieldset></form>`;
    const step = window.document.querySelector('fieldset');
    assert.equal(missing(step).name, 'your_name');
    step.querySelector('[name=your_name]').value = 'Asha';
    assert.equal(missing(step).name, 'consent');
    step.querySelector('[name=consent]').checked = true;
    assert.equal(missing(step), null);
    assert.equal(message(window.document.querySelector('form')), 'Hi! My name is Asha.');
});
