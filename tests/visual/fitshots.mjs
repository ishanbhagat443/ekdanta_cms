// Screen-fit comparison shots: every page at the screen-fit sizes, a screen at
// a time, reduced motion (steady frames) and the cookie choice made.
//   node tests/visual/fitshots.mjs <dir> [size …]   → tests/visual/shots/<dir>/<page>-<size>-<n>.png
// Compare two runs with tests/visual/fitdiff.py.
import { chromium } from '@playwright/test';
import { mkdirSync } from 'node:fs';

const base = process.env.EK_BASE || 'http://127.0.0.1:8001';
const [dir, ...sizeArgs] = process.argv.slice(2);
const sizes = (sizeArgs.length ? sizeArgs : ['1536x735', '1536x680', '1440x900', '1180x820', '1920x1080', '820x1180', '390x844', '360x640']).map((s) => s.split('x').map(Number));
const pages = { home: '/', contact: '/contact', services: '/services', thanks: '/thank-you', nf: '/no-such-page', about: '/about', work: '/work',
    case: '/work/yantra-design', svc: '/services/meta-ads', blog: '/blog', art: '/blog/why-your-factory-needs-reels-not-just-brochures',
    privacy: '/privacy-policy', terms: '/terms-of-service' };
const out = `tests/visual/shots/${dir}`;
mkdirSync(out, { recursive: true });
const browser = await chromium.launch({ channel: 'chrome' });
for (const [w, h] of sizes) {
    const context = await browser.newContext({ viewport: { width: w, height: h }, isMobile: w < 768, hasTouch: w < 768, reducedMotion: 'reduce' });
    await context.addCookies([{ name: 'cookie_consent', value: 'necessary', url: base }]);
    const page = await context.newPage();
    for (const [name, path] of Object.entries(pages)) {
        await page.goto(base + path, { waitUntil: 'networkidle' });
        await page.waitForTimeout(400);
        const total = await page.evaluate(() => document.documentElement.scrollHeight);
        let n = 0;
        for (let y = 0; y < total; y += h) {
            await page.evaluate((top) => window.scrollTo(0, top), y);
            await page.waitForTimeout(150);
            await page.screenshot({ path: `${out}/${name}-${w}x${h}-${String(n++).padStart(2, '0')}.png` });
        }
    }
    await context.close();
}
await browser.close();
