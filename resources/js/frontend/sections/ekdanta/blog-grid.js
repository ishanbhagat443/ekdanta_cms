/**
 * Ekdanta · Article Grid: topic chips and the hero's search box filter the
 * cards in place (the server drew the same filter for ?topic= / ?search=).
 * Where the browser has View Transitions, the grid re-sorts smoothly.
 * Markup: [data-blog-grid] > [data-grid-comp] (data-heading, data-results)
 * with a[data-topic] chips, li[data-topic][data-text] cards,
 * [data-grid-empty] and [data-grid-title]; forms [data-blog-search].
 */
export function matches(card, topic, query) {
    const q = query.trim().toLowerCase();
    return (!topic || ` ${card.topic} `.includes(` ${topic} `)) && (!q || card.text.includes(q));
}

const calm = () => document.documentElement.dataset.motion === 'off'
    || matchMedia('(prefers-reduced-motion: reduce)').matches;

export default function initBlogGrids() {
    const grids = [...document.querySelectorAll('[data-blog-grid] [data-grid-comp]')];
    if (!grids.length) return;
    const state = {
        topic: grids[0].querySelector('a[data-topic][aria-current]')?.dataset.topic || '',
        query: new URLSearchParams(location.search).get('search') || '',
    };

    const draw = () => grids.forEach((comp) => {
        let shown = 0;
        comp.querySelectorAll('li[data-topic]').forEach((li) => {
            const on = matches({ topic: li.dataset.topic, text: li.dataset.text }, state.topic, state.query);
            li.hidden = !on;
            shown += on ? 1 : 0;
        });
        comp.querySelector('[data-grid-empty]')?.toggleAttribute('hidden', shown > 0);
        comp.querySelectorAll('a[data-topic]').forEach((a) => {
            if (a.dataset.topic === state.topic) a.setAttribute('aria-current', 'true'); else a.removeAttribute('aria-current');
        });
        const title = comp.querySelector('[data-grid-title]');
        if (title) title.textContent = state.query.trim() ? comp.dataset.results.replace('{q}', state.query.trim()) : comp.dataset.heading;
    });
    const update = () => (document.startViewTransition && !calm() ? document.startViewTransition(draw) : draw());

    grids.forEach((comp) => comp.querySelectorAll('a[data-topic]').forEach((a) => a.addEventListener('click', (e) => {
        e.preventDefault();
        state.topic = a.dataset.topic;
        update();
        const url = new URL(location.href);
        if (state.topic) url.searchParams.set('topic', state.topic); else url.searchParams.delete('topic');
        history.replaceState(null, '', url);
    })));

    const anchor = document.querySelector('[data-blog-grid]');
    document.querySelectorAll('[data-blog-search]').forEach((form) => {
        const input = form.querySelector('input[type="search"]');
        input?.addEventListener('input', () => { state.query = input.value; update(); });
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            state.query = input?.value || '';
            update();
            anchor?.scrollIntoView({ behavior: calm() ? 'instant' : 'smooth' }); // 'auto' would follow Bootstrap's smooth scrolling
        });
    });
}
