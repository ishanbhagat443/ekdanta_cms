/**
 * Button Builder admin form: unlimited shadow rows + live preview with
 * desktop/tablet/mobile padding and a hover state mirroring the compiled CSS.
 */
(function () {
    'use strict';

    var form = document.getElementById('gsm-btn-form');
    if (!form) return;

    var preview = document.getElementById('gsm-btn-preview');
    var device = 'desktop';

    /* ── shadow repeaters ─────────────────────────────────────────── */

    function shadowRowHtml(prefix) {
        var id = 'inset-' + Math.random().toString(36).slice(2);
        return '<div class="gsm-shadow-row form-row align-items-end mb-2">' +
            ['x:0', 'y:4', 'blur:12', 'spread:0'].map(function (pair) {
                var p = pair.split(':');
                return '<div class="form-group col mb-0"><small class="text-muted">' + p[0].toUpperCase() + '</small>' +
                    '<input type="number" step="0.5" class="form-control form-control-sm" name="' + prefix + '[' + p[0] + ']" value="' + p[1] + '"></div>';
            }).join('') +
            '<div class="form-group col mb-0"><small class="text-muted">Color</small>' +
            '<input type="color" class="form-control form-control-sm" style="padding:2px; height:31px;" name="' + prefix + '[color]" value="#000000"></div>' +
            '<div class="form-group col mb-0"><small class="text-muted">Opacity %</small>' +
            '<input type="number" min="0" max="100" class="form-control form-control-sm" name="' + prefix + '[opacity]" value="25"></div>' +
            '<div class="form-group col-auto mb-0"><div class="custom-control custom-checkbox">' +
            '<input type="hidden" name="' + prefix + '[inset]" value="0">' +
            '<input type="checkbox" class="custom-control-input" id="' + id + '" name="' + prefix + '[inset]" value="1">' +
            '<label class="custom-control-label" for="' + id + '"><small>Inset</small></label></div></div>' +
            '<div class="form-group col-auto mb-0"><button type="button" class="btn btn-xs btn-outline-danger gsm-shadow-delete"><i class="fas fa-times"></i></button></div>' +
            '</div>';
    }

    document.addEventListener('click', function (e) {
        var add = e.target.closest('.gsm-shadow-add');
        var del = e.target.closest('.gsm-shadow-delete');

        if (add) {
            var wrap = document.getElementById(add.dataset.target);
            var index = wrap.querySelectorAll('.gsm-shadow-row').length;
            wrap.insertAdjacentHTML('beforeend', shadowRowHtml(wrap.dataset.name + '[' + index + ']'));
            refresh();
        }
        if (del) {
            del.closest('.gsm-shadow-row').remove();
            refresh();
        }
    });

    /* ── live preview ─────────────────────────────────────────────── */

    function val(name, fallback) {
        var el = form.querySelector('[name="' + name + '"]');
        if (!el) return fallback;
        if (el.type === 'checkbox') return el.checked;
        return el.value !== '' ? el.value : fallback;
    }

    function shadowsCss(wrapId) {
        var parts = [];
        document.getElementById(wrapId).querySelectorAll('.gsm-shadow-row').forEach(function (row) {
            var inputs = row.querySelectorAll('input[type=number], input[type=color], input[type=checkbox]');
            // order: x, y, blur, spread, color, opacity, inset-checkbox
            var x = inputs[0].value || 0, y = inputs[1].value || 0, blur = inputs[2].value || 0, spread = inputs[3].value || 0;
            var color = inputs[4].value, opacity = parseFloat(inputs[5].value || 100);
            var inset = inputs[6] && inputs[6].checked;
            var h = color.replace('#', '');
            var rgba = opacity >= 100 ? color
                : 'rgba(' + parseInt(h.substr(0, 2), 16) + ',' + parseInt(h.substr(2, 2), 16) + ',' + parseInt(h.substr(4, 2), 16) + ',' + (opacity / 100) + ')';
            parts.push((inset ? 'inset ' : '') + x + 'px ' + y + 'px ' + blur + 'px ' + spread + 'px ' + rgba);
        });
        return parts.join(', ');
    }

    function backgroundCss(prefix, fallbackMode) {
        var mode = val(prefix + '[mode]', fallbackMode);
        if (mode === 'solid') return { background: val(prefix + '[color]', '#111827') };
        if (mode === 'gradient') {
            var slug = val(prefix + '[gradient_slug]', '');
            return { backgroundImage: (window.gsmGradients || {})[slug] || 'linear-gradient(90deg,#111827,#4b5563)' };
        }
        if (mode === 'transparent') return { background: 'transparent' };
        if (mode === 'image' || mode === 'video') return { background: '#101828' };
        return null;
    }

    function radius(prefix) {
        var map = { tl: 'borderTopLeftRadius', tr: 'borderTopRightRadius', br: 'borderBottomRightRadius', bl: 'borderBottomLeftRadius' };
        var out = {};
        Object.keys(map).forEach(function (corner) {
            var v = val(prefix + '[' + corner + ']', '');
            if (v) out[map[corner]] = /(px|%|rem|em)$/.test(v) ? v : v + 'px';
        });
        return out;
    }

    function apply(el, styles) {
        Object.keys(styles || {}).forEach(function (k) { el.style[k] = styles[k]; });
    }

    function refresh() {
        // reset
        preview.removeAttribute('style');
        preview.style.display = 'inline-flex';
        preview.style.alignItems = 'center';
        preview.style.textDecoration = 'none';
        preview.style.gap = (val('config[icon][gap]', 8)) + 'px';

        // typography (role fonts resolve on the frontend; preview inherits)
        var size = val('config[typography][size]', '');
        if (size) preview.style.fontSize = size + 'px';
        preview.style.fontWeight = val('config[typography][weight]', '600');
        var ls = val('config[typography][letter_spacing]', '');
        if (ls !== '') preview.style.letterSpacing = ls + 'px';
        preview.style.textTransform = val('config[typography][transform]', 'none');
        var lh = val('config[typography][line_height]', '');
        if (lh !== '') preview.style.lineHeight = lh;

        // padding for the selected preview device (inherits desktop when empty)
        ['top', 'right', 'bottom', 'left'].forEach(function (side) {
            var v = val('config[padding][' + device + '][' + side + ']', val('config[padding][desktop][' + side + ']', 12));
            preview.style['padding' + side.charAt(0).toUpperCase() + side.slice(1)] = v + 'px';
        });

        // border + radius
        var bw = val('config[border][width]', 0);
        preview.style.border = bw > 0
            ? bw + 'px ' + val('config[border][style]', 'solid') + ' ' + val('config[border][color]', '#fff')
            : '0';
        apply(preview, radius('config[border][radius]'));

        // background + text
        apply(preview, backgroundCss('config[background]', 'gradient'));
        preview.style.color = val('config[background][text_color]', '#ffffff');

        // shadows
        var sh = shadowsCss('gsm-shadow-rows');
        if (sh) preview.style.boxShadow = sh;

        // icons
        ['left', 'right'].forEach(function (side) {
            var icon = document.getElementById('gsm-preview-icon-' + side);
            var cls = val('config[icon][' + side + ']', '');
            icon.className = cls ? cls : 'd-none';
        });
    }

    // Hover simulation mirroring the compiled hover rules.
    preview.addEventListener('mouseenter', function () {
        var hoverBg = backgroundCss('config[hover]', '');
        if (hoverBg) { preview.style.backgroundImage = ''; apply(preview, hoverBg); }
        var tc = val('config[hover][text_color]', '');
        if (tc) preview.style.color = tc;
        var bw = val('config[hover][border][width]', '');
        if (bw !== '') preview.style.border = bw + 'px ' + val('config[hover][border][style]', 'solid') + ' ' + val('config[hover][border][color]', '#fff');
        apply(preview, radius('config[hover][radius]'));
        var sh = shadowsCss('gsm-hover-shadow-rows');
        if (sh) preview.style.boxShadow = sh;

        var fx = val('config[animation][hover_effect]', 'none');
        var duration = val('config[animation][duration]', 250);
        preview.style.transition = 'all ' + duration + 'ms ease';
        if (fx === 'scale') preview.style.transform = 'scale(1.05)';
        if (fx === 'lift') preview.style.transform = 'translateY(-3px)';
        if (fx === 'slide') preview.style.transform = 'translateX(4px)';
        if (fx === 'glow') preview.style.filter = 'brightness(1.1) drop-shadow(0 0 12px rgba(255,255,255,.35))';
        if (fx === 'pulse') preview.style.animation = 'gsm-btn-pulse ' + duration + 'ms ease-in-out infinite';
    });
    preview.addEventListener('mouseleave', refresh);

    document.querySelectorAll('.gsm-preview-device').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.gsm-preview-device').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            device = btn.dataset.device;
            refresh();
        });
    });

    form.addEventListener('input', refresh);
    form.addEventListener('change', refresh);
    refresh();
})();
