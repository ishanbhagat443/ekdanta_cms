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
