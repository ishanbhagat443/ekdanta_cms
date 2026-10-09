/**
 * Scroll animations, driven entirely by data-* attributes.
 *
 * The CMS writes `data-anim="fade-up" data-anim-duration="700" …` onto a
 * section wrapper (see App\Support\SectionAnimation) and this file turns that
 * into an Anime.js v4 animation. Admins never write JavaScript; there is no
 * per-section script to go stale.
 *
 * Contract with the CSS: an element carrying [data-anim] starts hidden ONLY
 * when <html> has the `js-enabled` class. If this script never runs — no JS,
 * a CDN blocked, an old browser — the content is simply visible.
 *
 * Requires anime.umd.min.js (global `anime`) to be loaded first.
 */
(function () {
    'use strict';

    var TABLET_MAX = 991; // matches Bootstrap's lg breakpoint, as the CMS does
    var MOBILE_MAX = 767;

    /** transform + opacity only: anything else reflows and janks on phones. */
    var PRESETS = {
        'fade-in': { opacity: [0, 1] },
        'fade-up': { opacity: [0, 1], translateY: [32, 0] },
        'fade-down': { opacity: [0, 1], translateY: [-32, 0] },
        'fade-left': { opacity: [0, 1], translateX: [-40, 0] },
        'fade-right': { opacity: [0, 1], translateX: [40, 0] },
        'zoom-in': { opacity: [0, 1], scale: [0.94, 1] },
        'zoom-out': { opacity: [0, 1], scale: [1.06, 1] },
        'flip-up': { opacity: [0, 1], rotateX: [-28, 0], transformPerspective: 800 }
    };

    var EASES = {
        outQuad: 'out(2)',
        outCubic: 'out(3)',
        outExpo: 'outExpo',
        outBack: 'outBack',
        inOutQuad: 'inOut(2)',
        // v3's 'spring(1, 80, 10, 0)' string means nothing to v4, which
        // silently runs it linear. A function, because anime may not be
        // loaded yet when this map is built (see the note in init()).
        spring: function () { return anime.spring({ mass: 1, stiffness: 80, damping: 10, velocity: 0 }); }
    };

    function device() {
        var width = window.innerWidth;

        if (width <= MOBILE_MAX) return 'mobile';
        if (width <= TABLET_MAX) return 'tablet';

        return 'desktop';
    }

    /** Read one device's settings, falling back to the desktop attributes. */
    function settingsFor(el, name) {
        var prefix = name === 'desktop' ? 'data-anim' : 'data-anim-' + name;
        var preset = el.getAttribute(prefix);

        if (preset === null) {
            prefix = 'data-anim';
            preset = el.getAttribute(prefix);
        }

        var num = function (suffix, fallback) {
            var raw = parseInt(el.getAttribute(prefix + '-' + suffix), 10);

            return isNaN(raw) ? fallback : raw;
        };

        return {
            preset: preset || 'none',
            duration: num('duration', 700),
            delay: num('delay', 0),
            ease: EASES[el.getAttribute(prefix + '-ease')] || EASES.outQuad,
            stagger: num('stagger', 0),
            once: el.getAttribute(prefix + '-once') !== '0'
        };
    }

    /**
     * What actually moves.
     *
     * With stagger on, the section's own children animate one after another —
     * the "cards appear in sequence" effect — and the section itself just
     * becomes visible. With stagger off, the section moves as one piece.
     */
    function targetsFor(el, settings) {
        if (!settings.stagger) return el;

        var children = el.querySelectorAll('[data-anim-children] > *');

        if (!children.length) {
            // No explicit opt-in: use the deepest single wrapper's children,
            // which is what a section's grid or row almost always is.
            var row = el.querySelector('.row, .gsm-grid, [class*="grid"]');
            children = row ? row.children : [];
        }

        return children.length ? children : el;
    }

    function reveal(el) {
        el.style.opacity = '';
        el.style.transform = '';
        el.setAttribute('data-anim-done', '');
    }

    /**
     * Build one section's animation, paused and ready to play.
     *
     * Returns null when there is nothing to animate; the section has already
     * been revealed by the time that happens.
     */
    function build(el) {
        var settings = settingsFor(el, device());

        if (settings.preset === 'none' || !PRESETS[settings.preset]) {
            reveal(el);

            return null;
        }

        var targets = targetsFor(el, settings);
        var staggered = targets !== el && settings.stagger > 0;

        // When children carry the motion, the section itself must not stay
        // hidden behind them.
        if (staggered) reveal(el);

        var params = Object.assign({}, PRESETS[settings.preset], {
            duration: settings.duration,
            ease: typeof settings.ease === 'function' ? settings.ease() : settings.ease,
            delay: staggered
                ? anime.stagger(settings.stagger, { start: settings.delay })
                : settings.delay,
            // This file decides when a section plays (see watch()), not the
            // library. anime.onScroll() was doing that job and could not: the
            // observer's `sync` option is what actually connects "came into
            // view" to "play", so an observer built with sync off tracked
            // visibility and never started anything — leaving every animated
            // section, the hero above all, stuck at opacity 0.
            autoplay: false,
            onComplete: function () { el.setAttribute('data-anim-done', ''); }
        });

        return anime.animate(targets, params);
    }

    /** Sections already built, so a replay can reuse the same animation. */
    var running = new Map();

    /**
     * Play — or replay — one section.
     *
     * Wrapped end to end: the CSS keeps a [data-anim] section hidden until
     * this file says otherwise, so ANY failure in here — a library change, a
     * malformed attribute, a browser quirk — must still end with the content
     * on screen. An invisible page is a far worse outcome than a missing
     * animation.
     */
    function play(el) {
        // Already shown by the failsafe below: leave it alone rather than
        // fade it in a second time under the reader.
        if (el.hasAttribute('data-anim-done') && !running.has(el)) return;

        try {
            if (running.has(el)) {
                running.get(el).restart();

                return;
            }

            var animation = build(el);

            if (!animation) return;

            running.set(el, animation);
            el.setAttribute('data-anim-started', '');
            animation.play();
        } catch (err) {
            reveal(el);

            if (window.console && console.warn) {
                console.warn('[gsm-animations] falling back to no animation:', err);
            }
        }
    }

    function inViewport(el) {
        var box = el.getBoundingClientRect();
        var height = window.innerHeight || document.documentElement.clientHeight;

        return box.top < height && box.bottom > 0;
    }

    /**
     * Decide when each section plays.
     *
     * An IntersectionObserver reports what is ALREADY on screen as well as
     * what later scrolls onto it, so the hero at the top of the page and a
     * section four screens down take exactly the same path. Sections set to
     * play once are dropped after their first reveal; the rest keep their
     * subscription and replay on every return.
     */
    function watch(sections) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;

                play(entry.target);

                if (settingsFor(entry.target, device()).once) {
                    observer.unobserve(entry.target);
                }
            });
        }, {
            // Stands in for the old 'bottom-=80 top' trigger: a section starts
            // moving once 80px of it has cleared the fold.
            rootMargin: '0px 0px -80px 0px'
        });

        Array.prototype.forEach.call(sections, function (el) { observer.observe(el); });
    }

    function libraryReady() {
        return typeof anime !== 'undefined' && !!anime.animate;
    }

    /** How long to wait for the CDN before showing the content unanimated. */
    var LIBRARY_TIMEOUT = 4000;

    /** Nothing on screen may still be invisible this long after load. */
    var REVEAL_FAILSAFE = 1000;

    function init() {
        var sections = document.querySelectorAll('[data-anim]');

        if (!sections.length) return;

        var revealAll = function () { Array.prototype.forEach.call(sections, reveal); };

        // Someone who asked their OS for less motion gets the content, not the
        // motion. This is a preference, not a nicety. A browser with no
        // IntersectionObserver gets the same deal: content, no motion.
        var calm = (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches)
            || document.documentElement.getAttribute('data-motion') === 'off'; // Theme Customizer switch

        if (calm || typeof IntersectionObserver === 'undefined') {
            revealAll();

            return;
        }

        // Visibility must never depend on the animation succeeding. If a
        // section that is ON SCREEN has not been handed to an animation by
        // now, show it. Sections further down are left as they are: they are
        // not invisible to anyone, they are waiting their turn, and watch()
        // reaches them when the reader does.
        window.setTimeout(function () {
            Array.prototype.forEach.call(sections, function (el) {
                if (el.hasAttribute('data-anim-done')) return;
                if (el.hasAttribute('data-anim-started')) return;
                if (!inViewport(el)) return;

                reveal(el);
            });
        }, REVEAL_FAILSAFE);

        if (libraryReady()) {
            watch(sections);

            return;
        }

        // This file is deliberately loaded BEFORE the animation library, so
        // that a stalled CDN cannot hold the content hostage behind it. That
        // means anime may legitimately not be here yet: wait for it, but never
        // longer than LIBRARY_TIMEOUT, and show the content either way.
        var settled = false;
        var settle = function () {
            if (settled) return;
            settled = true;

            libraryReady() ? watch(sections) : revealAll();
        };

        window.addEventListener('load', settle);
        window.setTimeout(settle, LIBRARY_TIMEOUT);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // The builder's preview iframe reloads on every save; each load re-runs
    // this file, so editor and live stay in step with no extra wiring.
    window.gsmAnimations = { init: init, presets: PRESETS };
})();
