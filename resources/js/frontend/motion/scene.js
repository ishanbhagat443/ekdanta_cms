/**
 * One section's motion: its scroll progress, the tracks that animate its
 * parts, and its steps. The config comes from the server already resolved
 * (presets looked up, params filled in, sanitised): see App\Support\SectionMotion.
 *
 * Anime.js does the animating. Scroll-scrubbed tracks share ONE paused
 * timeline per scene, positioned in design px, so tracks that animate the
 * same property one after another hand over cleanly. This file only reads
 * the config, works out progress and calls seek().
 */
import { animate, createTimeline, cubicBezier, splitText, spring, stagger, steps, svg } from 'animejs';
import { clamp, inOutQuad } from './math.js';

/** Plain-number keyframe values for these properties are design px. */
const LENGTHS = new Set(['x', 'y', 'translateX', 'translateY', 'translateZ', 'left', 'top', 'right', 'bottom', 'width', 'height']);


/**
 * One design px in screen px: the composition's --u (registered as a length
 * in the Ekdanta styles, so it reads back resolved), else 0.
 */
export function unitOf(el) {
    return parseFloat(getComputedStyle(el).getPropertyValue('--u')) || 0;
}

/**
 * A section may draw one composition per screen size ([data-ek-comp]; tablets
 * show the tablet one, else the phone one): parts and the stage are looked
 * up in the one in view.
 */
export function compOf(el, device) {
    return (device === 'tablet' && el.querySelector('[data-ek-comp="tablet"]'))
        || el.querySelector(`[data-ek-comp="${device === 'tablet' ? 'mobile' : device}"]`) || el;
}

const DRAWN = { opacity: 1, scale: 1, scaleX: 1, scaleY: 1 }; // any other property: 0

/**
 * A scroll track that starts from the part as drawn and ends away from it
 * (it fades, moves or turns the part out): the part is whole before it.
 * One that ends at the drawn state brings the part in.
 */
export function leaves(track) {
    const props = Object.entries(track.keyframes || {}).filter(([k]) => !k.startsWith('$'));
    const drawn = (k, v) => parseFloat(v) === (DRAWN[k] ?? 0);
    return props.length > 0 && props.every(([k, v]) => Array.isArray(v) && drawn(k, v[0])) && props.some(([k, v]) => !drawn(k, v[v.length - 1]));
}

/** Design px → screen px, for plain numbers on length properties. */
export function scaleKeyframes(value, u, key = '') {
    if (Array.isArray(value)) return value.map((v) => scaleKeyframes(v, u, key));
    if (value && typeof value === 'object') {
        const out = {};
        for (const k of Object.keys(value)) {
            out[k] = scaleKeyframes(value[k], u, k === 'to' || k === 'from' ? key : k);
        }
        return out;
    }
    return typeof value === 'number' && LENGTHS.has(key) ? value * u : value;
}

/** A named ease, steps(n), cubicBezier(a,b,c,d) or spring(stiffness,damping). */
export function easeOf(name) {
    if (typeof name !== 'string' || name === '') return 'outQuad';
    const args = (/\(([^)]*)\)/.exec(name)?.[1] ?? '').split(',').map(Number);
    if (name.startsWith('steps(')) return steps(args[0] || 1);
    if (name.startsWith('cubicBezier(')) return cubicBezier(...args);
    if (name.startsWith('spring')) return spring({ mass: 1, stiffness: args[0] || 80, damping: args[1] || 10 });
    return name;
}

/**
 * Steps that pause on each item (the designs' snap()): a continuous index
 * f that holds still for the first `dwell` of each step, then eases on.
 * Reduced motion: no in-between, f is always whole.
 */
export function stepState(px, { range, dwell = 0.35, ease }, count, reduced = false) {
    const last = Math.max(1, count - 1);
    const raw = clamp((px - range[0]) / ((range[1] - range[0]) / last), 0, last);
    const i = Math.floor(raw);
    // ease "linear": an even glide from item to item, no pause.
    let f = ease === 'linear' ? raw : raw >= last ? last : i + inOutQuad(clamp((raw - i - dwell) / (1 - dwell)));
    if (reduced) f = Math.round(f);
    return { f, index: Math.round(f) };
}

/** Old [data-anim] stagger: an explicit [data-anim-children], else the first grid's children. */
function legacyChildren(el) {
    const marked = el.querySelectorAll('[data-anim-children] > *');
    if (marked.length) return [...marked];
    const row = el.querySelector('.row, .gsm-grid, [class*="grid"]');
    return row ? [...row.children] : [el];
}

const splits = new WeakMap();

