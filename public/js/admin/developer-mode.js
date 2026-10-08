/**
 * Developer Mode (Super Admin only): per-section modal with Generated
 * HTML / CSS / JS editors, Blade path, component info, live preview,
 * compare-to-original and version history. Saves to the safe override
 * store — original Blade components are never touched.
 */
(function () {
    'use strict';

    var current = null; // { url, payload }

    function token() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : (window.Laravel && window.Laravel.csrfToken) || '';
    }

    function el(html) {
        var tpl = document.createElement('template');
        tpl.innerHTML = html.trim();
        return tpl.content.firstChild;
    }

    function esc(value) {
        var d = document.createElement('div');
        d.textContent = value == null ? '' : String(value);
        return d.innerHTML;
    }

    function ensureModal() {
        var modal = document.getElementById('gsm-devmode-modal');
        if (modal) return modal;

        modal = el(
        '<div class="modal fade" id="gsm-devmode-modal" tabindex="-1">' +
          '<div class="modal-dialog modal-xl" style="max-width:94vw;">' +
            '<div class="modal-content">' +
              '<div class="modal-header py-2 bg-dark text-light">' +
                '<h5 class="modal-title"><i class="fas fa-code mr-2"></i>Developer Mode — <span id="gsm-dm-title"></span>' +
                  ' <span id="gsm-dm-flag" class="badge badge-warning ml-2" style="display:none;">Override active</span></h5>' +
                '<button type="button" class="close text-light" data-dismiss="modal"><span>&times;</span></button>' +
              '</div>' +
              '<div class="modal-body p-0">' +
                '<ul class="nav nav-tabs px-2 pt-2" role="tablist">' +
                  ['HTML', 'CSS', 'JavaScript', 'Blade View', 'Component Info', 'Preview', 'Compare', 'History'].map(function (t, i) {
                      return '<li class="nav-item"><a class="nav-link' + (i === 0 ? ' active' : '') + '" data-toggle="tab" href="#gsm-dm-tab' + i + '">' + t + '</a></li>';
                  }).join('') +
                '</ul>' +
                '<div class="tab-content p-3">' +
                  '<div class="tab-pane fade show active" id="gsm-dm-tab0">' +
                    '<p class="text-muted small mb-1">Generated HTML (editing freezes this markup — CMS field edits will not re-render it until you Reset to Original).</p>' +
                    '<textarea id="gsm-dm-html" class="form-control text-monospace" rows="18" spellcheck="false"></textarea>' +
                  '</div>' +
                  '<div class="tab-pane fade" id="gsm-dm-tab1">' +
                    '<p class="text-muted small mb-1">CSS injected with this section (scope with the section selector shown in Component Info).</p>' +
                    '<textarea id="gsm-dm-css" class="form-control text-monospace" rows="18" spellcheck="false"></textarea>' +
                  '</div>' +
                  '<div class="tab-pane fade" id="gsm-dm-tab2">' +
                    '<p class="text-muted small mb-1">JavaScript injected with this section (runs on the page after the frontend bundle).</p>' +
                    '<textarea id="gsm-dm-js" class="form-control text-monospace" rows="18" spellcheck="false"></textarea>' +
                  '</div>' +
                  '<div class="tab-pane fade" id="gsm-dm-tab3">' +
                    '<p class="text-muted small mb-2">Rendered Blade view path:</p>' +
                    '<pre id="gsm-dm-blade" class="bg-light border rounded p-2"></pre>' +
                  '</div>' +
                  '<div class="tab-pane fade" id="gsm-dm-tab4"><table class="table table-sm mb-0" id="gsm-dm-info"></table></div>' +
                  '<div class="tab-pane fade" id="gsm-dm-tab5">' +
                    '<button type="button" class="btn btn-sm btn-outline-primary mb-2" id="gsm-dm-preview-run"><i class="fas fa-sync-alt mr-1"></i>Render Preview</button>' +
                    '<iframe id="gsm-dm-preview" style="width:100%;height:60vh;border:1px solid #dee2e6;border-radius:4px;background:#fff;"></iframe>' +
                  '</div>' +
                  '<div class="tab-pane fade" id="gsm-dm-tab6">' +
                    '<div class="row"><div class="col-6"><p class="small text-muted mb-1">Original (generated now)</p>' +
                    '<textarea id="gsm-dm-orig" class="form-control text-monospace" rows="16" readonly></textarea></div>' +
                    '<div class="col-6"><p class="small text-muted mb-1">Your override</p>' +
                    '<textarea id="gsm-dm-mine" class="form-control text-monospace" rows="16" readonly></textarea></div></div>' +
                  '</div>' +
                  '<div class="tab-pane fade" id="gsm-dm-tab7"><div id="gsm-dm-versions" class="list-group"></div></div>' +
                '</div>' +
              '</div>' +
              '<div class="modal-footer py-2">' +
                '<span id="gsm-dm-status" class="text-muted small mr-auto"></span>' +
                '<button type="button" class="btn btn-sm btn-outline-danger" id="gsm-dm-reset"><i class="fas fa-undo mr-1"></i>Reset to Original</button>' +
                '<button type="button" class="btn btn-sm btn-primary" id="gsm-dm-save"><i class="fas fa-save mr-1"></i>Save Override</button>' +
              '</div>' +
            '</div>' +
          '</div>' +
        '</div>');

        document.body.appendChild(modal);
        bindModal(modal);
        return modal;
    }

    function fields() {
        return {
            html: document.getElementById('gsm-dm-html').value,
            css: document.getElementById('gsm-dm-css').value,
            js: document.getElementById('gsm-dm-js').value
        };
    }

    function apply(payload) {
        current.payload = payload;
        document.getElementById('gsm-dm-title').textContent = payload.name + ' (#' + payload.section_id + ')';
        document.getElementById('gsm-dm-flag').style.display = payload.has_override ? '' : 'none';
        document.getElementById('gsm-dm-html').value = payload.html || '';
        document.getElementById('gsm-dm-css').value = payload.css || '';
        document.getElementById('gsm-dm-js').value = payload.js || '';
        document.getElementById('gsm-dm-blade').textContent = payload.info['Blade file'];
        document.getElementById('gsm-dm-orig').value = payload.original_html || '';
        document.getElementById('gsm-dm-mine').value = payload.html || '';

        var info = document.getElementById('gsm-dm-info');
        info.innerHTML = '';
        Object.keys(payload.info).forEach(function (key) {
            info.insertAdjacentHTML('beforeend',
                '<tr><th style="width:200px;">' + esc(key) + '</th><td><code>' + esc(payload.info[key]) + '</code></td></tr>');
        });
        info.insertAdjacentHTML('beforeend',
            '<tr><th>Section selector</th><td><code>#gsm-s' + payload.section_id + '</code></td></tr>');

        var versions = document.getElementById('gsm-dm-versions');
        versions.innerHTML = payload.versions.length ? '' :
            '<p class="text-muted small mb-0">No saved versions yet — every Save/Reset snapshots the previous state here.</p>';
        payload.versions.forEach(function (v) {
            var row = el('<div class="list-group-item d-flex align-items-center py-2">' +
                '<div><strong>#' + v.id + '</strong> <span class="text-muted small">' + esc(v.created_at) +
                (v.label ? ' — ' + esc(v.label) : '') + (v.author ? ' — ' + esc(v.author) : '') + '</span></div>' +
                '<button type="button" class="btn btn-xs btn-outline-primary ml-auto" data-version="' + v.id + '">' +
                '<i class="fas fa-history mr-1"></i>Restore</button></div>');
            versions.appendChild(row);
        });
    }

    function request(method, url, body) {
        return fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body ? JSON.stringify(body) : undefined
        }).then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r;
        });
    }

    function status(text) {
        document.getElementById('gsm-dm-status').textContent = text;
    }

    function bindModal(modal) {
        document.getElementById('gsm-dm-save').addEventListener('click', function () {
            status('Saving…');
            request('PUT', current.url, fields())
                .then(function (r) { return r.json(); })
                .then(function (payload) { apply(payload); status('Override saved ✓'); })
                .catch(function (e) { status('Save failed: ' + e.message); });
        });

        document.getElementById('gsm-dm-reset').addEventListener('click', function () {
            if (!confirm('Reset to the original component? Your override is kept in Version History.')) return;
            status('Resetting…');
            request('POST', current.url + '/reset')
                .then(function (r) { return r.json(); })
                .then(function (payload) { apply(payload); status('Reset — original component is live again ✓'); })
                .catch(function (e) { status('Reset failed: ' + e.message); });
        });

        document.getElementById('gsm-dm-preview-run').addEventListener('click', function () {
            status('Rendering preview…');
            request('POST', current.url + '/preview', fields())
                .then(function (r) { return r.text(); })
                .then(function (doc) {
                    document.getElementById('gsm-dm-preview').srcdoc = doc;
                    status('Preview rendered (unsaved changes included)');
                })
                .catch(function (e) { status('Preview failed: ' + e.message); });
        });

        document.getElementById('gsm-dm-versions').addEventListener('click', function (e) {
            var btn = e.target.closest('[data-version]');
            if (!btn) return;
            status('Restoring version #' + btn.dataset.version + '…');
            request('POST', current.url + '/versions/' + btn.dataset.version + '/restore')
                .then(function (r) { return r.json(); })
                .then(function (payload) { apply(payload); status('Version restored ✓'); })
                .catch(function (e) { status('Restore failed: ' + e.message); });
        });

        // Keep the Compare tab's "override" pane in sync while typing.
        document.getElementById('gsm-dm-html').addEventListener('input', function () {
            document.getElementById('gsm-dm-mine').value = this.value;
        });
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.gsm-sc-devmode');
        if (!btn) return;

        current = { url: btn.dataset.url, payload: null };
        var modal = ensureModal();
        status('Loading…');
        window.jQuery(modal).modal('show');

        request('GET', current.url)
            .then(function (r) { return r.json(); })
            .then(function (payload) { apply(payload); status('Loaded'); })
            .catch(function (e2) { status('Load failed: ' + e2.message); });
    });
})();
