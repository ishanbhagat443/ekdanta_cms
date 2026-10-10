// Screenshots of a page at given scroll positions, at an exact viewport.
//   node tests/visual/shoot.mjs <path> <name> <width>x<height> <y,y,…> [--reduce] [--wait=ms]
// Base URL: EK_BASE (default http://127.0.0.1:8001, the preview database);
// output folder: EK_SHOTS (default tests/visual/shots, git-ignored).
import { chromium } from '@playwright/test';
import { mkdirSync } from 'node:fs';

const args = process.argv.slice(2);
const flags = Object.fromEntries(args.filter((a) => a.startsWith('--')).map((a) => a.slice(2).split('=')));
const [path, name, size, ys = '0'] = args.filter((a) => !a.startsWith('--'));
const [width, height] = size.split('x').map(Number);
const base = process.env.EK_BASE || 'http://127.0.0.1:8001';
const out = process.env.EK_SHOTS || 'tests/visual/shots';
mkdirSync(out, { recursive: true });

const browser = await chromium.launch({ channel: 'chrome' });
const page = await browser.newPage({
    viewport: { width, height },
    reducedMotion: 'reduce' in flags ? 'reduce' : 'no-preference',
    isMobile: width < 768,
    hasTouch: width < 768,
});
const errors = [];
page.on('pageerror', (e) => errors.push(e.message));
page.on('console', (m) => m.type() === 'error' && !m.text().startsWith('Failed to load resource') && errors.push(m.text()));
page.on('response', (r) => r.status() >= 400 && errors.push(`HTTP ${r.status()} ${r.url()}`));

await page.goto(base + path, { waitUntil: 'networkidle' });
await page.waitForTimeout(Number(flags.wait || 300));
for (const y of ys.split(',')) {
    await page.evaluate((top) => window.scrollTo(0, top), Number(y));
    await page.waitForTimeout(350);
    await page.screenshot({ path: `${out}/${name}-${y}.png` });
}
if (errors.length) console.log('page errors:', errors.join(' | '));
await browser.close();
