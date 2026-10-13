// Screenshots of every section of a page where it starts, a third and two thirds in,
// and where it ends (the brief's Phase 6 positions), at one size.
//   node tests/visual/sections.mjs <path> <name> <width>x<height> [--reduce]
//   EK_ONLY=c-visit,c-faq: only those sections
// Files: tests/visual/shots/sections/<name>-<section>-<n>.png
import { chromium } from '@playwright/test';
import { mkdirSync } from 'node:fs';

const args = process.argv.slice(2);
const [path, name, size] = args.filter((a) => !a.startsWith('--'));
const [width, height] = size.split('x').map(Number);
const out = 'tests/visual/shots/sections';
mkdirSync(out, { recursive: true });

const browser = await chromium.launch({ channel: 'chrome' });
const page = await browser.newPage({
    viewport: { width, height }, isMobile: width < 768, hasTouch: width < 768,
    reducedMotion: args.includes('--reduce') ? 'reduce' : 'no-preference',
});
await page.goto((process.env.EK_BASE || 'http://127.0.0.1:8001') + path, { waitUntil: 'networkidle' });
await page.waitForTimeout(2500);
const sections = await page.evaluate(() => [...document.querySelectorAll('.gsm-section')].map((s) => {
    const top = s.getBoundingClientRect().top + scrollY;
    return { id: s.querySelector('[id]')?.id || s.id, top, height: s.offsetHeight };
}));
const only = process.env.EK_ONLY ? process.env.EK_ONLY.split(',') : null;
for (const s of sections.filter((x) => !only || only.includes(x.id))) {
    const end = Math.max(s.top, s.top + s.height - height);
    const stops = [s.top, s.top + (end - s.top) / 3, s.top + (2 * (end - s.top)) / 3, end].map(Math.round);
    for (const [n, y] of [...new Set(stops)].entries()) {
        await page.evaluate((t) => window.scrollTo(0, t), y);
        await page.waitForTimeout(400);
        await page.screenshot({ path: `${out}/${name}-${s.id}-${n}.png` });
    }
}
await browser.close();
