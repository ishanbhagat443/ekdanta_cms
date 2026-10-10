/**
 * EkMotion: runs every section's motion (Motion tab → <script data-ek-config>)
 * and the older [data-anim] scroll-in effects, on Anime.js v4.
 *
 *   window.EkMotion.register('section-type', factory)  hand-coded scene; factory(el, ctx)
 *                                                       returns { update(state, scene), destroy() }
 *   window.EkMotion.get(sectionEl)        → { progress, px, step, f }
 *   window.EkMotion.refresh()             re-read every config (builder preview, device change)
 *   window.EkMotion.scrollToProgress(sectionEl, p)
 *   window.EkMotion.pause(true|false)     the visitor's "Pause animations" choice
 *
 * Markup hooks: [data-m="part"] parts, [data-ek-stage] the pinned stage,
 * [data-ek-step="2"] tap to jump to a step ("+1"/"-1": next/previous;
 *   with data-ek-target="#anchor", a step of that section),
 * [data-ek-step-num="2"] shows the current step number (2 digits),
 * [data-ek-pause] the pause button.
 */
import { Scene, unitOf } from './scene.js';

const root = document.documentElement;
const factories = new Map();
const scenes = new Map();
const onScreen = new Set();
const PAUSED = 'ek-motion-paused';
let device = '';
let ticking = false;
let screens = null;

/** The 9 entrance effects older pages store as [data-anim] attributes ("px": screen px, as before). */
const ENTRANCE = {
    'fade-in': { opacity: [0, 1] },
    'fade-up': { opacity: [0, 1], translateY: ['32px', 0] },
    'fade-down': { opacity: [0, 1], translateY: ['-32px', 0] },
    'fade-left': { opacity: [0, 1], translateX: ['-40px', 0] },
    'fade-right': { opacity: [0, 1], translateX: ['40px', 0] },
    'zoom-in': { opacity: [0, 1], scale: [0.94, 1] },
    'zoom-out': { opacity: [0, 1], scale: [1.06, 1] },
    'flip-up': { opacity: [0, 1], rotateX: [-28, 0], transformPerspective: 800 },
};

const deviceOf = () => (innerWidth >= 992 ? 'desktop' : innerWidth >= 768 ? 'tablet' : 'mobile');

/** Size of one design px (the CSS --u): 1440 × 900 desktop, 390 × 844 phone, tablets capped. */
const deviceUnit = (d) => (d === 'desktop'
    ? Math.min(innerWidth / 1440, innerHeight / 900)
    : Math.min(innerWidth / 390, innerHeight / 844, d === 'tablet' ? 1.35 : Infinity));

const isReduced = () => root.getAttribute('data-motion') === 'off'
    || (typeof matchMedia === 'function' && matchMedia('(prefers-reduced-motion: reduce)').matches);

function configFor(el, d) {
    const json = el.querySelector(':scope > script[data-ek-config]');
    if (json) {
        const all = JSON.parse(json.textContent);
        return all[d] || all.desktop || {};
    }
    // [data-anim]: one "enter" track on the element itself.
    const pre = d !== 'desktop' && el.hasAttribute(`data-anim-${d}`) ? `data-anim-${d}` : 'data-anim';
    const num = (k, fallback) => {
        const n = parseInt(el.getAttribute(`${pre}-${k}`), 10);
        return Number.isNaN(n) ? fallback : n;
    };
    const keyframes = ENTRANCE[el.getAttribute(pre)];
    if (!keyframes) return {};
    const each = num('stagger', 0);
    return {
        tracks: [{
            part: '@self', children: each > 0, trigger: 'enter', keyframes, stagger: each,
            duration: num('duration', 700), delay: num('delay', 0),
            ease: el.getAttribute(`${pre}-ease`) || 'outQuad', once: el.getAttribute(`${pre}-once`) !== '0',
        }],
    };
}

/** Never leave anything hidden because motion failed. */
function reveal(el) {
    el.style.opacity = '';
    el.style.transform = '';
    el.classList.add('ek-revealed');
    el.setAttribute('data-anim-done', '');
}

function build() {
    device = deviceOf();
    const reduced = isReduced();
    const u = deviceUnit(device);

    document.querySelectorAll('[data-ek-motion], [data-anim]').forEach((el) => {
        if (scenes.has(el)) return;
        try {
            const config = configFor(el, device);
            if (config.off) {
                reveal(el);
                return;
            }
            const make = factories.get(el.getAttribute('data-ek-type'));
            const factory = make ? make(el, { device, reduced, u, config }) : null;
            scenes.set(el, new Scene(el, config, { device, reduced, u, factory }));
            screens.observe(el);
            // The pre-script hiding (server CSS, [data-anim] CSS) ends here:
            // Anime's inline start states rule now, and a part a track could
            // not find is never left hidden.
            el.classList.add('ek-revealed');
            el.setAttribute('data-anim-done', '');
        } catch (err) {
            reveal(el);
            console.warn('[EkMotion] showing this section without motion:', err);
        }
    });

    root.classList.add('ek-ready');
    tick();
}

function tick() {
    ticking = false;
    const y = window.scrollY;
    onScreen.forEach((el) => scenes.get(el)?.update(y));
}

function requestTick() {
    if (!ticking) {
        ticking = true;
        requestAnimationFrame(tick);
    }
}

function refresh() {
    scenes.forEach((scene) => scene.destroy());
    scenes.clear();
    onScreen.clear();
    screens?.disconnect();
    build();
    syncPauseButtons();
}

