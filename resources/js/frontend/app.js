// Public site JS.
import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

// Scroll-in animations now live in public/js/gsm-animations.js, driven by the
// data-anim-* attributes the CMS renders and powered by Anime.js v4.

// Hero background video: browsers may still block autoplay in rare cases
// (e.g. data-saver mode) — force muted (required for mobile autoplay) and
// retry playback on the first user gesture. The element itself is
// pointer-events:none, so users can never pause or interact with it.
document.querySelectorAll('.gsm-hero-video').forEach((video) => {
    video.muted = true; // belt & suspenders: attribute + property

    const attempt = video.play();
    if (attempt !== undefined) {
        attempt.catch(() => {
            const retry = () => video.play().catch(() => {});
            document.addEventListener('click', retry, { once: true });
            document.addEventListener('touchstart', retry, { once: true });
        });
    }
});

// Shared horizontal scroll-snap pagination: one dot per scroll "page" of
// the track; hidden entirely when everything fits on one page. Used by
// the Home Category slider and the About History timeline — any number
// of items can be added via the CMS and this keeps working unmodified.
function initScrollDots(sliderSelector, trackSelector, dotsSelector, dotClass) {
    document.querySelectorAll(sliderSelector).forEach((slider) => {
        const track = slider.querySelector(trackSelector);
        const dotsWrap = slider.querySelector(dotsSelector);
        if (!track || !dotsWrap) return;

        const pageCount = () => Math.max(1, Math.ceil(track.scrollWidth / track.clientWidth));

        const update = () => {
            const index = Math.min(pageCount() - 1, Math.round(track.scrollLeft / track.clientWidth));
            dotsWrap.querySelectorAll(`.${dotClass}`).forEach((dot, i) => {
                dot.classList.toggle('active', i === index);
            });
        };

        const render = () => {
            const pages = pageCount();
            dotsWrap.innerHTML = '';
            if (pages < 2) return;

            for (let i = 0; i < pages; i++) {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.className = dotClass;
                dot.setAttribute('aria-label', `Go to slide ${i + 1}`);
                dot.addEventListener('click', () => {
                    track.scrollTo({ left: i * track.clientWidth, behavior: 'smooth' });
                });
                dotsWrap.appendChild(dot);
            }
            update();
        };

        track.addEventListener('scroll', () => requestAnimationFrame(update), { passive: true });
        window.addEventListener('resize', render);
        render();
    });
}

initScrollDots('.gsm-cat-slider', '.gsm-cat-track', '.gsm-cat-dots', 'gsm-cat-dot');
initScrollDots('.gsm-history-slider', '.gsm-history-track', '.gsm-history-dots', 'gsm-history-dot');
initScrollDots('.gsm-artistry-slider', '.gsm-artistry-track', '.gsm-artistry-dots', 'gsm-artistry-dot');

// History timeline connector (design 07 Our History.png): draws one
// continuous smooth curve through every badge's real center, in the
// track's own pixel coordinate space (viewBox matches scrollWidth x
// clientHeight exactly), so it stays aligned with the wave of
// alternating-height items at any breakpoint and for any CMS-added
// milestone count — measuring the actual DOM rather than hand-tuning a
// fixed-size asset is what makes this responsive.
document.querySelectorAll('[data-gsm-history-track]').forEach((track) => {
    const svg = track.querySelector('[data-gsm-history-line]');
    const path = track.querySelector('[data-gsm-history-path]');
    if (!svg || !path) return;

    const draw = () => {
        const items = Array.from(track.querySelectorAll('[data-gsm-history-item]'));
        const badges = items.map((item) => item.querySelector('[data-gsm-history-badge]')).filter(Boolean);
        if (badges.length < 2) {
            path.setAttribute('d', '');
            return;
        }

        const trackRect = track.getBoundingClientRect();
        const width = track.scrollWidth;
        const height = trackRect.height;

        svg.setAttribute('viewBox', `0 0 ${width} ${height}`);
        svg.setAttribute('width', width);
        svg.setAttribute('height', height);

        const points = badges.map((badge) => {
            const r = badge.getBoundingClientRect();
            return {
                x: r.left - trackRect.left + r.width / 2 + track.scrollLeft,
                y: r.top - trackRect.top + r.height / 2,
            };
        });

        // One cubic-bezier segment per adjacent pair, control points pulled
        // toward the horizontal midpoint — the same flowing S-curve shape
        // as the design, but generated for however many points exist.
        let d = `M ${points[0].x} ${points[0].y}`;
        for (let i = 1; i < points.length; i++) {
            const prev = points[i - 1];
            const curr = points[i];
            const midX = (prev.x + curr.x) / 2;
            d += ` C ${midX} ${prev.y}, ${midX} ${curr.y}, ${curr.x} ${curr.y}`;
        }
        path.setAttribute('d', d);
    };

    draw();
    window.addEventListener('resize', () => requestAnimationFrame(draw));
    track.addEventListener('scroll', () => requestAnimationFrame(draw), { passive: true });
    // Images loading below the badges can shift layout height slightly.
    track.querySelectorAll('img').forEach((img) => {
        if (!img.complete) img.addEventListener('load', draw, { once: true });
    });
});

