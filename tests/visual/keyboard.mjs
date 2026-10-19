// Keyboard pass: Tab through each page from the top and report focus stops that
// can't be seen (hidden, transparent, off the screen once focused) or that lack
// the focus ring (3 px #F2A230 outline, 3 px offset).
//   node tests/visual/keyboard.mjs [/path …]   (EK_SIZES=1440x900,390x844)
import { chromium } from '@playwright/test';

const base = process.env.EK_BASE || 'http://127.0.0.1:8001';
const paths = process.argv.slice(2).length ? process.argv.slice(2) : [
    '/', '/contact', '/services', '/thank-you', '/no-such-page', '/about', '/work', '/work/yantra-design',
    '/services/meta-ads', '/blog', '/blog/why-your-factory-needs-reels-not-just-brochures', '/privacy-policy', '/terms-of-service',
];
const sizes = (process.env.EK_SIZES || '1440x900,390x844').split(',').map((s) => s.split('x').map(Number));
const browser = await chromium.launch({ channel: 'chrome' });
let problems = 0;

for (const [width, height] of sizes) {
    const context = await browser.newContext({ viewport: { width, height }, isMobile: width < 768, hasTouch: width < 768 });
    await context.addCookies([{ name: 'cookie_consent', value: 'necessary', url: base }]);
    const page = await context.newPage();
    for (const path of paths) {
        await page.goto(base + path, { waitUntil: 'networkidle' });
        await page.waitForTimeout(800);
        const stops = [];
        const bad = [];
        for (let i = 0; i < 150; i++) {
            await page.keyboard.press('Tab');
            await page.waitForTimeout(700); // the stage scrolls the focused part into view
            const s = await page.evaluate(() => {
                const el = document.activeElement;
                if (!el || el === document.body) return null;
                if (el.dataset.kbSeen) return { wrapped: true };
                el.dataset.kbSeen = '1';
                // A visually hidden field shows its focus on its label (the next sibling) or its wrapper.
                const shown = (x) => x && x.checkVisibility({ opacityProperty: true, visibilityProperty: true }) && x.getBoundingClientRect().width > 0;
                const ringed = (x) => { const c = x && getComputedStyle(x); return !!c && c.outlineStyle !== 'none' && parseFloat(c.outlineWidth) >= 3 && c.outlineColor.replace(/\s/g, '') === 'rgb(242,162,48)'; };
                const face = ['INPUT', 'SELECT', 'TEXTAREA'].includes(el.tagName)
                    ? [el, el.nextElementSibling, el.parentElement, el.parentElement?.parentElement].find((x) => shown(x) && ringed(x)) || el
                    : el;
                const r = face.getBoundingClientRect();
                const cs = getComputedStyle(face);
                const label = `${el.closest('[id]')?.id || ''} ${el.tagName} "${(el.getAttribute('aria-label') || el.textContent || '').trim().replace(/\s+/g, ' ').slice(0, 30)}"`;
                const seen = shown(face) && r.height > 0
                    && r.bottom > 0 && r.top < innerHeight && r.right > 0 && r.left < innerWidth;
                const ring = ringed(face);
                return { label, seen, ring, ring_desc: `${cs.outlineStyle} ${cs.outlineWidth} ${cs.outlineColor} offset ${cs.outlineOffset}` };
            });
            if (!s) continue;
            if (s.wrapped) break; // back at the first stop
            stops.push(s.label);
            if (!s.seen) bad.push(`not visible: ${s.label}`);
            else if (!s.ring) bad.push(`no ring: ${s.label} (${s.ring_desc})`);
        }
        problems += bad.length;
        console.log(`${width}x${height} ${path}: ${stops.length} stops${bad.length ? '\n    ' + bad.join('\n    ') : ''}`);
    }
    await context.close();
}
await browser.close();
process.exitCode = problems ? 1 : 0;
