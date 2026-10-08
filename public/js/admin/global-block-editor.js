/**
 * GSM Global Block Editor — per-device save (reuses the same data[key] /
 * data[key][row][sub] serialization as header-builder.js's section forms,
 * since both render fields from admin/builder/partials/field.blade.php),
 * "update all pages" propagate toggle, and preview iframe controls.
 */
(function () {
    'use strict';

    var root = document.getElementById('gsm-global-block-editor');
    if (!root) return;

    var updateUrl = root.dataset.updateUrl;
    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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

    function reloadPreview() {
        var frame = document.getElementById('gsm-block-preview');
        if (frame) frame.contentWindow.location.reload();
    }

    /* Repeater add/remove (same markup/behavior as the builder field partial) */
    root.addEventListener('click', function (e) {
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

    /* Per-device content form save */
    root.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.classList.contains('gsm-block-form')) return;
        e.preventDefault();

        if (window.jQuery && window.jQuery.fn.summernote) {
            form.querySelectorAll('.gsm-richtext-input').forEach(function (el) {
                var $el = window.jQuery(el);
                if ($el.next('.note-editor').length) el.value = $el.summernote('code');
            });
        }

        var payload = { device: form.dataset.device, data: {} };
        var formData = new FormData(form);

        formData.forEach(function (value, name) {
            var match = name.match(/^data\[([^\]]+)\](?:\[([^\]]+)\]\[([^\]]+)\])?$/);
            if (!match) return;

            if (match[2] !== undefined) {
                payload.data[match[1]] = payload.data[match[1]] || {};
                payload.data[match[1]][match[2]] = payload.data[match[1]][match[2]] || {};
                payload.data[match[1]][match[2]][match[3]] = value;
            } else {
                payload.data[match[1]] = value;
            }
        });

        Object.keys(payload.data).forEach(function (k) {
            var v = payload.data[k];
            if (v && typeof v === 'object') payload.data[k] = Object.values(v);
        });

        var propagateToggle = form.querySelector('#propagate_toggle');
        payload.propagate = propagateToggle ? propagateToggle.checked : true;

        ajax('PUT', updateUrl, payload)
            .then(function () {
                toast(form.dataset.device.charAt(0).toUpperCase() + form.dataset.device.slice(1) + ' saved'
                    + (payload.propagate ? ' — pushed to linked pages' : ''));
                reloadPreview();
            })
            .catch(function (err) { toast(err.message, true); });
    });

    /* Responsive preview width toggle */
    root.querySelectorAll('.gsm-preview-size').forEach(function (btn) {
        btn.addEventListener('click', function () {
            root.querySelectorAll('.gsm-preview-size').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            var frame = document.getElementById('gsm-block-preview');
            if (frame) frame.style.width = btn.dataset.width;
        });
    });

    var refreshBtn = document.getElementById('gsm-block-refresh');
    if (refreshBtn) refreshBtn.addEventListener('click', reloadPreview);
})();