// Artistry gallery ([data-gsm-artistry-card]): clicking a card reveals its
// description and collapses any other open card in the same track — an
// accordion, not a hover reveal, so it works the same on touch devices.
document.querySelectorAll('.gsm-artistry-track').forEach((track) => {
    track.addEventListener('click', (e) => {
        const card = e.target.closest('[data-gsm-artistry-card]');
        if (!card || !track.contains(card)) return;

        track.querySelectorAll('[data-gsm-artistry-card]').forEach((other) => {
            const isTarget = other === card;
            other.classList.toggle('gsm-artistry-card-active', isTarget);
            other.setAttribute('aria-expanded', isTarget ? 'true' : 'false');
        });
    });
});

// Header over hero (.gsm-header-overlay): measure height so the negative
// margin pulls the hero underneath, and swap transparent → solid on scroll.
const overlayHeader = document.querySelector('.gsm-header-overlay');
if (overlayHeader) {
    const measure = () => {
        overlayHeader.style.setProperty('--gsm-header-h', `${overlayHeader.offsetHeight}px`);
    };

    measure();
    window.addEventListener('resize', measure);

    // Pages without a hero force the solid/light header always — colored
    // logo, dark menu, white background — never transparent.
    if (document.body.classList.contains('gsm-solid-header')) {
        overlayHeader.classList.add('gsm-header-scrolled');
    } else {
        const onScroll = () => {
            overlayHeader.classList.toggle('gsm-header-scrolled', window.scrollY > 40);
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }
}

// Button Builder ripple effect ([data-gsm-ripple], see ButtonStyleService).
document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-gsm-ripple]');
    if (!btn) return;

    const rect = btn.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const ink = document.createElement('span');
    ink.className = 'gsm-btn-ripple-ink';
    ink.style.width = ink.style.height = `${size}px`;
    ink.style.left = `${e.clientX - rect.left - size / 2}px`;
    ink.style.top = `${e.clientY - rect.top - size / 2}px`;
    btn.appendChild(ink);
    ink.addEventListener('animationend', () => ink.remove());
});

// Counter count-up (.gsm-countup with data-target).
const counters = document.querySelectorAll('.gsm-countup');
if (counters.length && 'IntersectionObserver' in window) {
    const countObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            countObserver.unobserve(entry.target);

            const el = entry.target;
            const target = parseInt(el.dataset.target, 10);
            if (Number.isNaN(target)) return;

            const duration = 1400;
            const start = performance.now();
            const tick = (now) => {
                const progress = Math.min((now - start) / duration, 1);
                el.textContent = Math.round(target * progress).toLocaleString();
                if (progress < 1) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
        });
    }, { threshold: 0.4 });

    counters.forEach((el) => countObserver.observe(el));
}

