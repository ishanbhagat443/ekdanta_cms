/**
 * GSM Media Library — WordPress-style, fully AJAX.
 *
 * Modes (data attributes on #media-library):
 *  - default: full library management
 *  - picker:  selection mode inside an iframe; posts selection to parent
 *             window via postMessage {type:'gsm-media-selected', media:[…]}
 *  - ckeditor: CKEditor 4 filebrowser mode (CKEditorFuncNum callback)
 */
(function () {
    'use strict';

    var root = document.getElementById('media-library');
    if (!root) return;

    var cfg = {
        browseUrl: root.dataset.browseUrl,
        uploadUrl: root.dataset.uploadUrl,
        mediaUrl: root.dataset.mediaUrl,
        folderUrl: root.dataset.folderUrl,
        bulkDeleteUrl: root.dataset.bulkDeleteUrl,
        picker: root.dataset.picker === '1',
        multiple: root.dataset.multiple === '1',
        ckeditor: root.dataset.ckeditor || ''
    };

    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    var state = {
        folder: null,     // current folder id or null (root)
        search: '',
        type: '',
        page: 1,
        lastPage: 1,
        items: {},        // id -> media object (current view)
        selected: [],     // ids (selection order preserved)
        detailId: null
    };

    /* ── helpers ─────────────────────────────────────────────────── */

    function ajax(method, url, body, isForm) {
        var opts = {
            method: method,
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: body || undefined
        };
        if (body && !isForm) {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(body);
        }
        return fetch(url, opts).then(function (res) {
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
            Swal.fire({ toast: true, position: 'top-end', timer: 2500, showConfirmButton: false,
                icon: isError ? 'error' : 'success', title: message });
        } else if (isError) {
            alert(message);
        }
    }

    function confirmDialog(text) {
        if (window.Swal) {
            return Swal.fire({
                title: 'Are you sure?', text: text, icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Delete'
            }).then(function (r) { return r.isConfirmed; });
        }
        return Promise.resolve(confirm(text));
    }

    function el(id) { return document.getElementById(id); }

    function typeIcon(type, mime) {
        if (type === 'pdf') return 'fa-file-pdf text-danger';
        if (type === 'video') return 'fa-file-video text-purple';
        if (mime && mime.indexOf('word') !== -1) return 'fa-file-word text-primary';
        if (mime && (mime.indexOf('excel') !== -1 || mime.indexOf('sheet') !== -1)) return 'fa-file-excel text-success';
        if (mime && mime.indexOf('zip') !== -1) return 'fa-file-archive text-warning';
        return 'fa-file-alt text-secondary';
    }

    /* ── rendering ───────────────────────────────────────────────── */

    function folderCard(folder) {
        var col = document.createElement('div');
        col.className = 'col-6 col-sm-4 col-md-3 col-xl-2 mb-3';
        col.innerHTML =
            '<div class="card h-100 ml-folder" data-id="' + folder.id + '" style="cursor:pointer;">' +
              '<div class="card-body text-center py-3">' +
                '<i class="fas fa-folder fa-3x text-warning"></i>' +
                '<div class="mt-2 text-truncate" title="' + folder.name + '">' + folder.name + '</div>' +
                '<small class="text-muted">' + folder.media_count + ' file(s)</small>' +
              '</div>' +
              '<div class="ml-folder-actions position-absolute" style="top:4px; right:6px;">' +
                '<a href="#" class="text-muted ml-folder-rename" title="Rename"><i class="fas fa-pen fa-xs"></i></a> ' +
                '<a href="#" class="text-muted ml-folder-delete" title="Delete"><i class="fas fa-trash fa-xs"></i></a>' +
              '</div>' +
            '</div>';
        return col;
    }

    function mediaCard(media) {
        var col = document.createElement('div');
        col.className = 'col-6 col-sm-4 col-md-3 col-xl-2 mb-3';

        var thumb;
        if (media.thumb_url) {
            thumb = '<img src="' + media.thumb_url + '" alt="" class="w-100" ' +
                    'style="height:120px; object-fit:cover;">';
        } else {
            thumb = '<div class="d-flex align-items-center justify-content-center bg-light" style="height:120px;">' +
                    '<i class="fas ' + typeIcon(media.type, media.mime) + ' fa-3x"></i></div>';
        }

        var selected = state.selected.indexOf(media.id) !== -1;

        // An image with no description is invisible to screen readers and to
        // image search. Flag it on the card so it can be found and fixed,
        // rather than only noticing inside the details panel.
        var needsAlt = media.type === 'image' && !(media.alt || '').trim();

        var altBadge = needsAlt
            ? '<div class="position-absolute ml-needs-alt" style="top:6px; right:8px;" ' +
                   'title="No description yet — add one so screen readers and search engines can read it">' +
                '<span class="badge badge-warning"><i class="fas fa-eye-slash mr-1"></i>No description</span>' +
              '</div>'
            : '';

        col.innerHTML =
            '<div class="card h-100 ml-item ' + (selected ? 'border-primary' : '') + (needsAlt ? ' ml-item--needs-alt' : '') +
                 '" data-id="' + media.id + '" style="cursor:pointer;">' +
              thumb +
              '<div class="card-body p-2">' +
                '<div class="text-truncate small" title="' + (media.filename || '') + '">' + (media.filename || '') + '</div>' +
                '<small class="text-muted">' + (media.human_size || '') + '</small>' +
              '</div>' +
              '<div class="position-absolute" style="top:6px; left:8px;">' +
                '<input type="checkbox" class="ml-select" ' + (selected ? 'checked' : '') + '>' +
              '</div>' +
              altBadge +
            '</div>';
        return col;
    }

    function render(data, append) {
        var grid = el('ml-grid');
        if (!append) {
            grid.innerHTML = '';
            state.items = {};
        }

        // Folders only at page 1 of an unfiltered view.
        if (!append && !state.search && !state.type) {
            data.folders.forEach(function (f) { grid.appendChild(folderCard(f)); });
        }

        data.media.data.forEach(function (m) {
            state.items[m.id] = m;
            grid.appendChild(mediaCard(m));
        });

        state.page = data.media.current_page;
        state.lastPage = data.media.last_page;

        el('ml-load-more').classList.toggle('d-none', state.page >= state.lastPage);
        el('ml-empty').classList.toggle('d-none',
            grid.children.length > 0);

        renderBreadcrumb(data.trail || []);
        updateSelectionUi();
    }

    function renderBreadcrumb(trail) {
        var ol = el('ml-breadcrumb');
        ol.innerHTML = '';

        var rootLi = document.createElement('li');
        rootLi.className = 'breadcrumb-item';
        if (trail.length === 0) {
            rootLi.classList.add('active');
            rootLi.textContent = 'Library';
        } else {
            rootLi.innerHTML = '<a href="#" data-folder="">Library</a>';
        }
        ol.appendChild(rootLi);

        trail.forEach(function (f, i) {
            var li = document.createElement('li');
            li.className = 'breadcrumb-item';
            if (i === trail.length - 1) {
                li.classList.add('active');
                li.textContent = f.name;
            } else {
                li.innerHTML = '<a href="#" data-folder="' + f.id + '">' + f.name + '</a>';
            }
            ol.appendChild(li);
        });
    }

    /* ── loading ─────────────────────────────────────────────────── */

    function load(append) {
        var params = new URLSearchParams();
        if (state.folder !== null) params.set('folder', state.folder);
        if (state.search) params.set('search', state.search);
        if (state.type) params.set('type', state.type);
        params.set('page', append ? state.page + 1 : 1);

        ajax('GET', cfg.browseUrl + '?' + params.toString())
            .then(function (data) { render(data, !!append); })
            .catch(function (e) { toast(e.message, true); });
    }

    /* ── uploads ─────────────────────────────────────────────────── */

    function uploadFiles(fileList) {
        var files = Array.prototype.slice.call(fileList);
        if (!files.length) return;

        var form = new FormData();
        files.forEach(function (f) { form.append('files[]', f); });
        if (state.folder !== null) form.append('folder_id', state.folder);

        var progress = el('ml-progress');
        progress.classList.remove('d-none');
        el('ml-progress-text').textContent = 'Uploading ' + files.length + ' file(s)…';
        progress.querySelector('.progress-bar').style.width = '60%';

        ajax('POST', cfg.uploadUrl, form, true)
            .then(function () {
                progress.querySelector('.progress-bar').style.width = '100%';
                setTimeout(function () { progress.classList.add('d-none'); }, 400);
                toast('Uploaded ' + files.length + ' file(s)');
                load();
            })
            .catch(function (e) {
                progress.classList.add('d-none');
                toast(e.message, true);
            });
    }

    /* ── selection / picker ──────────────────────────────────────── */

    function toggleSelect(id) {
        var idx = state.selected.indexOf(id);
        if (idx === -1) {
            if (cfg.picker && !cfg.multiple) state.selected = [];
            state.selected.push(id);
        } else {
            state.selected.splice(idx, 1);
        }
        // Re-sync card highlight + checkboxes without a full reload.
        document.querySelectorAll('.ml-item').forEach(function (card) {
            var cardId = parseInt(card.dataset.id, 10);
            var on = state.selected.indexOf(cardId) !== -1;
            card.classList.toggle('border-primary', on);
            card.querySelector('.ml-select').checked = on;
        });
        updateSelectionUi();
    }

    function updateSelectionUi() {
        var n = state.selected.length;
        el('ml-selected-count').textContent = n;
        el('ml-bulk-delete').classList.toggle('d-none', n === 0 || cfg.picker);
        var confirmBtn = el('ml-picker-confirm');
        if (confirmBtn) confirmBtn.classList.toggle('d-none', n === 0);
    }

    function confirmPickerSelection() {
        var chosen = state.selected.map(function (id) { return state.items[id]; }).filter(Boolean);
        if (!chosen.length) return;

        if (cfg.ckeditor) {
            // CKEditor 4 filebrowser callback.
            window.opener.CKEDITOR.tools.callFunction(parseInt(cfg.ckeditor, 10), chosen[0].url);
            window.close();
            return;
        }

        window.parent.postMessage({ type: 'gsm-media-selected', media: chosen }, window.location.origin);
    }

    /* ── details modal ───────────────────────────────────────────── */

    function openDetail(id) {
        var m = state.items[id];
        if (!m) return;
        state.detailId = id;

        var preview = el('mld-preview');
        if (m.type === 'image') {
            preview.innerHTML = '<img src="' + (m.variants && m.variants.card ? m.url : m.url) + '" class="img-fluid" alt="">';
        } else if (m.type === 'video') {
            preview.innerHTML = '<video src="' + m.url + '" controls class="w-100"></video>';
        } else if (m.type === 'pdf') {
            preview.innerHTML = '<i class="fas fa-file-pdf fa-5x text-danger"></i>';
        } else {
            preview.innerHTML = '<i class="fas ' + typeIcon(m.type, m.mime) + ' fa-5x"></i>';
        }

        el('mld-meta').textContent = m.mime + ' · ' + m.human_size +
            (m.width ? ' · ' + m.width + '×' + m.height + 'px' : '');
        el('mld-title').value = m.title || '';
        el('mld-alt').value = m.alt || '';
        el('mld-url').value = m.url;

        window.jQuery('#ml-detail-modal').modal('show');
    }

    /* ── events ──────────────────────────────────────────────────── */

    // Toolbar
    el('ml-upload-btn').addEventListener('click', function () { el('ml-file-input').click(); });
    el('ml-file-input').addEventListener('change', function () { uploadFiles(this.files); this.value = ''; });

    el('ml-new-folder-btn').addEventListener('click', function () {
        var name = prompt('Folder name:');
        if (!name) return;
        ajax('POST', cfg.folderUrl, { name: name, parent_id: state.folder })
            .then(function () { toast('Folder created'); load(); })
            .catch(function (e) { toast(e.message, true); });
    });

    el('ml-search-btn').addEventListener('click', function () {
        state.search = el('ml-search').value.trim();
        load();
    });
    el('ml-search').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); state.search = this.value.trim(); load(); }
    });
    el('ml-type-filter').addEventListener('change', function () {
        state.type = this.value;
        load();
    });
    el('ml-load-more').addEventListener('click', function () { load(true); });

    el('ml-bulk-delete').addEventListener('click', function () {
        confirmDialog('Delete ' + state.selected.length + ' selected file(s)? This cannot be undone.')
            .then(function (ok) {
                if (!ok) return;
                ajax('POST', cfg.bulkDeleteUrl, { ids: state.selected })
                    .then(function (r) {
                        toast('Deleted ' + r.deleted + ' file(s)');
                        state.selected = [];
                        load();
                    })
                    .catch(function (e) { toast(e.message, true); });
            });
    });

    var pickerConfirm = el('ml-picker-confirm');
    if (pickerConfirm) pickerConfirm.addEventListener('click', confirmPickerSelection);

    // Grid delegation: folders, items, checkboxes, folder actions
    el('ml-grid').addEventListener('click', function (e) {
        var renameLink = e.target.closest('.ml-folder-rename');
        var deleteLink = e.target.closest('.ml-folder-delete');
        var folderCardEl = e.target.closest('.ml-folder');
        var checkbox = e.target.closest('.ml-select');
        var itemCard = e.target.closest('.ml-item');

        if (renameLink && folderCardEl) {
            e.preventDefault(); e.stopPropagation();
            var newName = prompt('Rename folder:');
            if (!newName) return;
            ajax('PUT', cfg.folderUrl + '/' + folderCardEl.dataset.id, { name: newName })
                .then(function () { load(); })
                .catch(function (err) { toast(err.message, true); });
            return;
        }

        if (deleteLink && folderCardEl) {
            e.preventDefault(); e.stopPropagation();
            confirmDialog('Delete this folder? It must be empty.')
                .then(function (ok) {
                    if (!ok) return;
                    ajax('DELETE', cfg.folderUrl + '/' + folderCardEl.dataset.id)
                        .then(function () { toast('Folder deleted'); load(); })
                        .catch(function (err) { toast(err.message, true); });
                });
            return;
        }

        if (folderCardEl) {
            state.folder = parseInt(folderCardEl.dataset.id, 10);
            state.search = '';
            el('ml-search').value = '';
            load();
            return;
        }

        if (checkbox && itemCard) {
            toggleSelect(parseInt(itemCard.dataset.id, 10));
            return;
        }

        if (itemCard) {
            var id = parseInt(itemCard.dataset.id, 10);
            if (cfg.picker) {
                toggleSelect(id);
            } else {
                openDetail(id);
            }
        }
    });

    // Double-click in picker = immediate confirm (WordPress behavior)
    el('ml-grid').addEventListener('dblclick', function (e) {
        if (!cfg.picker) return;
        var itemCard = e.target.closest('.ml-item');
        if (!itemCard) return;
        var id = parseInt(itemCard.dataset.id, 10);
        if (state.selected.indexOf(id) === -1) toggleSelect(id);
        confirmPickerSelection();
    });

    // Breadcrumb navigation
    el('ml-breadcrumb').addEventListener('click', function (e) {
        var link = e.target.closest('a[data-folder]');
        if (!link) return;
        e.preventDefault();
        state.folder = link.dataset.folder === '' ? null : parseInt(link.dataset.folder, 10);
        load();
    });

    // Drag & drop
    var dropzone = el('ml-dropzone');
    var overlay = el('ml-drop-overlay');
    var dragDepth = 0;

    ['dragenter', 'dragover'].forEach(function (evt) {
        dropzone.addEventListener(evt, function (e) {
            e.preventDefault();
            if (evt === 'dragenter') dragDepth++;
            overlay.classList.remove('d-none');
            overlay.classList.add('d-flex');
        });
    });
    dropzone.addEventListener('dragleave', function (e) {
        e.preventDefault();
        if (--dragDepth <= 0) {
            dragDepth = 0;
            overlay.classList.add('d-none');
            overlay.classList.remove('d-flex');
        }
    });
    dropzone.addEventListener('drop', function (e) {
        e.preventDefault();
        dragDepth = 0;
        overlay.classList.add('d-none');
        overlay.classList.remove('d-flex');
        if (e.dataTransfer && e.dataTransfer.files.length) uploadFiles(e.dataTransfer.files);
    });

    // Details modal actions
    el('mld-save').addEventListener('click', function () {
        ajax('PUT', cfg.mediaUrl + '/' + state.detailId, {
            title: el('mld-title').value,
            alt: el('mld-alt').value
        }).then(function (r) {
            state.items[state.detailId] = Object.assign(state.items[state.detailId] || {}, r.media);
            toast('Saved');
            window.jQuery('#ml-detail-modal').modal('hide');
            load();
        }).catch(function (e) { toast(e.message, true); });
    });

    el('mld-copy').addEventListener('click', function () {
        el('mld-url').select();
        document.execCommand('copy');
        toast('URL copied');
    });

    el('mld-replace').addEventListener('click', function () { el('mld-replace-input').click(); });
    el('mld-replace-input').addEventListener('change', function () {
        if (!this.files.length) return;
        var form = new FormData();
        form.append('file', this.files[0]);
        this.value = '';
        ajax('POST', cfg.mediaUrl + '/' + state.detailId + '/replace', form, true)
            .then(function () {
                toast('File replaced');
                window.jQuery('#ml-detail-modal').modal('hide');
                load();
            })
            .catch(function (e) { toast(e.message, true); });
    });

    el('mld-delete').addEventListener('click', function () {
        confirmDialog('Delete this file? This cannot be undone.')
            .then(function (ok) {
                if (!ok) return;
                ajax('DELETE', cfg.mediaUrl + '/' + state.detailId)
                    .then(function () {
                        toast('Deleted');
                        window.jQuery('#ml-detail-modal').modal('hide');
                        load();
                    })
                    .catch(function (e) { toast(e.message, true); });
            });
    });

    // Initial load
    load();
})();
