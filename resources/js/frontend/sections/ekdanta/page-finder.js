/**
 * Ekdanta · Page Finder: the search box filters the section's small index
 * (pages, services, articles; JSON from the server) as you type, up to five
 * matches. Without JS the box stays hidden and the cards do the job.
 */
export function search(index, q, max = 5) {
    const needle = q.trim().toLowerCase();
    if (!needle) return [];
    return index.filter((r) => `${r.t} ${r.w || ''}`.toLowerCase().includes(needle)).slice(0, max);
}

function init(box, index) {
    const input = box.querySelector('input');
    const clear = box.querySelector('[data-finder-clear]');
    const results = box.querySelector('[data-finder-results]');
    const count = box.querySelector('[data-finder-count]');
    const list = box.querySelector('[data-finder-list]');
    const none = box.querySelector('[data-finder-none]');
    const row = box.querySelector('template').content.firstElementChild;
    const text = box.dataset;

    const update = () => {
        const q = input.value.trim();
        clear.hidden = results.hidden = !q;
        if (!q) return;
        const hits = search(index, q);
        count.textContent = hits.length ? (hits.length === 1 ? text.one : text.many).replace('{n}', hits.length) : text.none;
        none.hidden = hits.length > 0;
        list.replaceChildren(...hits.map((hit) => {
            const a = row.cloneNode(true);
            a.href = hit.u;
            const kind = a.querySelector('.ek-finder-kind');
            kind.dataset.kind = hit.k;
            kind.textContent = text[`kind${hit.k[0].toUpperCase()}${hit.k.slice(1)}`] || hit.k;
            a.querySelector('.ek-finder-title').textContent = hit.t;
            return a;
        }));
    };

    input.addEventListener('input', update);
    clear.addEventListener('click', () => { input.value = ''; update(); input.focus(); });
    box.hidden = false;
}

export default function initPageFinders() {
    document.querySelectorAll('.ek-page-finder').forEach((section) => {
        let index = [];
        try {
            index = JSON.parse(section.querySelector('[data-finder-index]')?.textContent || '[]');
        } catch {
            return; // a broken index: keep the box hidden, the cards still work
        }
        section.querySelectorAll('[data-finder]').forEach((box) => init(box, index));
    });
}