function scrollToPx(scene, px, instant = false) {
    window.scrollTo({ top: scene.top + px * scene.scale, behavior: instant || isReduced() ? 'auto' : 'smooth' });
}

function scrollToProgress(el, p, { instant = false } = {}) {
    const scene = scenes.get(el);
    if (scene) window.scrollTo({ top: scene.top + p * scene.travel, behavior: instant || isReduced() ? 'auto' : 'smooth' });
}

/** Design px where step i is fully in place. */
const stepPx = (st, i, count) => st.range[0] + i * ((st.range[1] - st.range[0]) / Math.max(1, count - 1));

function syncPauseButtons() {
    const paused = root.getAttribute('data-motion') === 'off';
    document.querySelectorAll('[data-ek-pause]').forEach((button) => {
        // The site switch or ?reduce-motion=1 already decided for everyone.
        button.hidden = root.hasAttribute('data-motion-forced');
        button.setAttribute('aria-pressed', paused ? 'true' : 'false');
    });
}

function pause(on) {
    if (root.hasAttribute('data-motion-forced')) return;
    try {
        on ? localStorage.setItem(PAUSED, '1') : localStorage.removeItem(PAUSED);
    } catch (e) { /* private mode: the choice lasts for this page only */ }
    on ? root.setAttribute('data-motion', 'off') : root.removeAttribute('data-motion');
    refresh();
}

function boot() {
    screens = new IntersectionObserver((entries) => {
        entries.forEach(({ target, isIntersecting }) => {
            isIntersecting ? onScreen.add(target) : onScreen.delete(target);
            scenes.get(target)?.setOnScreen(isIntersecting);
        });
        requestTick();
    }, { rootMargin: '50% 0px' });

    build();
    syncPauseButtons();

    addEventListener('scroll', requestTick, { passive: true });

    let resizing = 0;
    const remeasure = () => {
        clearTimeout(resizing);
        resizing = setTimeout(() => {
            if (deviceOf() !== device) return refresh();
            // A new design unit means every built distance is stale: rebuild.
            for (const scene of scenes.values()) {
                const u = unitOf(scene.root);
                if (u && Math.abs(u - scene.u) / scene.u > 0.01) return refresh();
                scene.measure();
            }
            tick();
        }, 150);
    };
    addEventListener('resize', remeasure);
    // Images and fonts loading above a section move it: re-read positions.
    new ResizeObserver(remeasure).observe(document.body);
    matchMedia('(prefers-reduced-motion: reduce)').addEventListener?.('change', refresh);

    document.addEventListener('click', (e) => {
        if (e.target.closest('[data-ek-pause]')) {
            pause(root.getAttribute('data-motion') !== 'off');
            return;
        }
        // Tap to jump: <button data-ek-step="2"> inside a section with steps;
        // "+1" / "-1" go to the next / previous step.
        const jump = e.target.closest('[data-ek-step]');
        // data-ek-target="#s-deep": the steps of another section (its anchor).
        const host = jump?.dataset.ekTarget ? document.querySelector(jump.dataset.ekTarget)?.closest('[data-ek-motion]') : jump?.closest('[data-ek-motion]');
        const scene = host && scenes.get(host);
        const st = scene?.config.steps;
        if (!st) return;
        e.preventDefault(); // a link to the section's anchor is the no-JS fallback
        const part = scene.root.querySelector(`[data-m="${st.part}"]`);
        const count = part ? part.children.length : 1;
        const raw = jump.dataset.ekStep;
        const to = /^[+-]/.test(raw) ? Math.max(0, scene.state.index) + Number(raw) : Number(raw);
        scrollToPx(scene, stepPx(st, Math.max(0, Math.min(count - 1, to)), count));
    });

    // Keyboard focus never lands on a part that is still hidden in a pinned
    // stage: scroll the scene on to where that part is shown (or its step is).
    document.addEventListener('focusin', (e) => {
        const el = e.target.closest('[data-ek-motion]');
        const scene = el && scenes.get(el);
        if (!scene?.pinned) return;
        let need = -Infinity;
        for (let n = e.target; n && n !== el.parentElement; n = n.parentElement) {
            if (scene.focusEnd.has(n)) need = Math.max(need, scene.focusEnd.get(n));
        }
        const st = scene.config.steps;
        const part = st && scene.root.querySelector(`[data-m="${st.part}"]`);
        const item = part && [...part.children].findIndex((c) => c.contains(e.target));
        if (item >= 0 && item !== scene.state.index) need = Math.max(need, stepPx(st, item, part.children.length));
        if (need > scene.state.px) scrollToPx(scene, need, true);
    });

    // Builder preview: the Motion tab's scroll slider and play button.
    if (window.parent !== window) {
        addEventListener('message', (e) => {
            if (e.origin !== location.origin || !e.data || typeof e.data !== 'object') return;
            const el = document.getElementById(e.data.section);
            if (!el) return;
            if (e.data.type === 'ek-motion-seek') scrollToProgress(el, Number(e.data.p) || 0, { instant: true });
            if (e.data.type === 'ek-motion-play') scenes.get(el)?.replay();
        });
    }
}

window.EkMotion = {
    register(type, factory) {
        factories.set(type, factory);
        if (screens) refresh();
    },
    get(el) {
        const s = scenes.get(el)?.state;
        return s ? { progress: s.p, px: s.px, step: s.index, f: s.f } : null;
    },
    refresh,
    scrollToProgress,
    pause,
};

if (typeof IntersectionObserver === 'undefined') {
    document.querySelectorAll('[data-ek-motion], [data-anim]').forEach(reveal);
} else {
    boot();
}
