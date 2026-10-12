/**
 * Ekdanta · Client Logo Wall: the industry filter. Matching logos fill the
 * first places in order; the rest keep their own place and fade out (CSS).
 * Markup: [data-wall] (data-cols), [data-wall-filter="<industry>|''"]
 * buttons, [data-wall-tile="<industry>"] logos, [data-wall-shown].
 */
export function slots(industries, pick) {
    let next = 0;
    return industries.map((industry, i) => {
        const match = !pick || industry === pick;
        return { match, slot: match ? next++ : i };
    });
}

function init(wall) {
    const cols = Number(wall.dataset.cols) || 8;
    const tiles = [...wall.querySelectorAll('[data-wall-tile]')];
    const buttons = [...wall.querySelectorAll('[data-wall-filter]')];
    const shown = wall.querySelector('[data-wall-shown]');

    buttons.forEach((button) => button.addEventListener('click', () => {
        const pick = button.dataset.wallFilter;
        buttons.forEach((b) => b.setAttribute('aria-pressed', String(b === button)));
        const places = slots(tiles.map((t) => t.dataset.wallTile), pick);
        places.forEach(({ match, slot }, i) => {
            tiles[i].style.setProperty('--x', slot % cols);
            tiles[i].style.setProperty('--y', Math.floor(slot / cols));
            tiles[i].classList.toggle('is-out', !match);
        });
        if (shown) shown.textContent = places.filter((p) => p.match).length;
    }));
    wall.querySelector('[data-wall-filters]')?.removeAttribute('hidden');
}

export default function initClientWalls() {
    document.querySelectorAll('[data-wall]').forEach(init);
}
