/**
 * GSM Global Block Library — index screen interactions (favorite toggle).
 */
(function () {
    'use strict';

    var grid = document.getElementById('gsm-block-grid');
    if (!grid) return;

    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    grid.addEventListener('click', function (e) {
        var btn = e.target.closest('.gsm-toggle-favorite');
        if (!btn) return;
        e.preventDefault();

        fetch(btn.dataset.url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        }).then(function (res) { return res.json(); }).then(function (json) {
            var icon = btn.querySelector('i');
            icon.className = (json.is_favorite ? 'fas' : 'far') + ' fa-star';
        });
    });
})();
