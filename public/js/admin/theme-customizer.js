/**
 * GSM Theme Customizer — AJAX per-tab saves, live preview iframe reload,
 * responsive preview width toggle, and instant in-page typography preview.
 */
(function () {
    'use strict';

    var root = document.getElementById('gsm-theme-customizer');
    if (!root) return;

    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function toast(message, isError) {
        if (window.Swal) {
            Swal.fire({ toast: true, position: 'top-end', timer: 2200, showConfirmButton: false,
                icon: isError ? 'error' : 'success', title: message });
        } else if (isError) {
            alert(message);
        }
    }

    function reloadPreview() {
        var frame = document.getElementById('gsm-theme-preview');
        if (frame) frame.contentWindow.location.reload();
    }

    /* ── AJAX-submit every group form (colors/buttons/spacing/etc.) ───── */
    root.querySelectorAll('.gsm-theme-group-form, #gsm-typography-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            fetch(form.action, {
                method: 'POST', // Laravel method-spoofed via the form's _method field
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form)
            }).then(function (res) {
                if (res.redirected || res.ok) {
                    toast('Saved');
                    reloadPreview();
                } else {
                    return res.json().then(function (json) {
                        var msg = json.message || 'Save failed';
                        if (json.errors) {
                            msg = Object.keys(json.errors).map(function (k) { return json.errors[k].join(' '); }).join(' ');
                        }
                        toast(msg, true);
                    });
                }
            }).catch(function () { toast('Save failed — network error', true); });
        });
    });

    var refreshBtn = document.getElementById('gsm-theme-refresh');
    if (refreshBtn) refreshBtn.addEventListener('click', reloadPreview);

    /* ── Responsive preview width toggle ──────────────────────────────── */
    root.querySelectorAll('.gsm-preview-size').forEach(function (btn) {
        btn.addEventListener('click', function () {
            root.querySelectorAll('.gsm-preview-size').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            var frame = document.getElementById('gsm-theme-preview');
            if (frame) frame.style.width = btn.dataset.width;
        });
    });

    /* ── Color picker <-> text input sync ─────────────────────────────── */
    root.querySelectorAll('.gsm-theme-color-text').forEach(function (text) {
        var colorInput = document.getElementById(text.dataset.for);
        if (!colorInput) return;
        text.addEventListener('input', function () {
            if (/^#[0-9a-fA-F]{6}$/.test(text.value)) colorInput.value = text.value;
        });
        colorInput.addEventListener('input', function () { text.value = colorInput.value; });
    });

    /* ── Typography: live in-page preview per role ────────────────────── */
    root.querySelectorAll('.gsm-typo-preview').forEach(function (preview) {
        var role = preview.dataset.role;
        var fields = root.querySelectorAll('.gsm-typo-field[data-role="' + role + '"]');
        var familySelect = root.querySelector('.gsm-typo-family[data-role="' + role + '"]');
        var label = root.querySelector('.gsm-typo-preview-label[data-role="' + role + '"]');

        function apply() {
            fields.forEach(function (field) {
                var cssProp = field.dataset.css;
                var unit = field.dataset.unit || '';
                if (!cssProp) return;
                preview.style[cssProp] = field.value + unit;
            });

            if (familySelect) {
                var selectedOption = familySelect.options[familySelect.selectedIndex];
                var familyName = selectedOption.dataset.familyName || familySelect.value;
                preview.style.fontFamily = "'" + familyName + "'";
                if (label) label.textContent = '— ' + familyName;
            }
        }

        fields.forEach(function (field) { field.addEventListener('input', apply); field.addEventListener('change', apply); });
        if (familySelect) familySelect.addEventListener('change', apply);

        apply();
    });

    /* ── Accordion chevron flip (Bootstrap collapse) ──────────────────── */
    document.querySelectorAll('#gsm-typography-accordion [data-toggle="collapse"]').forEach(function (header) {
        var target = document.querySelector(header.dataset.target);
        if (!target) return;
        target.addEventListener('show.bs.collapse', function () {
            header.querySelector('.fa-angle-right, .fa-angle-down').className = 'fas fa-angle-down mr-2';
        });
        target.addEventListener('hide.bs.collapse', function () {
            header.querySelector('.fa-angle-right, .fa-angle-down').className = 'fas fa-angle-right mr-2';
        });
    });
})();
