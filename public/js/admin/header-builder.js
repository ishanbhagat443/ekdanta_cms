/**
 * GSM Header Builder — section CRUD, drag-drop ordering, per-device saves.
 */
(function () {
    'use strict';

    var root = document.getElementById('gsm-header-builder')
        || document.getElementById('gsm-footer-builder')
        || document.getElementById('gsm-page-builder');
    if (!root) return;

    var sectionsUrl = root.dataset.sectionsUrl;
    var pageId = root.dataset.pageId ? parseInt(root.dataset.pageId, 10) : null;
    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function reloadPreview() {
        var frame = document.getElementById('gsm-live-preview');
        if (frame) frame.contentWindow.location.reload();
    }

    function ajax(method, url, body) {
        return fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: body ? JSON.stringify(body) : undefined
        }).then(function (res) {
            return res.json().catch(function () { return {}; }).then(function (json) {
                if (!res.ok) {
                    var msg = json.message || 'Request failed';
                    if (json.errors) {
                        msg = Object.keys(json.errors).map(function (k) { return json.errors[k].join(' '); }).join(' ');
                    }
                    throw new Error(msg);
                }
                return json;
            });
        });
    }

    function toast(message, isError) {
        if (window.Swal) {
            Swal.fire({ toast: true, position: 'top-end', timer: 2200, showConfirmButton: false,
                icon: isError ? 'error' : 'success', title: message });
        } else if (isError) {
            alert(message);
        }
    }

    /* ── Reordering ──────────────────────────────────────────────────
       Dragging a section is a save like any other, so it reports through the
       same state indicator rather than a toast that disappears. The order on
       screen is the order on the page: if the save fails, the card goes back
       where it came from instead of lying about where it now sits.
    */
    document.querySelectorAll('.gsm-section-list').forEach(function (list) {
        new Sortable(list, {
            handle: '.gsm-drag-handle',
            draggable: '.gsm-section-card',
            animation: 150,
            easing: 'cubic-bezier(0.2, 0, 0, 1)',
            ghostClass: 'gsm-section-card--ghost',
            chosenClass: 'gsm-section-card--chosen',
            dragClass: 'gsm-section-card--dragging',
            fallbackOnBody: true,
            swapThreshold: 0.65,

            onStart: function () {
                document.body.classList.add('gsm-is-reordering');
            },

            onEnd: function (evt) {
                document.body.classList.remove('gsm-is-reordering');

                if (evt.oldIndex === evt.newIndex) return; // dropped where it started

                var ids = Array.prototype.map.call(
                    list.querySelectorAll('.gsm-section-card'),
                    function (card) { return parseInt(card.dataset.id, 10); }
                );

                setState('saving');

                ajax('POST', sectionsUrl + '/reorder', { area: list.dataset.area, page_id: pageId, ids: ids })
                    .then(function () {
                        savedNow();
                        reloadPreview();
                    })
                    .catch(function (err) {
                        // Put it back: an order that did not save must not keep
                        // sitting there looking saved.
                        var moved = evt.item;
                        var siblings = list.querySelectorAll('.gsm-section-card');

                        if (evt.oldIndex < siblings.length) {
                            list.insertBefore(moved, siblings[evt.oldIndex]);
                        } else {
                            list.appendChild(moved);
                        }

                        setState('error', 'Order not saved — ' + err.message + '. Press Save to try again.');
                    });
            }
        });
    });

    /* Add section → reload to render its server-side card */
    document.querySelectorAll('.gsm-add-section').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            ajax('POST', sectionsUrl, { area: this.dataset.area, page_id: pageId, type: this.dataset.type })
                .then(function () { window.location.reload(); })
                .catch(function (err) { toast(err.message, true); });
        });
    });

    /* Add Existing Block (Global Block Library) → reload to render its card */
    document.querySelectorAll('.gsm-add-existing-block').forEach(function (btn) {
        btn.addEventListener('click', function () {
            ajax('POST', sectionsUrl + '/from-block', {
                area: 'page', page_id: pageId, global_block_id: parseInt(this.dataset.blockId, 10)
            }).then(function () { window.location.reload(); })
              .catch(function (err) { toast(err.message, true); });
        });
    });

    /* Live search inside the "Add Existing Block" modal */
    var blockSearch = document.getElementById('gsm-block-picker-search');
    if (blockSearch) {
        blockSearch.addEventListener('input', function () {
            var term = blockSearch.value.trim().toLowerCase();
            document.querySelectorAll('.gsm-block-picker-item').forEach(function (item) {
                item.style.display = item.dataset.name.indexOf(term) !== -1 ? '' : 'none';
            });
        });
    }

    /* Delegated card events */
    root.addEventListener('click', function (e) {
        var card = e.target.closest('.gsm-section-card');
        if (!card) return;
        var id = card.dataset.id;

        if (e.target.closest('.gsm-sc-delete')) {
            var doDelete = function () {
                ajax('DELETE', sectionsUrl + '/' + id)
                    .then(function () { card.remove(); toast('Section deleted'); })
                    .catch(function (err) { toast(err.message, true); });
            };
            if (window.Swal) {
                Swal.fire({ title: 'Delete this section?', icon: 'warning', showCancelButton: true,
                    confirmButtonColor: '#dc3545', confirmButtonText: 'Delete' })
                    .then(function (r) { if (r.isConfirmed) doDelete(); });
            } else if (confirm('Delete this section?')) {
                doDelete();
            }
        }

        if (e.target.closest('.gsm-sc-clone')) {
            ajax('POST', sectionsUrl + '/' + id + '/clone')
                .then(function () { window.location.reload(); })
                .catch(function (err) { toast(err.message, true); });
        }

        if (e.target.closest('.gsm-sc-detach')) {
            if (!confirm('Detach from the global block? Future edits to the master will no longer affect this page.')) return;
            ajax('POST', sectionsUrl + '/' + id + '/detach')
                .then(function () { window.location.reload(); })
                .catch(function (err) { toast(err.message, true); });
        }

        if (e.target.closest('.gsm-sc-promote')) {
            var blockName = prompt('Name this reusable block:', card.querySelector('strong').textContent.trim());
            if (!blockName) return;
            ajax('POST', sectionsUrl + '/' + id + '/promote', { name: blockName })
                .then(function () { window.location.reload(); toast('Saved as a reusable block'); })
                .catch(function (err) { toast(err.message, true); });
        }

        if (e.target.closest('.gsm-repeater-add')) {
            var repeater = e.target.closest('.gsm-repeater');
            var template = repeater.querySelector('.gsm-repeater-template');
            var index = repeater.querySelectorAll('.gsm-repeater-row:not(template .gsm-repeater-row)').length;
            var fragment = template.content.cloneNode(true);
            fragment.querySelectorAll('input[data-name]').forEach(function (input) {
                input.name = input.dataset.name.replace('__i__', String(index) + '_' + Date.now());
            });
            repeater.insertBefore(fragment, template);
        }

        if (e.target.closest('.gsm-repeater-remove')) {
            e.target.closest('.gsm-repeater-row').remove();
        }
    });

    /* Active toggle (immediate save) */
    root.addEventListener('change', function (e) {
        if (!e.target.classList.contains('gsm-sc-active')) return;
        var card = e.target.closest('.gsm-section-card');
        ajax('PUT', sectionsUrl + '/' + card.dataset.id, {
            device: 'desktop',
            data: null,
            is_active: e.target.checked
        }).then(function () { toast(e.target.checked ? 'Section enabled' : 'Section disabled'); })
          .catch(function (err) { toast(err.message, true); });
    });


    /* ── Saving ──────────────────────────────────────────────────────
       Each section has its own <form> (one per device, plus a display
       form). They are NOT inside the page/layout settings form — HTML
       forms cannot nest — so the primary "Save Page" button used to post
       only the settings fields and reload, silently discarding whatever
       the user had typed into a section. Everything below exists so that
       one button saves everything, and only reports success when it did.
    */

    /** Sync Summernote rich-text editors back into their textareas. */
    function syncRichText(form) {
        if (!window.jQuery || !window.jQuery.fn.summernote) return;

        form.querySelectorAll('.gsm-richtext-input').forEach(function (el) {
            var $el = window.jQuery(el);
            if ($el.next('.note-editor').length) el.value = $el.summernote('code');
        });
    }

    /** Build the PUT payload for one per-device section form. */
    function sectionPayload(form) {
        syncRichText(form);

        var payload = { device: form.dataset.device, data: {} };

        new FormData(form).forEach(function (value, name) {
            if (name === 'name') {
                if (form.dataset.device === 'desktop') payload.name = value;
                return;
            }

            var match = name.match(/^data\[([^\]]+)\](?:\[([^\]]+)\]\[([^\]]+)\])?$/);
            if (!match) return;

            if (match[2] !== undefined) {
                // repeater: data[key][row][sub]
                payload.data[match[1]] = payload.data[match[1]] || {};
                payload.data[match[1]][match[2]] = payload.data[match[1]][match[2]] || {};
                payload.data[match[1]][match[2]][match[3]] = value;
            } else {
                payload.data[match[1]] = value;
            }
        });

        // repeater objects → arrays
        Object.keys(payload.data).forEach(function (k) {
            var v = payload.data[k];
            if (v && typeof v === 'object') payload.data[k] = Object.values(v);
        });

        return payload;
    }

    /** Build the PUT payload for one section display/visibility form. */
    function visibilityPayload(form) {
        var style = { visible: {} };

        ['desktop', 'tablet', 'mobile'].forEach(function (device) {
            var box = form.querySelector('input[type="checkbox"][name="style[visible][' + device + ']"]');
            style.visible[device] = box ? box.checked : true;

            var widthSelect = form.querySelector('select[name="style[width][' + device + ']"]');
            if (widthSelect) {
                style.width = style.width || {};
                style.width[device] = parseInt(widthSelect.value, 10);
            }
        });

        // Every remaining style input in the form. Keep this list in step with
        // the fields in section-card.blade.php: anything missing here is a
        // control the user can change and watch do nothing.
        [
            'bg_media_id', 'bg_gradient', 'bg_video_media_id',
            'overlay_color', 'overlay_opacity',
            'pt', 'pb', 'mt', 'mb',
            'radius', 'z_index',
            'custom_css', 'custom_js'
        ].forEach(function (key) {
            var input = form.querySelector('[name="style[' + key + ']"]');
            if (input) style[key] = input.value === '' ? null : input.value;
        });

        // Scroll animation, one set of fields per device:
        //   style[anim][desktop][preset], style[anim][desktop][duration], …
        var anim = {};

        form.querySelectorAll('[name^="style[anim]"]').forEach(function (input) {
            var match = input.name.match(/^style\[anim\]\[([a-z]+)\]\[([a-z_]+)\]$/);
            if (!match) return;

            var animDevice = match[1];
            var key = match[2];

            anim[animDevice] = anim[animDevice] || {};

            // Checkboxes ship a hidden "0" ahead of them, so an unchecked box
            // still sends a value and the switch can actually be turned off.
            if (input.type === 'checkbox') {
                if (input.checked) anim[animDevice][key] = true;
                else if (!(key in anim[animDevice])) anim[animDevice][key] = false;

                return;
            }

            if (input.type === 'hidden' && key in anim[animDevice]) return;

            anim[animDevice][key] = input.value;
        });

        if (Object.keys(anim).length) style.anim = anim;

        var bgColor = form.querySelector('[name="style[bg_color]"]');
        if (bgColor) {
            var noBg = form.querySelector('[name="style[bg_none]"]');
            style.bg_color = (noBg && noBg.checked) ? null : bgColor.value;
        }

        return { device: 'desktop', data: null, style: style };
    }

    /** PUT one section form. Resolves on success, rejects with the error. */
    function saveSectionForm(form) {
        var isContent = form.classList.contains('gsm-section-form');
        var payload = isContent ? sectionPayload(form) : visibilityPayload(form);

        return ajax('PUT', sectionsUrl + '/' + form.dataset.id, payload).then(function (res) {
            delete form.dataset.dirty;
            return res;
        });
    }

    /** A short label for error messages: "Hero Banner (tablet)". */
    function formLabel(form) {
        var card = form.closest('.gsm-section-card');
        var title = card ? (card.querySelector('.gsm-sc-title') || {}).textContent : null;
        var name = (title || 'Section').trim();

        return form.dataset.device ? name + ' (' + form.dataset.device + ')' : name + ' (display)';
    }

    /* Any edit inside a section form marks it unsaved, so the primary save
       knows what to push and the tab-away guard knows to warn. */
    root.addEventListener('input', markDirty, true);
    root.addEventListener('change', markDirty, true);

    function markDirty(e) {
        var form = e.target.closest && e.target.closest('.gsm-section-form, .gsm-visibility-form');
        if (form) form.dataset.dirty = '1';
    }

    function dirtyForms() {
        return Array.prototype.slice.call(
            root.querySelectorAll('.gsm-section-form[data-dirty], .gsm-visibility-form[data-dirty]')
        );
    }


    /* ── Save state ──────────────────────────────────────────────────
       One visible answer to "is my work safe?", in the sticky bar. Every
       save path — autosave, the Save button, a section's own form — reports
       through here, so the indicator can never disagree with reality.
    */

    var stateEl = document.querySelector('[data-gsm-save-state]');
    var stateIcon = document.querySelector('[data-gsm-save-icon]');
    var stateText = document.querySelector('[data-gsm-save-text]');

    var STATES = {
        idle: { icon: 'fa-circle-check', text: 'All changes saved' },
        pending: { icon: 'fa-pen', text: 'Unsaved changes' },
        saving: { icon: 'fa-circle-notch', text: 'Saving…' },
        saved: { icon: 'fa-circle-check', text: 'Saved' },
        error: { icon: 'fa-triangle-exclamation', text: 'Not saved' }
    };

    function setState(name, message) {
        if (!stateEl) return;

        var state = STATES[name] || STATES.idle;

        stateEl.dataset.state = name;
        stateIcon.className = 'fas ' + state.icon;
        stateText.textContent = message || state.text;
    }

    function savedNow() {
        var time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        setState('saved', 'Saved at ' + time);
    }

    /* ── Undo / redo ─────────────────────────────────────────────────
       In-session and field-level: it walks back what you typed and picked.
       Adding or deleting a section is a server change — Version history is
       the way back from those, and it is one click away in the same bar.
    */

    var undoBtn = document.querySelector('[data-gsm-undo]');
    var redoBtn = document.querySelector('[data-gsm-redo]');
    var undoStack = [];
    var redoStack = [];
    var UNDO_LIMIT = 50;
    var restoring = false;

    function editableFields() {
        return Array.prototype.slice.call(
            root.querySelectorAll(
                '.gsm-section-form input, .gsm-section-form select, .gsm-section-form textarea,' +
                '.gsm-visibility-form input, .gsm-visibility-form select, .gsm-visibility-form textarea,' +
                '[data-gsm-primary-save] input, [data-gsm-primary-save] select, [data-gsm-primary-save] textarea'
            )
        ).filter(function (el) {
            return el.type !== 'hidden' && el.name;
        });
    }

    /** A snapshot is every editable field's value, keyed by a stable path. */
    function snapshotFields() {
        var snapshot = [];

        editableFields().forEach(function (el, index) {
            snapshot.push({
                el: el,
                index: index,
                value: (el.type === 'checkbox' || el.type === 'radio') ? el.checked : el.value
            });
        });

        return snapshot;
    }

    function applySnapshot(snapshot) {
        restoring = true;

        snapshot.forEach(function (entry) {
            var el = entry.el;
            if (!el.isConnected) return;

            if (el.type === 'checkbox' || el.type === 'radio') {
                if (el.checked !== entry.value) el.checked = entry.value;
            } else if (el.value !== entry.value) {
                el.value = entry.value;

                // Keep Summernote in step with the textarea it shadows.
                if (window.jQuery && window.jQuery.fn.summernote && el.classList.contains('gsm-richtext-input')) {
                    var $el = window.jQuery(el);
                    if ($el.next('.note-editor').length) $el.summernote('code', entry.value);
                }
            }

            var form = el.closest('.gsm-section-form, .gsm-visibility-form');
            if (form) form.dataset.dirty = '1';

            // Page/layout settings live in their own form, which is tracked by
            // a flag rather than a dataset attribute. Without this an undone
            // title or slug would never be sent, and the unload guard would
            // not warn about it either.
            if (pageForm && pageForm.contains(el)) pageFormDirty = true;
        });

        restoring = false;
        refreshUndoButtons();
        scheduleAutosave();
    }

    function refreshUndoButtons() {
        if (undoBtn) undoBtn.disabled = undoStack.length < 2;
        if (redoBtn) redoBtn.disabled = redoStack.length === 0;
    }

    function pushHistory() {
        if (restoring) return;

        undoStack.push(snapshotFields());
        if (undoStack.length > UNDO_LIMIT) undoStack.shift();

        redoStack.length = 0; // a new edit forks the timeline
        refreshUndoButtons();
    }

    function undo() {
        if (undoStack.length < 2) return;

        redoStack.push(undoStack.pop());
        applySnapshot(undoStack[undoStack.length - 1]);
    }

    function redo() {
        if (!redoStack.length) return;

        var next = redoStack.pop();
        undoStack.push(next);
        applySnapshot(next);
    }

    if (undoBtn) undoBtn.addEventListener('click', undo);
    if (redoBtn) redoBtn.addEventListener('click', redo);

    document.addEventListener('keydown', function (e) {
        if (!(e.ctrlKey || e.metaKey) || e.key.toLowerCase() !== 'z') return;

        // Leave the browser's own undo alone while typing in a field.
        var tag = (e.target.tagName || '').toLowerCase();
        if (tag === 'input' || tag === 'textarea' || e.target.isContentEditable) return;

        e.preventDefault();
        e.shiftKey ? redo() : undo();
    });

    // Seed the baseline so the first edit has something to go back to.
    undoStack.push(snapshotFields());
    refreshUndoButtons();

    /* ── Autosave ────────────────────────────────────────────────────
       Debounced: the user pauses, the work is on the server. Explicit Save
       stays for "I am done" — it is what writes a version-history entry.
    */

    var AUTOSAVE_DELAY = 1500;
    var autosaveTimer = null;
    var saveChain = Promise.resolve();
    var pageForm = document.querySelector('[data-gsm-primary-save]');
    var pageFormDirty = false;
    var leavingDeliberately = false;

    function scheduleAutosave() {
        if (!dirtyForms().length && !pageFormDirty) return;

        setState('pending');
        clearTimeout(autosaveTimer);
        autosaveTimer = setTimeout(function () { runSave(false); }, AUTOSAVE_DELAY);
    }

    /**
     * Push everything unsaved.
     *
     * @param {boolean} explicit  true = the Save button (also snapshots a version)
     */
    function runSave(explicit) {
        clearTimeout(autosaveTimer);

        var sections = dirtyForms();
        if (!sections.length && !pageFormDirty && !explicit) return Promise.resolve();

        setState('saving');

        // Queue behind any save already in flight: two writes to the same
        // section would race, and the later answer is not always the later write.
        saveChain = saveChain.then(function () {
            var failures = [];

            return sections.reduce(function (chain, form) {
                return chain.then(function () {
                    return saveSectionForm(form).catch(function (err) {
                        failures.push(formLabel(form) + ': ' + err.message);
                    });
                });
            }, Promise.resolve()).then(function () {
                if (failures.length) {
                    throw new Error(failures.join(' · '));
                }

                return savePageForm(explicit);
            }).then(function () {
                pageFormDirty = false;
                savedNow();
                reloadPreview();
            }).catch(function (err) {
                // Sticky and specific: say what failed and what to do next.
                setState('error', 'Not saved — ' + err.message + '. Press Save to try again.');
            });
        });

        return saveChain;
    }

    /** POST the page/layout settings form over fetch, staying on the page. */
    function savePageForm(explicit) {
        if (!pageForm || (!pageFormDirty && !explicit)) return Promise.resolve();

        var body = new FormData(pageForm);

        // Only the explicit Save writes a version-history entry. Autosave says
        // so, otherwise every keystroke pause would evict the snapshots the
        // user actually wants back.
        if (!explicit) body.append('autosave', '1');

        return fetch(pageForm.action, {
            method: 'POST', // _method=PUT rides inside the body
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: body,
            credentials: 'same-origin'
        }).then(function (res) {
            return res.json().catch(function () { return {}; }).then(function (json) {
                if (res.ok) return json;

                var message = json.message || 'the server rejected the change';
                if (json.errors) {
                    message = Object.keys(json.errors).map(function (k) {
                        return json.errors[k].join(' ');
                    }).join(' ');
                }

                throw new Error(message);
            });
        });
    }

    /* Typing anywhere in the builder queues an autosave and an undo step. */
    root.addEventListener('input', onEdit, true);
    root.addEventListener('change', onEdit, true);

    function onEdit(e) {
        if (restoring) return;

        var target = e.target;
        if (!target.name && !target.classList.contains('gsm-richtext-input')) return;

        if (pageForm && pageForm.contains(target)) pageFormDirty = true;

        pushHistory();
        scheduleAutosave();
    }

    /* ── Submits ─────────────────────────────────────────────────────
       A section's own form and the one Save button now walk the same path.
    */

    root.addEventListener('submit', function (e) {
        var form = e.target;

        if (form.classList.contains('gsm-section-form') || form.classList.contains('gsm-visibility-form')) {
            e.preventDefault();
            setState('saving');

            saveSectionForm(form)
                .then(function () { savedNow(); reloadPreview(); })
                .catch(function (err) {
                    setState('error', 'Not saved — ' + err.message + '. Press Save to try again.');
                });

            return;
        }

        if (form.hasAttribute('data-gsm-primary-save')) {
            e.preventDefault();

            var button = document.querySelector('.gsm-save-btn');
            if (button) button.disabled = true;

            runSave(true).then(function () {
                if (button) button.disabled = false;
            });
        }
    });

    /* Last line of defence: a hard close mid-edit still warns. */
    window.addEventListener('beforeunload', function (e) {
        if (leavingDeliberately) return;

        if (dirtyForms().length || pageFormDirty) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    /* ── Inline editing ──────────────────────────────────────────────
       The preview iframe reports edits made on the page itself. They are
       written into the matching side-panel field and then travel the normal
       autosave path — so permissions, validation and the save indicator all
       behave exactly as if the user had typed in the panel.
    */

    window.addEventListener('message', function (event) {
        // Same-origin only: the preview is our own page, and a message from
        // anywhere else has no business writing into the editor.
        if (event.origin !== window.location.origin) return;

        var data = event.data;
        if (!data || data.source !== 'gsm-inline-edit') return;

        if (data.ready) {
            showInlineHint(data.count);

            return;
        }

        applyInlineEdit(data.sectionId, data.field, data.value);
    });

    function applyInlineEdit(sectionId, field, value) {
        var form = root.querySelector('.gsm-section-form[data-id="' + sectionId + '"][data-device="desktop"]');

        if (!form) return;

        var input = form.querySelector('[name="data[' + field + ']"]');

        if (!input) return;

        input.value = value;

        // Keep a rich-text editor in step with the textarea it shadows.
        if (window.jQuery && window.jQuery.fn.summernote && input.classList.contains('gsm-richtext-input')) {
            var $input = window.jQuery(input);
            if ($input.next('.note-editor').length) $input.summernote('code', value);
        }

        form.dataset.dirty = '1';
        pushHistory();      // an inline edit is undoable like any other
        scheduleAutosave();
    }

    /** Said once per session: after that it is just how the preview works. */
    function showInlineHint(count) {
        if (!count || window.sessionStorage.getItem('gsm-inline-hint')) return;

        toast('Tip: click text in the preview to edit it there.');

        try {
            window.sessionStorage.setItem('gsm-inline-hint', '1');
        } catch (err) {
            // Private mode: the hint simply shows again next time.
        }
    }

    /* ── Writing assistant ───────────────────────────────────────────
       Optional. The buttons only exist when the server rendered them, which
       it does only when an API key is configured. The rewritten text lands in
       the field like any other edit — it is undoable, autosaved, and the user
       can simply not save it.
    */

    root.addEventListener('click', function (e) {
        var action = e.target.closest('.gsm-ai-action');
        if (!action) return;

        var field = document.getElementById(action.dataset.target);
        if (!field) return;

        var original = field.value;

        if (!original.trim()) {
            toast('Write something first, then I can improve it.', true);

            return;
        }

        var button = action.closest('.gsm-ai').querySelector('.gsm-ai__btn');
        button.classList.add('is-busy');
        button.disabled = true;

        fetch(root.dataset.aiImproveUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ text: original, tone: action.dataset.tone })
        }).then(function (res) {
            return res.json().catch(function () { return {}; }).then(function (json) {
                if (!res.ok) throw new Error(json.message || 'The assistant could not help this time.');

                return json;
            });
        }).then(function (json) {
            field.value = json.text;

            // Route it through the same path as a typed edit so undo, the save
            // state and autosave all treat it identically.
            field.dispatchEvent(new Event('input', { bubbles: true }));

            toast('Rewritten — press Ctrl+Z to undo.');
        }).catch(function (err) {
            toast(err.message, true);
        }).finally(function () {
            button.classList.remove('is-busy');
            button.disabled = false;
        });
    });

    /* ── Version history ─────────────────────────────────────────────
       Opened from the bar; lists snapshots newest first, previews one in a
       new tab, restores after confirming.
    */

    var historyBody = document.querySelector('[data-gsm-history-body]');
    var historyUrl = root.dataset.revisionsUrl;

    function renderHistory(revisions) {
        if (!revisions.length) {
            historyBody.innerHTML =
                '<p class="text-muted mb-0">No versions yet. The first one is saved the next time you press Save.</p>';
            return;
        }

        historyBody.innerHTML = revisions.map(function (rev, index) {
            var when = new Date(rev.created_at).toLocaleString([], {
                day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit'
            });

            var meta = [rev.author, rev.sections + (rev.sections === 1 ? ' section' : ' sections')];
            if (rev.label) meta.unshift(rev.label);

            return '' +
                '<div class="gsm-revision' + (index === 0 ? ' gsm-revision--current' : '') + '">' +
                  '<div>' +
                    '<div class="gsm-revision__when">' + escapeHtml(when) +
                      (index === 0 ? ' <span class="badge badge-success ml-1">Current</span>' : '') +
                    '</div>' +
                    '<div class="gsm-revision__meta">' + escapeHtml(meta.join(' · ')) + '</div>' +
                  '</div>' +
                  '<div class="gsm-revision__actions">' +
                    '<a class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener" href="' +
                      escapeHtml(rev.preview_url) + '">Preview</a>' +
                    '<button type="button" class="btn btn-outline-primary btn-sm" data-gsm-restore="' +
                      escapeHtml(rev.restore_url) + '" data-when="' + escapeHtml(when) + '">Restore</button>' +
                  '</div>' +
                '</div>';
        }).join('');
    }

    function escapeHtml(value) {
        var div = document.createElement('div');
        div.textContent = value == null ? '' : String(value);

        return div.innerHTML;
    }

    function loadHistory() {
        if (!historyBody || !historyUrl) return;

        historyBody.innerHTML = '<p class="text-muted mb-0">Loading versions…</p>';

        fetch(historyUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
            .then(function (res) { return res.json(); })
            .then(function (json) { renderHistory(json.revisions || []); })
            .catch(function () {
                historyBody.innerHTML =
                    '<p class="text-danger mb-0">Could not load the version history. Close this and try again.</p>';
            });
    }

    var historyOpener = document.querySelector('[data-gsm-history-open]');
    if (historyOpener) historyOpener.addEventListener('click', loadHistory);

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-gsm-restore]');
        if (!btn) return;

        if (!window.confirm('Restore the version from ' + btn.dataset.when +
            '?\n\nYour current version is saved to history first, so you can undo this.')) {
            return;
        }

        btn.disabled = true;

        // Anything typed inside the autosave debounce is not on the server
        // yet. Flush it first — otherwise the snapshot taken during the
        // restore would miss it and the page-unload guard, which we are about
        // to bypass, would not be there to catch the loss.
        runSave(true).then(function () {
            if (stateEl && stateEl.dataset.state === 'error') {
                btn.disabled = false;
                window.alert('Your latest changes could not be saved, so the restore was cancelled.\n\n' +
                    'Fix the error shown at the top of the screen, then try again.');

                return;
            }

            var form = document.createElement('form');
            form.method = 'POST';
            form.action = btn.dataset.gsmRestore;
            form.innerHTML = '<input type="hidden" name="_token" value="' + escapeHtml(csrf) + '">';

            leavingDeliberately = true;
            document.body.appendChild(form);
            form.submit();
        });
    });
})();
