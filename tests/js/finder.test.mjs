// The 404 Page Finder's search: title or extra words, any case, five at most.
// Run with `npm run test:js`.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { search } from '../../resources/js/frontend/sections/ekdanta/page-finder.js';

const index = [
    { k: 'page', t: 'Home', u: '/', w: 'start homepage' },
    { k: 'service', t: 'Meta ads', u: '/services/meta-ads', w: 'facebook instagram leads' },
    { k: 'article', t: 'Five signs your website is losing you leads', u: '/blog/five-signs', w: '' },
    ...Array.from({ length: 6 }, (_, i) => ({ k: 'article', t: `Reels idea ${i}`, u: `/blog/${i}`, w: '' })),
];

test('matches the title or the extra words, ignoring case and spaces', () => {
    assert.deepEqual(search(index, '  LEADS ').map((r) => r.t), ['Meta ads', 'Five signs your website is losing you leads']);
    assert.deepEqual(search(index, 'homepage').map((r) => r.u), ['/']);
});

test('an empty box finds nothing; many matches stop at five', () => {
    assert.deepEqual(search(index, '   '), []);
    assert.equal(search(index, 'reels').length, 5);
});
