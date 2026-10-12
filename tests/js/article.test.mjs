// The article's reading rail: how far through, which heading, minutes left. Run with `npm run test:js`.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { progress, active, minutesLeft } from '../../resources/js/frontend/sections/ekdanta/article.js';

test('progress follows a reading line 60 % down the screen, from the first line to the last', () => {
    assert.equal(progress(900, 4000, 900), 0, 'text still below the line');
    assert.equal(progress(540, 4000, 900), 0, 'first line on the line');
    assert.equal(progress(-1460, 4000, 900), 0.5);
    assert.equal(progress(-5000, 4000, 900), 1, 'past the end');
});

test('the heading in view is the last one that reached the line', () => {
    assert.equal(active([500, 1200, 2000], 260), -1, 'none yet: the bar hides');
    assert.equal(active([100, 1200, 2000], 260), 0);
    assert.equal(active([-900, 100, 400], 260), 1);
    assert.equal(active([-900, -100, Infinity], 260), 1, 'a missing heading never lights');
});

test('minutes left count down, and reach 0 only at the end', () => {
    assert.equal(minutesLeft(5, 0), 5);
    assert.equal(minutesLeft(5, 0.5), 3);
    assert.equal(minutesLeft(5, 0.99), 1);
    assert.equal(minutesLeft(5, 1), 0);
});
