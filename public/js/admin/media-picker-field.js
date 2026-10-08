/**
 * Lightweight media picker for builder fields (.gsm-bf-media-pick buttons).
 * Opens the Media Library picker in a modal iframe and writes the chosen
 * media ID into the target input.
 */
(function () {
    'use strict';

    var activeTargetId = null;

    function ensureModal(src) {
        var modal = document.getElementById('gsm-bf-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'gsm-bf-modal';
            modal.className = 'modal fade';
            modal.tabIndex = -1;
            modal.innerHTML =
                '<div class="modal-dialog modal-xl" style="max-width: 92vw;">' +
                  '<div class="modal-content">' +
                    '<div class="modal-header py-2">' +
                      '<h5 class="modal-title">Select Media</h5>' +
                      '<button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>' +
                    '</div>' +
                    '<div class="modal-body p-0">' +
                      '<iframe id="gsm-bf-frame" src="about:blank" style="width:100%; height:72vh; border:0;"></iframe>' +
                    '</div>' +
                  '</div>' +
                '</div>';
            document.body.appendChild(modal);
        }
        document.getElementById('gsm-bf-frame').src = src;
        return modal;
    }

    var activeTargetEl = null;

    function builderRoot() {
        return document.getElementById('gsm-header-builder')
            || document.getElementById('gsm-footer-builder')
            || document.getElementById('gsm-page-builder')
            || document.getElementById('gsm-menu-editor')
            || document.getElementById('gsm-global-block-editor')
            || document.body;
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.gsm-bf-media-pick');
        var nearBtn = e.target.closest('.gsm-bf-media-pick-near');
        if (!btn && !nearBtn) return;

        if (btn) {
            activeTargetId = btn.dataset.target;
            activeTargetEl = null;
        } else {
            // Repeater rows: write into the input inside the same input-group.
            activeTargetId = null;
            activeTargetEl = nearBtn.closest('.input-group').querySelector('input');
        }

        var src = builderRoot().dataset.pickerSrc || '/cms/media?picker=1';
        window.jQuery(ensureModal(src)).modal('show');
    });

    window.addEventListener('message', function (e) {
        if (e.origin !== window.location.origin) return;
        if (!e.data || e.data.type !== 'gsm-media-selected') return;
        if (!activeTargetId && !activeTargetEl) return;

        var media = (e.data.media || [])[0];
        if (media) {
            var input = activeTargetEl || document.getElementById(activeTargetId);
            if (input) input.value = media.id;
        }
        activeTargetId = null;
        activeTargetEl = null;
        window.jQuery('#gsm-bf-modal').modal('hide');
    });
})();