/** Count-up: the real number stays in the DOM for screen readers; the ticking copy is hidden from them. */
function countTarget(el, locale) {
    // The real text, kept on the element: a rebuilt scene (refresh) must not
    // read the ticking copy it wrote last time.
    el.dataset.ekCount ??= el.textContent.trim();
    const text = el.dataset.ekCount;
    const m = /^(\D*)([\d.,]+)(.*)$/s.exec(text);
    if (!m) return null;
    const to = parseFloat(m[2].replace(/,/g, ''));
    const sr = document.createElement('span');
    sr.className = 'visually-hidden';
    sr.textContent = text;
    const shown = document.createElement('span');
    shown.setAttribute('aria-hidden', 'true');
    el.replaceChildren(sr, shown);
    const fmt = new Intl.NumberFormat(typeof locale === 'string' ? locale : 'en-IN');
    const box = { v: 0 };
    const draw = () => { shown.textContent = m[1] + fmt.format(Math.round(box.v)) + m[3]; };
    return { box, to, draw };
}

export class Scene {
    /**
     * @param {HTMLElement} el      the section
     * @param {object}      config  this device's resolved config
     * @param {object}      env     { device, reduced, u, factory }
     */
    constructor(el, config, env) {
        this.el = el;
        this.config = config;
        this.env = env;
        this.root = compOf(el, env.device);
        this.length = config.stage?.length || 0;
        // Reduced motion unpins a stage, unless it has steps: those still
        // need the scroll to show each item (they switch, without moving).
        // "step" tracks (reduce: 'step') switch in place at their point in the scroll.
        const stepping = (config.tracks || []).some((t) => t.reduce === 'step' && t.enabled !== false);
        this.pinned = !!config.stage?.pin && (!env.reduced || !!config.steps || stepping);
        this.switches = []; // reduced motion, reduce: 'step': { anim, at, on }
        this.focusEnd = new Map(); // element → px where its scrub tracks are done (D9)
        this.focusBefore = new Map(); // element → px where it starts to leave
        this.timeline = null;
        this.origin = 0;
        this.timed = []; // { track, anim, targets, run }
        this.abort = new AbortController();
        this.state = { px: 0, p: 0, f: 0, index: -1 };
        this.u = unitOf(this.root) || env.u;
        // The unit as a plain number, for CSS that can't use a length (scale(), offset-path frames).
        this.root.style?.setProperty('--u-n', String(this.u));
        this.build();
        this.measure();
    }

    targetsOf(track) {
        let els = track.part === '@self' ? [this.el] : [...this.root.querySelectorAll(`[data-m="${track.part}"]`)];
        if (track.children) els = els.flatMap((p) => (track.part === '@self' ? legacyChildren(p) : [...p.children]));
        const kf = track.keyframes || {};
        if (kf.$split) {
            els = els.flatMap((e) => {
                if (!splits.has(e)) splits.set(e, splitText(e, { words: true, chars: kf.$split === 'chars' }));
                return splits.get(e)[kf.$split] || [e];
            });
        }
        if ('draw' in kf) els = svg.createDrawable(els);
        return els;
    }

    /** Anime params for a track: its keyframes, minus our own $-markers, in screen px. */
    params(track) {
        const out = {};
        for (const [k, v] of Object.entries(track.keyframes || {})) if (!k.startsWith('$')) out[k] = v;
        return scaleKeyframes(out, this.u);
    }

