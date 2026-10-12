// The Work client wall's industry filter. Run with `npm run test:js`.
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { slots } from '../../resources/js/frontend/sections/ekdanta/client-wall.js';

test('matching logos fill the first places in order; the rest keep their own', () => {
    const wall = ['Retail', 'Services', 'Retail', 'Food and beverage'];
    assert.deepEqual(slots(wall, 'Retail'), [{ match: true, slot: 0 }, { match: false, slot: 1 }, { match: true, slot: 1 }, { match: false, slot: 3 }]);
    assert.deepEqual(slots(wall, '').map((s) => s.slot), [0, 1, 2, 3]);
});
