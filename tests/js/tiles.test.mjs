// Service Deep Dive tiles: every {placeholder} in a tile's artwork gets a value
// from its tile function (resources/js/frontend/sections/ekdanta/service-tiles.js).
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readdirSync, readFileSync } from 'node:fs';

const { TILES } = await import('../../resources/js/frontend/sections/ekdanta/service-tiles.js');
const dir = new URL('../../resources/views/frontend/sections/ekdanta/tiles/', import.meta.url);

test('each tile artwork has a tile function that fills all its bindings', () => {
    const files = readdirSync(dir).filter((f) => f.endsWith('.blade.php'));
    assert.equal(files.length, Object.keys(TILES).length, 'one function per artwork');
    for (const file of files) {
        const key = file.replace('.blade.php', '');
        assert.ok(TILES[key], `${key}: no tile function`);
        const html = readFileSync(new URL(file, dir), 'utf8');
        const names = new Set([...html.matchAll(/data-b-[\w-]+="([^"]*)"/g)].flatMap((m) => [...m[1].matchAll(/\{(\w+)\}/g)].map((n) => n[1])));
        for (const p of [0, 0.5, 1]) {
            const values = TILES[key](p, false);
            for (const name of names) assert.notEqual(values[name], undefined, `${key}: {${name}} has no value at p=${p}`);
        }
    }
});