    build() {
        const tracks = (this.config.tracks || []).filter((t) => t.enabled !== false);
        // Reduced motion: a scrub track marked reduce 'step' jumps from start
        // to end, item by item, at the middle of its range: no movement, and
        // what the design reveals one by one is still revealed one by one.
        if (this.env.reduced) {
            for (const t of tracks) {
                if (t.trigger !== 'scrub' || t.reduce !== 'step' || !t.keyframes || t.keyframes.$count) continue;
                this.targetsOf(t).forEach((e, i) => {
                    const anim = animate(e, { ...this.params(t), duration: 1, ease: 'linear', autoplay: false });
                    anim.seek(0);
                    this.switches.push({ anim, at: (t.range[0] + t.range[1]) / 2 + i * (t.stagger || 0), on: false });
                });
            }
        }
        // reduce 'start': a part that leaves as the visitor scrolls stays as drawn.
        const skip = (t) => this.env.reduced && ((t.reduce === 'step' && !t.keyframes.$count) || t.reduce === 'start');
        const scrub = tracks.filter((t) => t.trigger === 'scrub' && t.keyframes && !skip(t));

        for (const t of tracks) {
            if (this.env.reduced && t.reduce === 'hide') {
                this.targetsOf(t).forEach((e) => { e.style.visibility = 'hidden'; });
            }
        }

        if (scrub.length) {
            this.origin = Math.min(...scrub.map((t) => t.range[0]));
            // Reduced motion: the whole timeline (count-ups too) shows its final state, as the designs do.
            const tl = createTimeline({ autoplay: false, defaults: { ease: 'linear' } });
            for (const t of scrub) {
                const els = this.targetsOf(t);
                if (!els.length) continue;
                const span = Math.max(1, t.range[1] - t.range[0]);
                const at = t.range[0] - this.origin;
                const each = t.stagger || 0;
                if (t.keyframes.$count) {
                    els.map((e) => countTarget(e, t.keyframes.$count)).forEach((c, i) => {
                        if (c) tl.add(c.box, { v: [0, c.to], duration: span, ease: easeOf(t.ease), onUpdate: c.draw }, at + i * each);
                    });
                } else {
                    tl.add(els, { ...this.params(t), duration: span, ease: easeOf(t.ease), delay: each ? stagger(each) : 0 }, at);
                }
                els.forEach((e, i) => (leaves(t)
                    ? this.focusBefore.set(e, Math.min(this.focusBefore.get(e) ?? Infinity, t.range[0] + i * each))
                    : this.focusEnd.set(e, Math.max(this.focusEnd.get(e) ?? -Infinity, t.range[1] + i * each))));
            }
            // A child renders its start state only once the playhead has
            // passed it, so run to the end and back now (same frame, no paint).
            tl.seek(tl.duration);
            tl.seek(0);
            this.timeline = tl;
        }

        for (const t of tracks) {
            if (t.trigger === 'scrub') continue;
            const targets = this.targetsOf(t);
            if (!targets.length) continue;

            if (t.css) {
                // CSS @keyframes preset (load/loop motion, as the designs do it):
                // the engine only switches it on. Reduced motion: CSS turns it off.
                targets.forEach((e, i) => {
                    for (const [k, v] of Object.entries(t.vars || {})) e.style.setProperty(`--${k}`, String(v));
                    e.style.animation = `${t.css} ${t.duration ?? 600}ms ${t.ease || 'ease'} ${(t.delay || 0) + i * (t.stagger || 0)}ms ${t.trigger === 'loop' ? 'infinite' : 1} ${t.direction || 'normal'} both`;
                    if (t.trigger !== 'load' && t.trigger !== 'loop') e.style.animationPlayState = 'paused';
                });
                this.timed.push({ track: t, anim: null, targets });
                continue;
            }

            if (t.keyframes?.$count) {
                const counters = targets.map((e) => countTarget(e, t.keyframes.$count)).filter(Boolean);
                const anims = counters.map((c, i) => animate(c.box, { v: [0, c.to], duration: t.duration ?? 1200, delay: (t.delay || 0) + i * (t.stagger || 0), ease: easeOf(t.ease), autoplay: false, onUpdate: c.draw }));
                anims.forEach((a) => a.seek(0));
                this.timed.push({ track: t, anim: anims, targets });
                continue;
            }

            if (!t.keyframes) continue;
            const make = (els) => animate(els, {
                ...this.params(t),
                duration: t.duration ?? 700,
                delay: t.stagger ? stagger(t.stagger, { start: t.delay || 0 }) : t.delay || 0,
                ease: easeOf(t.ease),
                loop: t.trigger === 'loop',
                autoplay: false,
            });
            // Hover, click and enter animate each target on its own.
            const anim = ['hover', 'click', 'enter'].includes(t.trigger) ? targets.map((e) => make(e)) : [make(targets)];
            anim.forEach((a) => a.seek(0));
            this.timed.push({ track: t, anim, targets });
        }

        this.wire();
    }

    /** Hover/click/state/enter/load/loop: when each timed track runs. */
    wire() {
        const reduced = this.env.reduced;
        const on = { signal: this.abort.signal };
        this.observers = [];
        for (const item of this.timed) {
            const { track: t, anim, targets } = item;
            const run = (a, back = false) => {
                if (!a) {
                    targets.forEach((e) => { e.style.animationPlayState = back ? 'paused' : 'running'; });
                    return;
                }
                if (reduced) { a.seek(back ? 0 : a.duration); return; }
                back ? a.reverse() : (a.reversed ? a.play() : a.restart());
            };
            const all = anim || [null];
            item.run = run;

            if (t.trigger === 'load' || t.trigger === 'loop') {
                if (reduced) all.forEach((a) => a?.seek(t.trigger === 'loop' ? 0 : a.duration));
                else all.forEach((a) => a?.play());
            } else if (t.trigger === 'enter') {
                if (reduced) { all.forEach((a) => a && a.seek(a.duration)); continue; }
                // Each part plays as it comes into view (a tall grid fills in
                // row by row); parts that arrive together keep the stagger.
                const io = new IntersectionObserver((entries) => {
                    const shown = entries.filter((e) => e.isIntersecting).map((e) => targets.indexOf(e.target)).sort((x, y) => x - y);
                    shown.forEach((i, k) => {
                        setTimeout(() => run(all[i]), k * (t.stagger || 0));
                        if (t.once !== false) io.unobserve(targets[i]);
                    });
                }, { rootMargin: '0px 0px -80px 0px' });
                targets.forEach((e) => io.observe(e));
                this.observers.push(io);
            } else if (t.trigger === 'hover' || t.trigger === 'click') {
                targets.forEach((e, i) => {
                    const a = anim ? anim[i] : null;
                    if (t.trigger === 'hover') {
                        for (const type of ['mouseenter', 'focusin']) e.addEventListener(type, () => run(a), on);
                        for (const type of ['mouseleave', 'focusout']) e.addEventListener(type, () => run(a, true), on);
                    } else {
                        e.addEventListener('click', () => run(a), on);
                    }
                });
            }
        }
    }

