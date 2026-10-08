/**
 * Inline editing inside the builder's preview.
 *
 * Click a heading on the page, type, click away — the new text is handed to
 * the builder in the parent window, which writes it into the matching form
 * field and lets the normal autosave carry it to the server. Nothing here
 * talks to the API directly: one save path means one set of rules about
 * permissions, validation and the save indicator.
 *
 * Loaded only when gsm_inline_editing() is true — a preview request from
 * someone who may edit pages. Public visitors never receive this file.
 */
(function () {
    'use strict';

    var fields = document.querySelectorAll('[data-gsm-edit]');

    if (!fields.length || window.parent === window) return;

    document.documentElement.classList.add('gsm-inline-editing');

    /** Text only: what the CMS stores for these fields is a plain string. */
    function textOf(el) {
        return el.textContent.replace(/\s+/g, ' ').trim();
    }

    function isEditing(el) {
        return el.getAttribute('contenteditable') !== null;
    }

    function beginEditing(el) {
        if (isEditing(el)) return;

        el.dataset.gsmOriginal = textOf(el);
        el.setAttribute('contenteditable', 'plaintext-only');
        el.focus();

        // Put the caret where the user clicked rather than selecting the lot:
        // selecting everything makes the first keystroke destructive.
        if (!el.dataset.gsmClicked) {
            var range = document.createRange();
            range.selectNodeContents(el);
            range.collapse(false);

            var selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
        }

        delete el.dataset.gsmClicked;
    }

    function finishEditing(el, keep) {
        if (!isEditing(el)) return;

        el.removeAttribute('contenteditable');

        var original = el.dataset.gsmOriginal || '';
        var current = textOf(el);

        delete el.dataset.gsmOriginal;

        if (!keep) {
            el.textContent = original;

            return;
        }

        if (current === original) return;

        // Collapse whitespace back into the element too, so what is on screen
        // matches exactly what the builder was told.
        el.textContent = current;

        var parts = (el.getAttribute('data-gsm-edit') || '').split(':');

        window.parent.postMessage({
            source: 'gsm-inline-edit',
            sectionId: parseInt(parts[0], 10),
            field: parts[1],
            value: current
        }, window.location.origin);

        el.classList.add('gsm-editable--saved');
        window.setTimeout(function () { el.classList.remove('gsm-editable--saved'); }, 1200);
    }

    Array.prototype.forEach.call(fields, function (el) {
        el.addEventListener('mousedown', function () { el.dataset.gsmClicked = '1'; });
        el.addEventListener('click', function (e) {
            e.preventDefault();
            beginEditing(el);
        });

        // Keyboard parity: the element is focusable, so Enter opens it too.
        el.addEventListener('keydown', function (e) {
            if (!isEditing(el) && (e.key === 'Enter' || e.key === ' ')) {
                e.preventDefault();
                beginEditing(el);

                return;
            }

            if (!isEditing(el)) return;

            // Enter commits (these are single-line fields); Escape reverts.
            if (e.key === 'Enter') {
                e.preventDefault();
                finishEditing(el, true);
                el.blur();
            }

            if (e.key === 'Escape') {
                e.preventDefault();
                finishEditing(el, false);
                el.blur();
            }
        });

        el.addEventListener('blur', function () { finishEditing(el, true); });

        // A link wrapping an editable heading must not navigate mid-edit.
        var link = el.closest('a');
        if (link) {
            link.addEventListener('click', function (e) {
                if (isEditing(el)) e.preventDefault();
            });
        }
    });

    // Tell the builder inline editing is live, so it can show the hint once.
    window.parent.postMessage({ source: 'gsm-inline-edit', ready: true, count: fields.length },
        window.location.origin);
})();