// Progressive AJAX for Form Builder forms ([data-gsm-ajax-form]) — contact,
// footer, etc. Submits without a page reload; shows inline success or
// per-field validation errors. Falls back to a normal POST if JS is off.
document.querySelectorAll('[data-gsm-ajax-form]').forEach((form) => {
    const card = form.closest('.card') || form.parentElement;
    const alertBox = card ? card.querySelector('[data-gsm-form-alert]') : null;

    const clearErrors = () => {
        form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
        form.querySelectorAll('[data-gsm-error]').forEach((el) => el.remove());
        if (alertBox) { alertBox.hidden = true; alertBox.textContent = ''; alertBox.className = 'gsm-form-alert'; }
    };

    const showAlert = (msg, ok) => {
        if (!alertBox) return;
        alertBox.hidden = false;
        alertBox.className = 'gsm-form-alert alert ' + (ok ? 'alert-success' : 'alert-danger') + ' py-2';
        alertBox.textContent = msg;
    };

    const showFieldError = (name, msg) => {
        const input = form.querySelector('[name="' + name + '"], [name="' + name + '[]"]');
        if (!input) return;
        input.classList.add('is-invalid');
        const fb = document.createElement('div');
        fb.className = 'invalid-feedback d-block';
        fb.setAttribute('data-gsm-error', name);
        fb.textContent = msg;
        (input.closest('.col-12, .col-md-6') || input.parentElement).appendChild(fb);
    };

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        clearErrors();

        const btn = form.querySelector('[type="submit"]');
        const label = btn ? btn.textContent : '';
        if (btn) { btn.disabled = true; btn.textContent = 'Sending…'; }

        // Accept: JSON makes Laravel answer with JSON, including 422 errors.
        fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' } })
            .then((res) => res.json().catch(() => ({})).then((data) => {
                if (res.ok) {
                    showAlert(data.message || 'Thank you! Your message has been sent.', true);
                    form.reset();
                    form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
                } else if (res.status === 422 && data.errors) {
                    Object.keys(data.errors).forEach((name) => showFieldError(name, data.errors[name][0]));
                    showAlert('Please correct the errors below.', false);
                } else {
                    showAlert('Something went wrong. Please try again.', false);
                }
            }))
            .catch(() => showAlert('Something went wrong. Please try again.', false))
            .finally(() => {
                if (btn) { btn.disabled = false; btn.textContent = label; }
            });
    });
});

// Blog Detail "Copy Link" share button (Blog Detail CMS → Social Share,
// opt-in). Event delegation — works for any current/future instance.
document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-gsm-copy-link]');
    if (!btn) return;

    const url = btn.dataset.gsmCopyLink;
    const restoreIcon = btn.innerHTML;

    navigator.clipboard.writeText(url).then(() => {
        btn.innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(() => { btn.innerHTML = restoreIcon; }, 1500);
    }).catch(() => {});
});

/* ── Live search suggestions (pages + blog posts) ────────────────────── */
document.querySelectorAll('[data-live-search]').forEach(function (searchInput) {
    const panel = searchInput.closest('[data-live-search-wrap]')?.querySelector('[data-live-search-results]');
    if (!panel) return;

    const render = (data) => {
        const parts = [];
        (data.pages || []).forEach((p, i) => {
            if (i === 0) parts.push('<div class="gsm-search-group">Pages</div>');
            parts.push('<a class="gsm-search-item" href="' + p.url + '"><i class="far fa-file fa-fw me-2"></i>' + p.name + '</a>');
        });
        (data.posts || []).forEach((p, i) => {
            if (i === 0) parts.push('<div class="gsm-search-group">Blog</div>');
            parts.push('<a class="gsm-search-item" href="' + p.url + '"><i class="far fa-newspaper fa-fw me-2"></i>' + p.name + '</a>');
        });
        panel.innerHTML = parts.length ? parts.join('') : '<div class="gsm-search-item text-muted">No matches</div>';
        panel.hidden = false;
    };

    let timer;
    searchInput.addEventListener('input', () => {
        const q = searchInput.value.trim();
        clearTimeout(timer);
        if (q.length < 2) { panel.hidden = true; return; }

        timer = setTimeout(() => {
            fetch('/search/suggest?q=' + encodeURIComponent(q), { headers: { Accept: 'application/json' } })
                .then((r) => r.json())
                .then(render)
                .catch(() => {});
        }, 220);
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('[data-live-search-wrap]')) panel.hidden = true;
    });
});

// Full-screen menu (Header Builder → Navigation style). A modal <dialog>:
// the browser traps focus inside, closes it on Esc and returns focus to the
// menu button. Following a link closes it, which also covers #anchors on the
// same page.
document.querySelectorAll('[data-gsm-menu-open]').forEach((button) => {
    const dialog = document.getElementById(button.getAttribute('aria-controls'));
    if (!dialog || typeof dialog.showModal !== 'function') return;

    button.addEventListener('click', () => {
        dialog.showModal();
        button.setAttribute('aria-expanded', 'true');
    });
    dialog.addEventListener('close', () => button.setAttribute('aria-expanded', 'false'));
    dialog.querySelectorAll('a, [data-gsm-menu-close]').forEach((el) => el.addEventListener('click', () => dialog.close()));
});
