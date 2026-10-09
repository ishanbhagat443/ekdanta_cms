/**
 * Repeating rows outside the page builder (Settings lists, Collections):
 * "Add" clones the row template, "×" removes a row. Template fields carry
 * data-name with an __i__ placeholder, so they are never posted themselves.
 */
document.addEventListener('click', function (e) {
    var add = e.target.closest && e.target.closest('.gsm-repeater-add');
    if (add) {
        var repeater = add.closest('.gsm-repeater');
        var fragment = repeater.querySelector('.gsm-repeater-template').content.cloneNode(true);
        var index = Date.now();
        fragment.querySelectorAll('[data-name]').forEach(function (field) {
            field.name = field.dataset.name.replace('__i__', index);
        });
        repeater.insertBefore(fragment, add);
        var first = add.previousElementSibling && add.previousElementSibling.querySelector('input:not([type=hidden]), textarea');
        if (first) first.focus();
    }
    var remove = e.target.closest && e.target.closest('.gsm-repeater-remove');
    if (remove) remove.closest('.gsm-repeater-row').remove();
});