    /** Geometry, read once here and on resize, never in the frame loop. */
    measure() {
        const box = this.el.getBoundingClientRect();
        this.top = box.top + window.scrollY;
        const stage = this.root.querySelector('[data-ek-stage]');
        const travel = this.pinned && stage ? this.el.offsetHeight - stage.offsetHeight : 0;
        // Screen px per design px: from the real pinned travel when there is
        // one (it already includes svh quirks), else from the screen height.
        this.scale = travel > 0 && this.length > 0 ? travel / this.length : window.innerHeight / (this.env.device === 'desktop' ? 900 : 844);
        this.travel = (travel > 0 ? travel : this.el.offsetHeight) || 1;
    }

    update(scrollY) {
        const px = (scrollY - this.top) / this.scale;
        const p = clamp((scrollY - this.top) / this.travel);
        const s = this.state;
        if (px === s.px && s.index !== -1) return;
        s.px = px;
        s.p = p;

        for (const sw of this.switches) {
            const on = px >= sw.at;
            if (on !== sw.on) {
                sw.on = on;
                sw.anim.seek(on ? sw.anim.duration : 0);
            }
        }

        if (this.timeline) {
            this.timeline.seek(this.env.reduced ? this.timeline.duration : clamp(px - this.origin, 0, this.timeline.duration));
        }

        const st = this.config.steps;
        if (st) {
            const part = st.part === '@self' ? this.root : this.root.querySelector(`[data-m="${st.part}"]`);
            const items = part ? [...part.children] : [];
            const { f, index } = stepState(px, st, items.length, this.env.reduced);
            s.f = f;
            this.el.style.setProperty('--ek-f', f.toFixed(4));
            if (index !== s.index) {
                s.index = index;
                this.root.querySelectorAll('[data-ek-step-num]').forEach((e) => {
                    e.textContent = String(index + 1).padStart(Number(e.dataset.ekStepNum) || 1, '0');
                });
                items.forEach((item, i) => {
                    item.classList.toggle('is-active', i === index);
                    if (i === index) item.setAttribute('aria-current', 'step');
                    else item.removeAttribute('aria-current');
                });
                for (const item of this.timed) {
                    if (item.track.trigger !== 'state') continue;
                    (item.anim || [null]).forEach((a) => item.run(a, item.track.step !== index));
                }
                this.el.dispatchEvent(new CustomEvent('ek:step', { detail: { index } }));
            }
        } else if (s.index === -1) {
            s.index = 0;
        }

        this.env.factory?.update?.(s, this);
    }

    /** Builder preview's play button: run the time-based tracks again. */
    replay() {
        for (const { anim, targets } of this.timed) {
            if (anim) {
                anim.forEach((a) => a.restart());
                continue;
            }
            targets.forEach((e) => {
                const css = e.style.animation;
                e.style.animation = 'none';
                void e.offsetWidth; // restart a CSS animation
                e.style.animation = css;
            });
        }
    }

    /** Pause/resume loops while the section is off screen. */
    setOnScreen(on) {
        this.el.classList.toggle('ek-offscreen', !on);
        for (const { track, anim } of this.timed) {
            if (track.trigger === 'loop' && anim && !this.env.reduced) anim.forEach((a) => (on ? a.play() : a.pause()));
        }
    }

    destroy() {
        this.abort.abort();
        this.observers.forEach((io) => io.disconnect());
        for (const { anim, targets } of this.timed) {
            anim?.forEach((a) => a.revert());
            if (!anim) targets.forEach((e) => { e.style.animation = ''; });
        }
        this.timeline?.revert();
        this.switches.forEach((sw) => sw.anim.revert());
        this.env.factory?.destroy?.();
    }
}
