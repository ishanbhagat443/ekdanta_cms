// Our Story's counters follow the beat index f. Run with `npm run test:js`.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { countAt } from '../../resources/js/frontend/sections/ekdanta/about-story.js';

test('a counter waits for its beat, counts across its span, then holds', () => {
    const years = { to: 10, at: 0.4, span: 0.6 };
    assert.equal(countAt(0, years), 0);
    assert.equal(countAt(0.7, years), 5);
    assert.equal(countAt(1, years), 10);
    assert.equal(countAt(4, years), 10);
});
