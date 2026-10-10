/**
 * Service Deep Dive tiles. One function per tile artwork (the key is the
 * service's "tile" in Collections → Services): progress p (0–1) and reduced
 * motion → the values its artwork's data-b-* templates take. The math is
 * the design's own (Services.dc.html, deep dive), ported as it is.
 */
const clamp = (v, a = 0, b = 1) => Math.max(a, Math.min(b, v));
const lerp = (a, b, k) => a + (b - a) * k;
const ease = (k) => (k < 0.5 ? 2 * k * k : 1 - Math.pow(-2 * k + 2, 2) / 2);
const easeOut = (k) => 1 - Math.pow(1 - k, 3);
const easeBack = (k) => { const c1 = 1.70158, c3 = c1 + 1; return 1 + c3 * Math.pow(k - 1, 3) + c1 * Math.pow(k - 1, 2); };
const px = (v) => v.toFixed(1) + 'px';
const f3 = (v) => v.toFixed(3);
const pad = (n) => (n < 10 ? '0' : '') + n;

export const TILES = {
    'brand-strategy': (p, rm) => { const vals = {}; const move = (v) => (rm ? 0 : v); { const a = easeOut(clamp(p / 0.8)); const hit = clamp((p - 0.8) / 0.2); vals['ax'] = lerp(-150, 0, a).toFixed(1); vals['ring'] = (15 + 44 * hit).toFixed(1); vals['ringOp'] = f3(p > 0.8 ? 1 - hit : 0); } return vals; },
    'brand-identity': (p, rm) => { const vals = {}; const move = (v) => (rm ? 0 : v); vals['off'] = (1 - ease(clamp(p / 0.7))).toFixed(3); vals['imgOp'] = f3(clamp((p - 0.45) / 0.3)); vals['imgSc'] = (0.6 + 0.4 * easeBack(clamp((p - 0.45) / 0.45))).toFixed(3); return vals; },
    'visual-identity': (p, rm) => { const vals = {}; const move = (v) => (rm ? 0 : v); for (let j = 0; j < 4; j++) { const q = clamp((p - j * 0.15) / 0.4); vals['s' + j] = px(move(-110 * (1 - easeBack(q)))); vals['o' + j] = f3(clamp(q * 3)); } return vals; },
    'brand-guidelines': (p, rm) => { const vals = {}; const move = (v) => (rm ? 0 : v); { const aa = clamp((p - 0.15) / 0.45); vals['aaOp'] = f3(aa); vals['aaTy'] = px(move((1 - easeOut(aa)) * 30)); vals['txOp'] = f3(clamp((p - 0.45) / 0.35)); vals['w0'] = px(214 * ease(clamp(p / 0.5))); vals['w1'] = px(150 * ease(clamp((p - 0.15) / 0.5))); vals['w2'] = px(180 * ease(clamp((p - 0.3) / 0.5))); } return vals; },
    'packaging-design': (p, rm) => { const vals = {}; const move = (v) => (rm ? 0 : v); vals['ty'] = move(90 * (1 - easeOut(clamp(p / 0.45)))).toFixed(1); vals['lid'] = lerp(-75, 0, ease(clamp((p - 0.45) / 0.45))).toFixed(1); return vals; },
    'brand-positioning': (p, rm) => { const vals = {}; const move = (v) => (rm ? 0 : v); { const e = ease(clamp((p - 0.15) / 0.7)); vals['g'] = 'translate(' + (62 * e).toFixed(1) + ' ' + (-38 * e).toFixed(1) + ')'; vals['lblOp'] = f3(clamp((p - 0.7) / 0.3)); } return vals; },
    'social-media-management': (p, rm) => { const vals = {}; const move = (v) => (rm ? 0 : v); for (let j = 0; j < 9; j++) vals['q' + j] = f3(clamp((p - j * 0.08) / 0.12)); vals['heartOp'] = f3(clamp((p - 0.85) / 0.15)); return vals; },
    'performance-marketing': (p, rm) => { const vals = {}; const move = (v) => (rm ? 0 : v); vals['off'] = (1 - clamp(p / 0.85)).toFixed(3); vals['area'] = (0.22 * clamp((p - 0.3) / 0.6)).toFixed(3); vals['dot'] = f3(clamp((p - 0.85) / 0.15)); return vals; },
    'meta-ads': (p, rm) => { const vals = {}; const move = (v) => (rm ? 0 : v); { const c = ease(clamp((p - 0.35) / 0.4)); const rp = clamp((p - 0.78) / 0.22); vals['ty'] = px(move((1 - easeOut(clamp(p / 0.4))) * 70)); vals['cur'] = 'translate(' + px(move(lerp(90, 0, c))) + ', ' + px(move(lerp(-40, 0, c))) + ')'; vals['tapOp'] = f3(p > 0.78 && !rm ? 1 - rp : 0); vals['tapSc'] = (0.4 + 1.3 * rp).toFixed(3); vals['btn'] = p > 0.8 ? '#8FE3AE' : '#2EB45D'; } return vals; },
    'lead-generation': (p, rm) => { const vals = {}; const move = (v) => (rm ? 0 : v); [96, 70, 84].forEach((w, j) => { vals['f' + j] = px(w * ease(clamp((p - j * 0.22) / 0.25))); vals['l' + j] = '1'; }); vals['toastOp'] = f3(clamp((p - 0.75) / 0.15)); vals['toastTy'] = px(move((1 - easeOut(clamp((p - 0.75) / 0.25))) * 16)); return vals; },
    'seo': (p, rm) => { const vals = {}; const move = (v) => (rm ? 0 : v); { const sw = ease(clamp((p - 0.5) / 0.4)); vals['typeW'] = px(152 * clamp(p / 0.45)); vals['g'] = px(-52 * sw); vals['a'] = px(26 * sw); vals['b'] = px(26 * sw); vals['rank'] = sw > 0.5 ? '#1' : '#3'; } return vals; },
    'website-development': (p, rm) => { const vals = {}; const move = (v) => (rm ? 0 : v); for (let j = 0; j < 4; j++) vals['b' + j] = f3(clamp((p - j * 0.2) / 0.25)); return vals; },
    'lead-funnel-strategy': (p, rm) => { const vals = {}; const move = (v) => (rm ? 0 : v); for (let j = 0; j < 3; j++) vals['l' + j] = easeOut(clamp((p - j * 0.15) / 0.35)).toFixed(3); vals['txt'] = f3(clamp((p - 0.4) / 0.3)); vals['dots'] = f3(clamp((p - 0.55) / 0.3)); return vals; },
    'sales-funnel-design': (p, rm) => { const vals = {}; const move = (v) => (rm ? 0 : v); [150, 112, 76, 44].forEach((w, j) => vals['w' + j] = px(w * ease(clamp((p - j * 0.18) / 0.3)))); return vals; },
    'lead-nurturing': (p, rm) => { const vals = {}; const move = (v) => (rm ? 0 : v); for (let j = 0; j < 3; j++) { const o = clamp((p - j * 0.28) / 0.15); vals['o' + j] = f3(o); vals['y' + j] = px(move((1 - easeOut(o)) * 14)); } vals['tick'] = f3(clamp((p - 0.88) / 0.12)); return vals; },
    'sales-consulting': (p, rm) => { const vals = {}; const move = (v) => (rm ? 0 : v); for (let j = 0; j < 3; j++) { vals['o' + j] = f3(0.45 + 0.55 * clamp((p - j * 0.3) / 0.1)); vals['c' + j] = (1 - clamp((p - j * 0.3) / 0.2)).toFixed(3); } return vals; },
    'crm-and-app-development': (p, rm) => { const vals = {}; const move = (v) => (rm ? 0 : v); vals['x'] = px(80 * ease(clamp(p / 0.4)) + 80 * ease(clamp((p - 0.5) / 0.4))); vals['wonBg'] = p > 0.9 ? '#D9F2E1' : '#F4F6F1'; return vals; },
    'conversion-optimisation': (p, rm) => { const vals = {}; const move = (v) => (rm ? 0 : v); { const ah = lerp(8, 44, ease(clamp(p / 0.6))), bh = lerp(8, 86, ease(clamp((p - 0.1) / 0.7))); vals['ah'] = ah.toFixed(1); vals['ay'] = (124 - ah).toFixed(1); vals['bh'] = bh.toFixed(1); vals['by'] = (124 - bh).toFixed(1); vals['win'] = f3(clamp((p - 0.8) / 0.2)); } return vals; },
    'brand-and-corporate-films': (p, rm) => { const vals = {}; const move = (v) => (rm ? 0 : v); { const fr = Math.floor(p * 12 * 25); vals['sc'] = (1.12 - 0.12 * easeOut(clamp(p / 0.4))).toFixed(3); vals['tc'] = '00:00:' + pad(Math.floor(fr / 25)) + ':' + pad(fr % 25); } return vals; },
    'product-photography': (p, rm) => { const vals = {}; const move = (v) => (rm ? 0 : v); vals['ph'] = (50 * (1 - easeOut(clamp((p - 0.55) / 0.4)))).toFixed(1); vals['flash'] = f3(rm ? 0 : Math.max(0, 1 - Math.abs(p - 0.5) / 0.08) * 0.9); return vals; },
    'product-videos': (p, rm) => { const vals = {}; const move = (v) => (rm ? 0 : v); { const k = clamp((p - 0.15) / 0.85); vals['bar'] = px(190 * k); vals['play'] = f3(1 - clamp((p - 0.05) / 0.15)); vals['time'] = '0:' + pad(Math.round(30 * k)) + ' / 0:30'; } return vals; },
    'motion-graphics': (p, rm) => { const vals = {}; const move = (v) => (rm ? 0 : v); { const e = [0, 1, 2].map((j) => easeBack(clamp((p - j * 0.15) / 0.45))); vals['c'] = move(-110 * (1 - e[0])).toFixed(1); vals['s'] = move(-100 * (1 - e[1])).toFixed(1); vals['r'] = (rm ? 0 : 180 * p).toFixed(1); vals['t'] = move(110 * (1 - e[2])).toFixed(1); } return vals; },
    'ugc-content': (p, rm) => { const vals = {}; const move = (v) => (rm ? 0 : v); vals['cap'] = f3(clamp((p - 0.3) / 0.3)); vals['heartOp'] = f3(clamp((p - 0.5) / 0.3)); return vals; },
    'influencer-and-model-shoots': (p, rm) => { const vals = {}; const move = (v) => (rm ? 0 : v); vals['arm'] = (rm ? 0 : p < 0.5 ? -28 * easeOut(clamp(p / 0.3)) : -28 * (1 - clamp((p - 0.5) / 0.07))).toFixed(1); vals['burst'] = f3(p > 0.57 && !rm ? 1 - clamp((p - 0.6) / 0.3) : 0); return vals; },
};

/**
 * Fill a tile's bound attributes: an element lists them in data-b ("r opacity
 * text"), each with its template in data-b-<name> ("{ring}", "translate({ax} 0)").
 * On HTML elements a value in px is design px, so it becomes --u units.
 */
export function applyTile(tile, values) {
    for (const el of tile.querySelectorAll('[data-b]')) {
        const svg = el instanceof SVGElement;
        for (const name of el.dataset.b.split(' ')) {
            let v = el.getAttribute(`data-b-${name}`).replace(/\{(\w+)\}/g, (_, k) => values[k] ?? '');
            if (!svg && name === 'style') v = v.replace(/(-?\d+(?:\.\d+)?)px/g, 'calc($1 * var(--u))');
            if (name === 'text') el.textContent = v;
            else el.setAttribute(name, v);
        }
    }
}
