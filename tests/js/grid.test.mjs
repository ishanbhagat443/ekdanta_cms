// The blog grid's filter: a topic and words. Run with `npm run test:js`.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { matches } from '../../resources/js/frontend/sections/ekdanta/blog-grid.js';

const card = { topic: 'social-media ads', text: 'why your factory needs reels social media' };

test('a card matches its own topics and words, in any case', () => {
    assert.equal(matches(card, '', ''), true);
    assert.equal(matches(card, 'ads', '  REELS '), true);
    assert.equal(matches(card, 'social', ''), false, 'whole topic slugs only');
    assert.equal(matches(card, 'branding', ''), false);
    assert.equal(matches(card, '', 'packaging'), false);
});
