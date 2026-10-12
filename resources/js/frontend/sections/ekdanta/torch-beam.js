/**
 * Ekdanta · Home Hero (Torch): the beam opens over `openSeconds`, or sooner
 * as the visitor scrolls (whichever is further along), then the buttons
 * appear and the scroll hint fades in. Writes CSS variables the view's
 * clip-path and styles read. Numbers: Motion tab → Scene settings.
 */
import { clamp, inOutQuad } from '../../motion/math.js';

export const DEFAULTS = {
    openSeconds: 10, scrollOpen: 600, spreadA: -2400, spreadB: 3200, ctaAt: 0.8, ctaSpan: 0.12, ctaRise: 30,
    glowFrom: 0.55, hintAt: 560, hintFadeAt: 650, hintFadeSpan: 150,
};

export default function torchBeam(el, { reduced, config, root }) {
    const p = { ...DEFAULTS, ...(config.scene?.params || {}) };
    let t0 = performance.now();
    let y = 0;
    let raf = 0;
    let started = false;

    const draw = () => {
        const it = (performance.now() - t0) / 1000;
        const prog = reduced ? 1 : Math.max(clamp(it / p.openSeconds), clamp(y / p.scrollOpen));
        const open = inOutQuad(prog);
        const done = reduced ? 1 : Math.max(clamp((it - p.openSeconds) / 0.6), clamp((y - p.hintAt) / 100));
        const s = root.style;
        s.setProperty('--bk', (1 - open).toFixed(4));
        s.setProperty('--spa', (p.spreadA * open).toFixed(1));
        s.setProperty('--spb', (p.spreadB * open).toFixed(1));
        s.setProperty('--glow', (p.glowFrom + (1 - p.glowFrom) * prog).toFixed(3));
        s.setProperty('--cta', clamp((prog - p.ctaAt) / p.ctaSpan).toFixed(3));
        s.setProperty('--cta-rise', String(p.ctaRise));
        s.setProperty('--hint', (done * (1 - clamp((y - p.hintFadeAt) / p.hintFadeSpan))).toFixed(3));
        return it;
    };

    const intro = () => {
        raf = draw() < p.openSeconds + 0.9 ? requestAnimationFrame(intro) : 0;
    };

    return {
        update(state) {
            y = state.px;
            if (!started) {
                started = true;
                // Arriving already scrolled down: the intro has been and gone.
                if (y > 0) t0 -= (p.openSeconds + 1) * 1000;
                if (!reduced) raf = requestAnimationFrame(intro);
            }
            draw();
        },
        destroy() {
            cancelAnimationFrame(raf);
        },
    };
}
