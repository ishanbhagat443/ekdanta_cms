// Phase 6 checks on the preview database (php artisan serve --env=preview --port=8001,
// built with php artisan ekdanta:seed --with-pages --env=preview):
//   errors     console errors, page errors and failed requests, with motion on
//   cls        layout shift while the page loads (no scrolling), with motion on
//   overlap    text running into other text, scrolling the whole page with reduced
//              motion (the finished layouts), at desktop, laptop and two phone sizes
//   sideways   a page that scrolls sideways
//   no-js      headings and paragraphs that are hidden with JavaScript off
//   node tests/visual/check.mjs [/path …]   (report: tests/visual/shots/check-report.json)
import { chromium } from '@playwright/test';
import { mkdirSync, writeFileSync } from 'node:fs';

const base = process.env.EK_BASE || 'http://127.0.0.1:8001';
const paths = process.argv.slice(2).length ? process.argv.slice(2) : [
    '/', '/contact', '/services', '/thank-you', '/no-such-page', '/about', '/work', '/work/yantra-design',
    '/services/meta-ads', '/blog', '/blog/why-your-factory-needs-reels-not-just-brochures',
];
const sizes = [[1440, 900], [1280, 720], [390, 844], [360, 640]];
const browser = await chromium.launch({ channel: 'chrome' });
const report = [];

const open = async (options) => {
    const [width, height] = options.size;
    const context = await browser.newContext({
        viewport: { width, height }, isMobile: width < 768, hasTouch: width < 768,
        reducedMotion: options.reduce ? 'reduce' : 'no-preference', javaScriptEnabled: options.js !== false,
    });
    const page = await context.newPage();
    return { context, page };
};

// Text runs that overlap other text in the viewport. Left out: decoration, hidden text,
// text covered by something else (a card on top, the back of a flipped card), and the
// line boxes of tight headlines: each run counts only its letters' middle (60 % of the
// font size), so lines set closer than 1 don't count, letters running into letters do.
const overlapsInView = () => {
    if (!document.getElementById('ek-hit-all')) {
        document.head.append(Object.assign(document.createElement('style'), { id: 'ek-hit-all', textContent: '*{pointer-events:auto!important}' }));
    }
    const shown = (el, r) => [[0.5, 0.5], [0.15, 0.3], [0.85, 0.3], [0.15, 0.7], [0.85, 0.7]].some(([fx, fy]) => {
        const hit = document.elementFromPoint(r.left + r.width * fx, r.top + r.height * fy);
        return hit && (hit === el || el.contains(hit));
    });
    const runs = [];
    const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
    while (walker.nextNode()) {
        const node = walker.currentNode;
        const el = node.parentElement;
        if (!node.textContent.trim() || !el || el.closest('[aria-hidden="true"], .visually-hidden, script, style, template, noscript, header')) continue;
        if (!el.checkVisibility({ opacityProperty: true, visibilityProperty: true })) continue;
        const range = document.createRange();
        range.selectNodeContents(node);
        for (const r of range.getClientRects()) {
            if (r.width < 3 || r.height < 3 || r.bottom < 0 || r.top > innerHeight || r.right < 0 || r.left > innerWidth || !shown(el, r)) continue;
            const half = parseFloat(getComputedStyle(el).fontSize) * 0.3;
            const mid = (r.top + r.bottom) / 2;
            runs.push({ r: { left: r.left, right: r.right, top: mid - half, bottom: mid + half, width: r.width, height: half * 2 }, el, text: node.textContent.trim().slice(0, 40) });
        }
    }
    const found = [];
    for (let i = 0; i < runs.length; i++) {
        for (let j = i + 1; j < runs.length; j++) {
            const a = runs[i];
            const b = runs[j];
            if (a.el === b.el || a.el.contains(b.el) || b.el.contains(a.el)) continue;
            const w = Math.min(a.r.right, b.r.right) - Math.max(a.r.left, b.r.left);
            const h = Math.min(a.r.bottom, b.r.bottom) - Math.max(a.r.top, b.r.top);
            if (w > 4 && h > 4 && w * h > 0.2 * Math.min(a.r.width * a.r.height, b.r.width * b.r.height)) {
                const section = a.el.closest('[id]')?.id || '';
                found.push(`${section}: “${a.text}” × “${b.text}”`);
            }
        }
    }
    return found;
};

