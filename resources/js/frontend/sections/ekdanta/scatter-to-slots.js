/**
 * Scatter to slots: tiles pop up around a scatter area (in a seeded random
 * order), then fly in an arc onto their shelf, shelf by shelf. Each shelf's
 * count and fill bar follow the tiles that have landed. Under reduced
 * motion the tiles fade in already on their shelves.
 * Used by Brand Shelves (Home) and the Client Logo Wall (Work).
 * Markup: [data-m="tiles"] > tile (data-shelf="<index>"), [data-m="shelf-count"]
 * and [data-m="shelf-fill"] per shelf, in shelf order.
 */
import { clamp, lerp, inOutQuad as ease, easeOut } from '../../motion/math.js';

/** The design's generator (Park–Miller), so the scatter matches it exactly. */
function seeded(seed) {
    let s = seed;
    return () => {
        s = (s * 16807) % 2147483647;
        return (s - 1) / 2147483646;
    };
}

export default function scatterToSlots(el, { reduced, config, root }) {
    const p = {
        seed: 21, area: [200, 905, 300, 400], turn: 56, rankStep: 12, popFrom: -500, popFor: 200,
        flyFrom: 300, flyShelf: 70, flyItem: 28, flyFor: 450, arc: 80, bob: 6,
        shelves: [[96, 300], [418, 300], [740, 300], [1062, 300]], slot: [147, 58], scale: 1, fadeIn: [-300, 400],
        ...(config.scene?.params || {}),
    };
    const tiles = [...(root.querySelector('[data-m="tiles"]')?.children || [])];
    const counts = [...root.querySelectorAll('[data-m="shelf-count"]')];
    const fills = [...root.querySelectorAll('[data-m="shelf-fill"]')];

    // Layout, decided once: pop order, scatter spot and slot of every tile.
    const rnd = seeded(p.seed);
    const order = tiles.map((_, i) => i);
    for (let i = order.length - 1; i > 0; i--) {
        const j = Math.floor(rnd() * (i + 1));
        [order[i], order[j]] = [order[j], order[i]];
    }
    const rank = [];
    order.forEach((v, i) => { rank[v] = i; });
    const perShelf = [];
    const plan = tiles.map((tile) => {
        const shelf = Number(tile.dataset.shelf || 0);
        const j = perShelf[shelf] = (perShelf[shelf] ?? -1) + 1;
        const [sx0, sy0] = p.shelves[shelf % p.shelves.length];
        return {
            shelf, j,
            cx: p.area[0] + rnd() * p.area[1], cy: p.area[2] + rnd() * p.area[3], cr: (rnd() - 0.5) * p.turn,
            sx: sx0 + (j % 2) * p.slot[0], sy: sy0 + Math.floor(j / 2) * p.slot[1],
        };
    });
    const totals = perShelf.map((n) => n + 1);

    return {
        update(state, scene) {
            const u = scene.u;
            const s = state.px;
            const landed = totals.map(() => 0);

            tiles.forEach((tile, gi) => {
                const t = plan[gi];
                const pop = easeOut(clamp((s - p.popFrom - rank[gi] * p.rankStep) / p.popFor));
                const fly = ease(clamp((s - p.flyFrom - (t.shelf * p.flyShelf + t.j * p.flyItem)) / p.flyFor));
                if (fly > 0.5 || reduced) landed[t.shelf]++;
                let x;
                let y;
                let r = 0;
                let sc = p.scale;
                let op;
                if (reduced) {
                    [x, y] = [t.sx, t.sy];
                    op = clamp((s - p.fadeIn[0]) / p.fadeIn[1]);
                } else {
                    x = lerp(t.cx, t.sx, fly);
                    y = lerp(t.cy + Math.sin((s + gi * 50) / 120) * p.bob * (1 - fly), t.sy, fly) - Math.sin(Math.PI * fly) * p.arc;
                    r = lerp(t.cr, 0, fly);
                    sc = ((0.4 + 0.5 * pop) + 0.1 * fly) * p.scale;
                    op = pop;
                }
                const flying = !reduced && fly > 0.02 && fly < 0.98;
                tile.style.transform = `translate(${(x * u).toFixed(1)}px, ${(y * u).toFixed(1)}px) rotate(${r.toFixed(1)}deg) scale(${sc.toFixed(3)})`;
                tile.style.opacity = op.toFixed(3);
                tile.style.zIndex = flying ? 30 : 10;
            });

            counts.forEach((c, i) => { c.textContent = String(landed[i] ?? 0); });
            fills.forEach((f, i) => { f.style.transform = `scaleX(${((landed[i] ?? 0) / (totals[i] || 1)).toFixed(3)})`; });
        },
    };
}
