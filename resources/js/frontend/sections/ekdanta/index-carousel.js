/**
 * Index carousel: each item placed by its distance d = i − f from the
 * current index f (the engine's steps, with the design's pause on each
 * item): sideways by d, turned and shrunk with |d|, faded past fadeFrom.
 * Items also "deal in" from below as the section arrives. Captions follow
 * the index; an optional counter shows it. The current item gets
 * is-active from the steps (its ring is CSS).
 * Used by Reels Carousel (Home), Proof Carousel (Services) and Reels Arc (Work).
 */
import { clamp, inOutQuad as ease } from '../../motion/math.js';

export default function indexCarousel(el, { reduced, config, root }) {
    const p = {
        items: 'reel-cards', captions: 'reel-caps', count: 'reel-count',
        spacing: 300, tilt: 24, shrink: 0.16, dip: 14, depth: 2, fadeFrom: 1.2, arc: 0,
        dealFrom: -700, dealFor: 800, dealStagger: 90, dealRise: 520,
        capShift: -16, capFade: 2.5,
        ...(config.scene?.params || {}),
    };
    const items = [...(root.querySelector(`[data-m="${p.items}"]`)?.children || [])];
    const caps = [...(root.querySelector(`[data-m="${p.captions}"]`)?.children || [])];
    const count = root.querySelector(`[data-m="${p.count}"]`);

    return {
        update(state, scene) {
            const u = scene.u;
            const f = state.f;

            items.forEach((item, i) => {
                const ti = reduced ? 1 : ease(clamp((state.px - p.dealFrom - i * p.dealStagger) / p.dealFor));
                const d = i - f;
                const ad = Math.abs(d);
                const x = d * p.spacing;
                const y = (1 - ti) * p.dealRise + Math.min(ad, p.depth) * p.dip + ad * ad * p.arc;
                const turn = -clamp(d, -1, 1) * p.tilt;
                const scale = 1 - Math.min(ad, p.depth) * p.shrink;
                item.style.transform = `translate(${(x * u).toFixed(1)}px, ${(y * u).toFixed(1)}px) rotateY(${turn.toFixed(1)}deg) scale(${scale.toFixed(3)})`;
                item.style.opacity = (clamp(1 - Math.max(0, ad - p.fadeFrom)) * clamp(ti * 1.4)).toFixed(3);
                item.style.zIndex = 20 - Math.round(ad * 4);
            });

            caps.forEach((cap, i) => {
                cap.style.opacity = clamp(1 - Math.abs(f - i) * p.capFade).toFixed(3);
                cap.style.transform = `translateY(${(reduced ? 0 : (f - i) * p.capShift * u).toFixed(1)}px)`;
            });

            if (count) count.textContent = String(Math.round(f) + 1).padStart(2, '0');
        },
    };
}
