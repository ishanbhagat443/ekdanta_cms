/**
 * Ekdanta · Work Showcase. Three phases by scroll position (design px since
 * the section's top), as the design's renderVals():
 *   gather   the cards arrive into a scatter            [gatherFrom, +gatherFor]
 *   forward  the first comes forward, the others fly off [forwardFrom, +forwardFor]
 *   carousel one case at a time, by distance from the current index
 * The background blends between the cases' brand colours. Under reduced
 * motion the cases switch in place. Numbers: Motion tab → Scene settings.
 */
import { clamp, lerp, inOutQuad as ease } from '../../motion/math.js';
const rgb = (hex) => [1, 3, 5].map((i) => parseInt(hex.slice(i, i + 2), 16));
const mix = (c1, c2, t) => `rgb(${c1.map((v, i) => Math.round(lerp(v, c2[i], t))).join(',')})`;

export default function workIntro(el, { reduced, config, root }) {
    const p = {
        gatherFrom: -600, gatherFor: 1100, forwardFrom: 500, forwardFor: 600, carouselFrom: 1100, step: 700,
        hero: [-320, 0], spacing: 720, dip: 40, tilt: 10, spread: 1.6, drop: 320, shrink: 0.4,
        panelRise: 40, panelShift: -60, introRise: 40, dot: [10, 34],
        scatter: [[-470, 60, 0.42, -8], [-170, 230, 0.38, 6], [150, 40, 0.42, 7], [430, 220, 0.38, -6], [580, 40, 0.34, 5], [-580, 250, 0.34, -4]],
        ...(config.scene?.params || {}),
    };
    const cards = [...root.querySelectorAll('[data-m="work-card"]')];
    const panels = [...root.querySelectorAll('[data-m="work-panels"] > *')];
    const dots = [...root.querySelectorAll('[data-m="work-dots"] > *')];
    const dotBox = root.querySelector('[data-m="work-dots"]');
    const intro = root.querySelector('[data-m="work-intro"]');
    const stage = root.querySelector('[data-ek-stage]');
    const base = rgb(el.querySelector('.ek-work-showcase')?.dataset.baseColour || el.firstElementChild?.dataset.baseColour || '#0A5A30');
    const colours = cards.map((c) => rgb(c.dataset.colour || '#0A5A30'));
    const last = Math.max(0, cards.length - 1);

    return {
        update(state, scene) {
            const u = scene.u;
            const s = state.px;
            const aT = clamp((s - p.gatherFrom) / p.gatherFor);
            const a = ease(aT);
            const bT = clamp((s - p.forwardFrom) / p.forwardFor);
            const b = ease(bT);
            const cf = clamp((s - p.carouselFrom) / p.step, 0, last);
            const inC = s >= p.carouselFrom;

            cards.forEach((card, i) => {
                const [px, py, ps, pr] = p.scatter[i % p.scatter.length];
                let x;
                let y;
                let sc;
                let r;
                let op;
                let z;
                if (reduced) {
                    [x, y, r, sc, z] = [p.hero[0], p.hero[1], -3, 1, 20];
                    op = inC ? clamp(1 - Math.abs(Math.round(cf) - i)) : (i === 0 ? clamp(aT * 2) : 0);
                } else if (!inC) {
                    if (bT <= 0) {
                        [x, y, sc, r, op, z] = [lerp(px * p.spread, px, a), lerp(py + p.drop, py, a), lerp(ps * p.shrink, ps, a), pr, clamp(aT * 1.6 - 0.1), 5 + i];
                    } else if (i === 0) {
                        [x, y, sc, r, op, z] = [lerp(px, p.hero[0], b), lerp(py, p.hero[1], b), lerp(ps, 1, b), lerp(pr, -3, b), 1, 20];
                    } else {
                        const k = 1 + b * 1.5;
                        [x, y, sc, r, op, z] = [px * k, py * k, ps * (1 + b * 1.8), pr * (1 + b), clamp(1 - b * 1.25), 25];
                    }
                } else {
                    const d = cf - i;
                    const ad = Math.abs(d);
                    [x, y, r, sc, op, z] = [p.hero[0] - d * p.spacing, p.hero[1] + ad * p.dip, -3 - d * p.tilt, 1 - Math.min(ad, 1) * 0.12, clamp(1 - ad * 1.5), 20 - Math.round(ad * 4)];
                }
                card.style.transform = `translate(${(x * u).toFixed(1)}px, ${(y * u).toFixed(1)}px) rotate(${r.toFixed(2)}deg) scale(${sc.toFixed(3)})`;
                card.style.opacity = op.toFixed(3);
                card.style.zIndex = z;
            });

            panels.forEach((panel, i) => {
                let op;
                let ty;
                if (!inC) {
                    op = i === 0 ? clamp((s - (p.carouselFrom - 250)) / 250) : 0;
                    ty = (1 - op) * p.panelRise;
                } else {
                    op = clamp(1 - Math.abs(cf - i) * 2.4);
                    ty = (cf - i) * p.panelShift;
                }
                panel.style.opacity = op.toFixed(3);
                panel.style.transform = `translateY(${((reduced ? 0 : ty) * u).toFixed(1)}px)`;
            });

            if (intro) {
                intro.style.opacity = (clamp(aT * 2 - 0.3) * (1 - clamp(bT * 2))).toFixed(3);
                intro.style.transform = `translateY(${((reduced ? 0 : (1 - a) * p.introRise) * u).toFixed(1)}px)`;
            }

            if (stage && colours.length) {
                const i0 = Math.floor(cf);
                stage.style.background = inC
                    ? mix(colours[i0], colours[Math.min(last, i0 + 1)], cf - i0)
                    : mix(base, colours[0], b);
            }

            const active = inC ? Math.round(cf) : -1;
            if (dotBox) dotBox.style.opacity = s > p.carouselFrom - 100 ? '1' : '0';
            dots.forEach((dot, i) => {
                dot.style.height = `${(i === active ? p.dot[1] : p.dot[0]) * u}px`;
                dot.style.background = i === active ? 'var(--tk-green)' : 'rgba(255,255,255,0.3)';
            });
        },
    };
}
