/**
 * Form Builder — fields repeater.
 * Add/remove rows, toggle options textarea per type, drag-sort via SortableJS.
 * Row indexes only need to be unique (server re-orders by DOM position),
 * so new rows take an ever-increasing counter.
 */
(function () {
    'use strict';

    var wrap = document.getElementById('field-rows');
    if (!wrap) return;

    var template = document.getElementById('field-row-template');
    var hint = document.getElementById('no-fields-hint');
    var counter = wrap.querySelectorAll('.field-row').length + 1000;

    var OPTION_TYPES = ['select', 'radio', 'checkbox'];

    function toggleHint() {
        if (hint) hint.classList.toggle('d-none', wrap.querySelectorAll('.field-row').length > 0);
    }

    document.getElementById('add-field').addEventListener('click', function () {
        var html = template.innerHTML.replace(/__i__/g, 'n' + (counter++));
        wrap.insertAdjacentHTML('beforeend', html);
        toggleHint();
    });

    wrap.addEventListener('click', function (e) {
        var btn = e.target.closest('.remove-field');
        if (!btn) return;
        btn.closest('.field-row').remove();
        toggleHint();
    });

    wrap.addEventListener('change', function (e) {
        if (!e.target.classList.contains('field-type')) return;
        var row = e.target.closest('.field-row');
        var optionsWrap = row.querySelector('.options-wrap');
        optionsWrap.style.display = OPTION_TYPES.indexOf(e.target.value) !== -1 ? '' : 'none';
    });

    if (window.Sortable) {
        new Sortable(wrap, { handle: '.drag-handle', animation: 150 });
    }
})();