for (const path of paths) {
    const result = { path, errors: [], cls: {}, overlap: {}, sideways: [], noJs: [] };

    // Motion on: errors and layout shift while loading.
    for (const size of [sizes[0], sizes[2]]) {
        const { context, page } = await open({ size });
        await page.addInitScript(() => {
            window.__cls = 0;
            new PerformanceObserver((list) => list.getEntries().forEach((e) => { if (!e.hadRecentInput) window.__cls += e.value; }))
                .observe({ type: 'layout-shift', buffered: true });
        });
        page.on('pageerror', (e) => result.errors.push(`${size[0]}: ${e.message}`));
        page.on('console', (m) => m.type() === 'error' && !m.text().startsWith('Failed to load resource') && result.errors.push(`${size[0]}: ${m.text()}`));
        page.on('response', (r) => r.status() >= 400 && r.url() !== base + path && result.errors.push(`${size[0]}: HTTP ${r.status()} ${r.url()}`));
        await page.goto(base + path, { waitUntil: 'networkidle' });
        await page.waitForTimeout(2500);
        result.cls[size[0]] = Number((await page.evaluate(() => window.__cls)).toFixed(3));
        await context.close();
    }

    // Reduced motion: every finished layout, the whole page, at every size.
    for (const size of sizes) {
        const { context, page } = await open({ size, reduce: true });
        await page.goto(base + path, { waitUntil: 'networkidle' });
        await page.waitForTimeout(500);
        const total = await page.evaluate(() => document.documentElement.scrollHeight);
        const found = new Set();
        for (let y = 0; y < total; y += Math.round(size[1] * 0.5)) {
            await page.evaluate((top) => window.scrollTo(0, top), y);
            await page.waitForTimeout(120);
            (await page.evaluate(overlapsInView)).forEach((o) => found.add(o));
        }
        if (await page.evaluate(() => document.documentElement.scrollWidth > innerWidth + 1)) result.sideways.push(size.join('×'));
        result.overlap[size.join('×')] = [...found];
        await context.close();
    }

    // JavaScript off: every heading and paragraph can be read.
    const { context, page } = await open({ size: sizes[0], js: false });
    await page.goto(base + path, { waitUntil: 'load' });
    result.noJs = await page.evaluate(() => [...document.querySelectorAll('main h1, main h2, main h3, main p, .gsm-section h1, .gsm-section h2, .gsm-section h3, .gsm-section p')]
        // (each section draws a desktop and a phone layout; the other one is meant to be hidden)
        // (closed <details> open without a script; the Brief Builder's "sent" note only follows a send)
        .filter((el) => el.textContent.trim() && !el.closest('[aria-hidden="true"], .visually-hidden, [hidden], noscript, details:not([open]), [data-brief-sent]'))
        .filter((el) => getComputedStyle(el.closest('.ek-comp') || el).display !== 'none')
        .filter((el) => !el.checkVisibility({ opacityProperty: true, visibilityProperty: true }) || el.getBoundingClientRect().height < 1)
        // …unless the section's no-script list carries it
        .filter((el) => !(el.closest('.ek')?.querySelector('.ek-noscript')?.textContent || '').replace(/\s+/g, '').includes(el.textContent.replace(/\s+/g, '').slice(0, 30)))
        .map((el) => `${el.closest('[id]')?.id || ''}: ${el.tagName} “${el.textContent.trim().slice(0, 40)}”`));
    await context.close();

    report.push(result);
    const overlaps = Object.entries(result.overlap).filter(([, list]) => list.length).map(([s, list]) => `${s} ${list.length}`).join(', ');
    console.log(`${path}  errors ${result.errors.length}  cls ${JSON.stringify(result.cls)}  overlaps ${overlaps || 0}  sideways ${result.sideways.join(' ') || 'none'}  no-js hidden ${result.noJs.length}`);
}

mkdirSync('tests/visual/shots', { recursive: true });
writeFileSync('tests/visual/shots/check-report.json', JSON.stringify(report, null, 2));
await browser.close();
