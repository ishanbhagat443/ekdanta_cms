// Phase 6 checks on the preview database (php artisan serve --env=preview --port=8001,
// built with php artisan ekdanta:seed --with-pages --env=preview):
//   errors     console errors, page errors and failed requests, with motion on
//   cls        layout shift while the page loads (no scrolling), with motion on
//   overlap    text running into other text, scrolling the whole page with reduced
//              motion (the finished layouts), at desktop, laptop and two phone sizes
//   sideways   a page that scrolls sideways
//   no-js      headings and paragraphs that are hidden with JavaScript off
//   page       the checklist per page (finished layouts): one h1, no skipped heading
//              level, images with alt, links, buttons and fields with a name, page
//              language, ids used once, and (phones) tap targets at least 24 × 24
//   node tests/visual/check.mjs [/path …]   (report: tests/visual/shots/check-report.json)
//   EK_SIZES=820x1180,1024x1366: other sizes (errors and layout shift at the first and third)
import { chromium } from '@playwright/test';
import { mkdirSync, writeFileSync } from 'node:fs';

const base = process.env.EK_BASE || 'http://127.0.0.1:8001';
const paths = process.argv.slice(2).length ? process.argv.slice(2) : [
    '/', '/contact', '/services', '/thank-you', '/no-such-page', '/about', '/work', '/work/yantra-design',
    '/services/meta-ads', '/blog', '/blog/why-your-factory-needs-reels-not-just-brochures', '/privacy-policy', '/terms-of-service',
];
const sizes = (process.env.EK_SIZES || '1440x900,1280x720,390x844,360x640').split(',').map((s) => s.split('x').map(Number));
const browser = await chromium.launch({ channel: 'chrome' });
const report = [];

const open = async (options) => {
    const [width, height] = options.size;
    const context = await browser.newContext({
        viewport: { width, height }, isMobile: width < 768, hasTouch: width < 768,
        reducedMotion: options.reduce ? 'reduce' : 'no-preference', javaScriptEnabled: options.js !== false,
    });
    // The finished layouts are checked after a cookie choice: the bar covers the bottom until then.
    if (options.reduce) await context.addCookies([{ name: 'cookie_consent', value: 'necessary', url: base }]);
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

// The page checklist, on what is drawn (each section has a desktop and a phone layout;
// the other one is display: none and out of the accessibility tree).
const checklist = () => {
    const drawn = (el) => el.checkVisibility() && !el.closest('[aria-hidden="true"]');
    const where = (el) => `${el.closest('[id]')?.id || el.tagName}: ${(el.textContent.trim() || el.outerHTML).slice(0, 50)}`;
    const named = (el) => (el.getAttribute('aria-label') || el.getAttribute('title') || el.textContent.trim()
        || [...el.querySelectorAll('img[alt], svg title')].map((i) => i.alt || i.textContent).join('')
        || (el.getAttribute('aria-labelledby') || '').split(' ').map((id) => document.getElementById(id)?.textContent || '').join('')).trim();
    const levels = [...document.querySelectorAll('h1, h2, h3, h4, h5, h6')].filter(drawn).map((h) => [Number(h.tagName[1]), h]);
    const fields = [...document.querySelectorAll('input:not([type=hidden]), select, textarea')].filter(drawn);
    const ids = [...document.querySelectorAll('[id]')].map((e) => e.id);
    return {
        lang: document.documentElement.lang || 'missing',
        h1: levels.filter(([l]) => l === 1).length,
        skips: levels.flatMap(([l, h], i) => (i && l > levels[i - 1][0] + 1 ? [`h${l} after h${levels[i - 1][0]} ${where(h)}`] : [])),
        noAlt: [...document.querySelectorAll('img:not([alt])')].map(where),
        noName: [...document.querySelectorAll('a[href], button, [role=button]')].filter(drawn).filter((el) => !named(el)).map((el) => el.outerHTML.slice(0, 80)),
        noLabel: fields.filter((f) => !f.labels?.length && !f.getAttribute('aria-label') && !f.getAttribute('aria-labelledby') && !f.title).map((f) => f.outerHTML.slice(0, 80)),
        dupIds: [...new Set(ids.filter((id, i) => ids.indexOf(id) !== i))],
        small: innerWidth < 768 ? [...document.querySelectorAll('a[href], button, input, select, summary, [role=button]')].filter(drawn)
            .map((el) => el.labels?.[0] || el) // a field's tap target is its label
            .filter((el) => !el.closest('p, li > p') && getComputedStyle(el).display !== 'inline')
            .filter((el) => { const r = el.getBoundingClientRect(); return r.width && r.height && (r.width < 24 || r.height < 24); })
            .map((el) => `${el.closest('[id]')?.id || ''}: ${el.tagName} ${Math.round(el.getBoundingClientRect().width)}×${Math.round(el.getBoundingClientRect().height)} ${(el.textContent.trim() || el.getAttribute('aria-label') || '').slice(0, 30)}`) : [],
    };
};

for (const path of paths) {
    const result = { path, errors: [], cls: {}, overlap: {}, sideways: [], noJs: [], page: {} };

    // Motion on: errors and layout shift while loading.
    for (const size of sizes.filter((s, i) => i === 0 || i === 2)) {
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
        result.page[size.join('×')] = await page.evaluate(checklist);
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
    const checks = Object.entries(result.page).map(([s, c]) => `${s}: lang ${c.lang} h1 ${c.h1} skips ${c.skips.length} alt ${c.noAlt.length} names ${c.noName.length} labels ${c.noLabel.length} dup-ids ${c.dupIds.length}${parseInt(s, 10) < 768 ? ` small ${c.small.length}` : ''}`).join(' | ');
    console.log(`${path}  errors ${result.errors.length}  cls ${JSON.stringify(result.cls)}  overlaps ${overlaps || 0}  sideways ${result.sideways.join(' ') || 'none'}  no-js hidden ${result.noJs.length}\n    ${checks}`);
}

mkdirSync('tests/visual/shots', { recursive: true });
writeFileSync('tests/visual/shots/check-report.json', JSON.stringify(report, null, 2));
await browser.close();
