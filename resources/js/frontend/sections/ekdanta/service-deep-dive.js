/**
 * Service Deep Dive: one pinned stage, one panel per service group. Each
 * group's panel opens with a circle wipe, its heading and text rise, its
 * services light up one by one while their tiles build (service-tiles.js),
 * and the tabs follow the group in view. Under reduced motion each panel
 * switches in place and every tile shows its finished state.
 * Markup: [data-group] panels in order; inside each, [data-deep="word" |
 * "head" | "text" | "item" | "tile" | "caption" | "dot"]; tiles carry
 * data-tile="<artwork key>"; [data-deep="tab"] buttons and [data-deep="bar"].
 */
import { TILES, applyTile } from './service-tiles.js';

const clamp = (v, a = 0, b = 1) => Math.max(a, Math.min(b, v));
const ease = (t) => (t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2);
const easeOut = (t) => 1 - Math.pow(1 - t, 3);
const easeBack = (t) => { const c1 = 1.70158, c3 = c1 + 1; return 1 + c3 * Math.pow(t - 1, 3) + c1 * Math.pow(t - 1, 2); };

export default function serviceDeepDive(el, { device, reduced, config }) {
    const desktop = device === 'desktop';
    const p = {
        group: 1600, wipeFrom: -320, wipeFor: 360, wipeAt: desktop ? [1078, 440] : [195, 420], wipeTo: desktop ? 1300 : 1100,
        word: desktop ? [220, 440] : [80, 260], rise: desktop ? [40, 30] : [30, 24], tileRise: desktop ? 40 : 30,
        ...(config.scene?.params || {}),
    };
    const root = el.querySelector(`[data-ek-comp="${device}"]`) || el.querySelector('[data-ek-comp="mobile"]') || el;
    const groups = [...root.querySelectorAll('[data-group]')].map((panel) => ({
        panel,
        word: panel.querySelector('[data-deep="word"]'),
        head: panel.querySelector('[data-deep="head"]'),
        text: panel.querySelector('[data-deep="text"]'),
        items: [...panel.querySelectorAll('[data-deep="item"]')],
        tiles: [...panel.querySelectorAll('[data-deep="tile"]')],
        captions: [...panel.querySelectorAll('[data-deep="caption"]')],
        dots: [...panel.querySelectorAll('[data-deep="dot"]')],
        badge: panel.dataset.badge, badgeText: panel.dataset.badgeText, ink: panel.dataset.ink,
    }));
    const tabs = [...root.querySelectorAll('[data-deep="tab"]')];
    const bar = root.querySelector('[data-deep="bar"]');
    const n = groups.length;

    return {
        update(state, scene) {
            const u = scene.u;
            const s = state.px;
            groups.forEach((g, gi) => {
                const sg = s - gi * p.group;
                if (gi > 0) {
                    const r = reduced ? (sg >= -140 ? p.wipeTo : 0) : ease(clamp((sg - p.wipeFrom) / p.wipeFor)) * p.wipeTo;
                    g.panel.style.clipPath = `circle(${(r * u).toFixed(1)}px at ${(p.wipeAt[0] * u).toFixed(1)}px ${(p.wipeAt[1] * u).toFixed(1)}px)`;
                }
                if (sg < -500 || sg > p.group + 600) return; // not on screen: leave it as it is
                const move = (v) => (reduced ? 0 : v);
                if (g.word) g.word.style.transform = `translateX(${(move(p.word[0] - clamp((sg + 300) / 1900) * p.word[1]) * u).toFixed(1)}px)`;
                const e0 = gi === 0 ? -500 : -150;
                const hIn = reduced ? 1 : easeOut(clamp((sg - e0) / 260));
                const tIn = reduced ? 1 : easeOut(clamp((sg - e0 - 80) / 260));
                if (g.head) Object.assign(g.head.style, { opacity: hIn.toFixed(3), transform: `translateY(${(move((1 - hIn) * p.rise[0]) * u).toFixed(1)}px)` });
                if (g.text) Object.assign(g.text.style, { opacity: tIn.toFixed(3), transform: `translateY(${(move((1 - tIn) * p.rise[1]) * u).toFixed(1)}px)` });

                g.items.forEach((item, k) => {
                    const vis = reduced ? 1 : clamp((sg - e0 - 100 - k * 25) / 200);
                    const on = reduced ? 1 : clamp((sg - (150 + k * 180)) / 120);
                    item.style.opacity = (vis * (0.38 + 0.62 * on)).toFixed(3);
                    item.style.transform = `translateY(${(move((1 - easeOut(vis)) * 20) * u).toFixed(1)}px)`;
                    const num = item.firstElementChild;
                    num.style.background = on > 0.5 ? g.badge : 'transparent';
                    num.style.color = on > 0.5 ? g.badgeText : g.ink;
                });

                let lit = 0;
                for (let k = 0; k < g.tiles.length; k++) if (reduced || sg - (150 + k * 180) > 60) lit = k;
                const capIn = reduced ? 1 : clamp((sg - 150) / 120);
                g.captions.forEach((c, k) => { c.style.opacity = (k === lit ? capIn : 0).toFixed(3); c.style.transform = `translateY(${(k === lit ? 0 : 10 * u).toFixed(1)}px)`; });
                g.dots.forEach((d, k) => { d.style.background = k <= lit && capIn > 0 ? g.badge : 'rgba(128,128,128,0.35)'; d.style.width = `${((k === lit ? 18 : 6) * u).toFixed(1)}px`; });

                g.tiles.forEach((tile, k) => {
                    const a = reduced ? 1 : clamp((sg - (100 + k * 180)) / 200);
                    tile.style.opacity = clamp(a * 1.5).toFixed(3);
                    tile.style.transform = `translateY(${(move((1 - easeOut(a)) * p.tileRise) * u).toFixed(1)}px) scale(${(reduced ? 1 : 0.9 + 0.1 * easeBack(a)).toFixed(3)})`;
                    const draw = TILES[tile.dataset.tile];
                    if (draw) applyTile(tile, draw(reduced ? 1 : clamp((sg - (150 + k * 180)) / 330), reduced));
                });
            });

            const active = clamp(Math.floor((s + 150) / p.group), 0, Math.max(0, n - 1));
            tabs.forEach((tab, i) => {
                tab.classList.toggle('is-current', i === active);
                if (i === active) tab.setAttribute('aria-current', 'true');
                else tab.removeAttribute('aria-current');
            });
            if (bar) bar.style.transform = `scaleX(${clamp(s / (n * p.group)).toFixed(3)})`;
        },
    };
}
