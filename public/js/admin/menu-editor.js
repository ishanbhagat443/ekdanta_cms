/**
 * GSM Menu Editor — nested drag-drop tree + full-featured item CRUD (AJAX).
 */
(function () {
    'use strict';

    var root = document.getElementById('gsm-menu-editor');
    if (!root) return;

    var itemsUrl = root.dataset.itemsUrl;     // POST create
    var itemUrl = root.dataset.itemUrl;       // PUT/DELETE /{id}
    var treeUrl = root.dataset.treeUrl;       // POST save tree
    var referenceUrl = root.dataset.referenceUrl; // GET /{type}?q=
    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    var LINK_TYPES_WITH_URL = ['url', 'custom', 'anchor', 'phone', 'email'];
    var LINK_TYPES_WITH_REFERENCE = ['page', 'product_category', 'product', 'blog', 'blog_category'];
    var URL_LABELS = {
        url: 'URL', custom: 'URL / Path', anchor: 'Anchor (without #)', phone: 'Phone Number', email: 'Email Address'
    };
    var REFERENCE_LABELS = {
        page: 'Page', product_category: 'Product Category', product: 'Product',
        blog: 'Blog Post', blog_category: 'Blog Category'
    };
    var COLUMN_TYPES_NEEDING_IMAGE = ['image'];
    var COLUMN_TYPES_NEEDING_HTML = ['html'];
    var COLUMN_TYPES_NEEDING_CTA = ['cta'];
    var COLUMN_TYPES_NEEDING_FEATURED = ['featured_products', 'featured_categories'];

    function ajax(method, url, body) {
        return fetch(url, {
            method: method,
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
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

    /* ── Nested sortable on every list (root + children) ─────────────── */
    root.querySelectorAll('.gsm-tree').forEach(function (list) {
        new Sortable(list, {
            group: 'menu-tree',
            handle: '.gsm-tree-handle',
            animation: 150,
            fallbackOnBody: true,
            swapThreshold: 0.65
        });
    });

    function readTree(list) {
        return Array.prototype.filter.call(list.children, function (li) {
            return li.classList && li.classList.contains('gsm-tree-item');
        }).map(function (li) {
            var childList = li.querySelector(':scope > .gsm-tree');
            return {
                id: parseInt(li.dataset.id, 10),
                children: childList ? readTree(childList) : []
            };
        });
    }

    var saveTreeBtn = document.getElementById('gsm-save-tree');
    if (saveTreeBtn) {
        saveTreeBtn.addEventListener('click', function () {
            var tree = readTree(document.getElementById('gsm-tree-root'));
            ajax('POST', treeUrl, { tree: tree })
                .then(function () { toast('Menu structure saved'); })
                .catch(function (e) { toast(e.message, true); });
        });
    }

    /* ── Per-form field visibility + reference combobox wiring ───────── */

    function setupForm(form) {
        var linkTypeSelect = form.querySelector('.gsm-link-type');
        var urlField = form.querySelector('.gsm-field-url');
        var urlLabel = form.querySelector('.gsm-url-label');
        var urlInput = form.querySelector('input[name="url"]');
        var referenceField = form.querySelector('.gsm-field-reference');
        var referenceLabel = form.querySelector('.gsm-reference-label');
        var referenceSelect = form.querySelector('.gsm-reference-select');
        var isMegaCheckbox = form.querySelector('.gsm-is-mega');
        var megaField = form.querySelector('.gsm-field-mega');
        var columnHeaders = form.querySelectorAll('.gsm-field-column-header');
        var columnTypeField = form.querySelector('.gsm-field-column-type');
        var columnTypeSelect = form.querySelector('.gsm-column-type');
        var imageField = form.querySelector('.gsm-field-image');
        var htmlField = form.querySelector('.gsm-field-html');
        var ctaField = form.querySelector('.gsm-field-cta');
        var featuredField = form.querySelector('.gsm-field-featured');

        function show(el, visible) { if (el) el.style.display = visible ? '' : 'none'; }

        function refreshLinkTypeFields() {
            var type = linkTypeSelect.value;
            var needsUrl = LINK_TYPES_WITH_URL.indexOf(type) !== -1;
            var needsReference = LINK_TYPES_WITH_REFERENCE.indexOf(type) !== -1;

            show(urlField, needsUrl);
            show(referenceField, needsReference);

            if (needsUrl && urlLabel) urlLabel.textContent = URL_LABELS[type] || 'URL';
            if (needsReference && referenceLabel) referenceLabel.textContent = REFERENCE_LABELS[type] || 'Select';

            if (needsReference) {
                referenceSelect.dataset.type = type;
            }
        }

        function refreshMegaFields() {
            var isMega = isMegaCheckbox && isMegaCheckbox.checked;
            show(megaField, isMega);
            // Column-content fields only make sense on children of a mega item,
            // which the admin can't easily tell here — always offer them; the
            // frontend simply ignores column_type unless the parent is mega.
            columnHeaders.forEach(function (el) { show(el, true); });
            show(columnTypeField, true);
            refreshColumnTypeFields();
        }

        function refreshColumnTypeFields() {
            var type = columnTypeSelect ? columnTypeSelect.value : 'links';
            show(imageField, COLUMN_TYPES_NEEDING_IMAGE.indexOf(type) !== -1);
            show(htmlField, COLUMN_TYPES_NEEDING_HTML.indexOf(type) !== -1);
            show(ctaField, COLUMN_TYPES_NEEDING_CTA.indexOf(type) !== -1);
            show(featuredField, COLUMN_TYPES_NEEDING_FEATURED.indexOf(type) !== -1);
        }

        if (linkTypeSelect) {
            linkTypeSelect.addEventListener('change', refreshLinkTypeFields);
            refreshLinkTypeFields();
        }
        if (isMegaCheckbox) {
            isMegaCheckbox.addEventListener('change', refreshMegaFields);
            refreshMegaFields();
        }
        if (columnTypeSelect) {
            columnTypeSelect.addEventListener('change', refreshColumnTypeFields);
        }

        /* Reference combobox: plain <select> populated via fetch as the
           admin types into a companion search box (no external JS deps). */
        if (referenceSelect) {
            var wrap = referenceSelect.parentElement;
            var searchBox = document.createElement('input');
            searchBox.type = 'text';
            searchBox.className = 'form-control form-control-sm mb-1';
            searchBox.placeholder = 'Search…';
            wrap.insertBefore(searchBox, referenceSelect);

            var debounceTimer = null;
            searchBox.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                var term = searchBox.value;
                var type = referenceSelect.dataset.type;
                debounceTimer = setTimeout(function () {
                    if (!type) return;
                    fetch(referenceUrl + '/' + type + '?q=' + encodeURIComponent(term), {
                        headers: { 'Accept': 'application/json' }
                    }).then(function (r) { return r.json(); }).then(function (json) {
                        var current = referenceSelect.value;
                        referenceSelect.innerHTML = '';
                        (json.results || []).forEach(function (row) {
                            var opt = document.createElement('option');
                            opt.value = row.id;
                            opt.textContent = row.text;
                            if (String(row.id) === String(current)) opt.selected = true;
                            referenceSelect.appendChild(opt);
                        });
                    });
                }, 250);
            });
        }
    }

    setupForm(document.getElementById('gsm-add-item-form'));
    setupForm(document.getElementById('gsm-edit-item-form'));

    /* ── Serialize a form (incl. checkboxes + device checkbox group) ──── */
    function serialize(form) {
        var f = new FormData(form);
        var visibleDevices = f.getAll('visible_devices[]');

        return {
            label: f.get('label'),
            nav_label: f.get('nav_label') || null,
            link_type: f.get('link_type') || 'url',
            url: f.get('url') || null,
            reference_id: f.get('reference_id') ? parseInt(f.get('reference_id'), 10) : null,
            parent_id: f.get('parent_id') ? parseInt(f.get('parent_id'), 10) : null,
            icon: f.get('icon') || null,
            target: f.get('new_tab') ? '_blank' : '_self',
            css_class: f.get('css_class') || null,
            element_id: f.get('element_id') || null,
            badge_text: f.get('badge_text') || null,
            badge_color: f.get('badge_color') || null,
            permission: f.get('permission') || null,
            auth_only: !!f.get('auth_only'),
            guest_only: !!f.get('guest_only'),
            is_mega: !!f.get('is_mega'),
            mega_columns: f.get('mega_columns') ? parseInt(f.get('mega_columns'), 10) : 4,
            column_type: f.get('column_type') || 'links',
            image_media_id: f.get('image_media_id') ? parseInt(f.get('image_media_id'), 10) : null,
            custom_html: f.get('custom_html') || null,
            cta_label: f.get('cta_label') || null,
            cta_url: f.get('cta_url') || null,
            cta_style: f.get('cta_style') || null,
            featured_limit: f.get('featured_limit') ? parseInt(f.get('featured_limit'), 10) : 4,
            visible_devices: visibleDevices.length ? visibleDevices : ['desktop', 'tablet', 'mobile'],
            is_active: !!f.get('is_active')
        };
    }

    /* ── Add item ──────────────────────────────────────────────────────── */
    var addForm = document.getElementById('gsm-add-item-form');
    if (addForm) {
        addForm.addEventListener('submit', function (e) {
            e.preventDefault();
            ajax('POST', itemsUrl, serialize(addForm))
                .then(function () { window.location.reload(); })
                .catch(function (err) { toast(err.message, true); });
        });
    }

    /* ── Edit / delete (delegated) ────────────────────────────────────── */
    root.addEventListener('click', function (e) {
        var editBtn = e.target.closest('.gsm-item-edit');
        var deleteBtn = e.target.closest('.gsm-item-delete');

        if (editBtn) {
            var item = JSON.parse(editBtn.dataset.item);
            var form = document.getElementById('gsm-edit-item-form');
            form.elements.id.value = item.id;
            form.elements.label.value = item.label || '';
            form.elements.nav_label.value = item.nav_label || '';
            form.elements.link_type.value = item.link_type || 'url';
            form.elements.url.value = item.url || '';
            form.elements.icon.value = item.icon || '';
            form.elements.css_class.value = item.css_class || '';
            form.elements.element_id.value = item.element_id || '';
            form.elements.badge_text.value = item.badge_text || '';
            form.elements.badge_color.value = item.badge_color || '';
            form.elements.permission.value = item.permission || '';
            form.elements.new_tab.checked = item.target === '_blank';
            form.elements.auth_only.checked = !!item.auth_only;
            form.elements.guest_only.checked = !!item.guest_only;
            form.elements.is_mega.checked = !!item.is_mega;
            form.elements.mega_columns.value = item.mega_columns || 4;
            form.elements.column_type.value = item.column_type || 'links';
            form.elements.image_media_id.value = item.image_media_id || '';
            form.elements.custom_html.value = item.custom_html || '';
            form.elements.cta_label.value = item.cta_label || '';
            form.elements.cta_url.value = item.cta_url || '';
            form.elements.cta_style.value = item.cta_style || 'primary';
            form.elements.featured_limit.value = item.featured_limit || 4;
            form.elements.is_active.checked = !!item.is_active;

            var devices = item.visible_devices || ['desktop', 'tablet', 'mobile'];
            Array.prototype.forEach.call(form.querySelectorAll('.gsm-visible-device'), function (box) {
                box.checked = devices.indexOf(box.value) !== -1;
            });

            // Reference picker: pre-fill with the current reference (if any).
            var referenceSelect = form.querySelector('.gsm-reference-select');
            if (referenceSelect && item.reference_id) {
                referenceSelect.dataset.type = item.link_type;
                referenceSelect.innerHTML = '';
                var opt = document.createElement('option');
                opt.value = item.reference_id;
                opt.textContent = editBtn.dataset.referenceText || ('#' + item.reference_id);
                opt.selected = true;
                referenceSelect.appendChild(opt);
            }

            form.dispatchEvent(new Event('gsm:populated'));
            form.querySelector('.gsm-link-type').dispatchEvent(new Event('change'));
            form.querySelector('.gsm-is-mega').dispatchEvent(new Event('change'));
            if (form.querySelector('.gsm-column-type')) {
                form.querySelector('.gsm-column-type').dispatchEvent(new Event('change'));
            }

            window.jQuery('#gsm-item-modal').modal('show');
        }

        if (deleteBtn) {
            var li = deleteBtn.closest('.gsm-tree-item');
            var doDelete = function () {
                ajax('DELETE', itemUrl + '/' + li.dataset.id)
                    .then(function () { li.remove(); toast('Item deleted'); })
                    .catch(function (err) { toast(err.message, true); });
            };
            if (window.Swal) {
                Swal.fire({ title: 'Delete this item (and its children)?', icon: 'warning',
                    showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Delete' })
                    .then(function (r) { if (r.isConfirmed) doDelete(); });
            } else if (confirm('Delete this item?')) {
                doDelete();
            }
        }
    });

    /* ── Save edit modal ──────────────────────────────────────────────── */
    var editForm = document.getElementById('gsm-edit-item-form');
    if (editForm) {
        editForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var data = serialize(editForm);
            ajax('PUT', itemUrl + '/' + editForm.elements.id.value, data)
                .then(function () { window.location.reload(); })
                .catch(function (err) { toast(err.message, true); });
        });
    }
})();
