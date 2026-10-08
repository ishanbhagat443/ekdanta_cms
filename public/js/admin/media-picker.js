/**
 * GSM media picker component driver.
 * One shared modal iframe; each .gsm-media-picker opens it and receives
 * the selection via postMessage {type:'gsm-media-selected', media:[…]}.
 */
(function () {
    'use strict';

    var activePicker = null;

    function ensureModal() {
        var modal = document.getElementById('gsm-mp-modal');
        if (modal) return modal;

        modal = document.createElement('div');
        modal.id = 'gsm-mp-modal';
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
                  '<iframe id="gsm-mp-frame" src="about:blank" style="width:100%; height:72vh; border:0;"></iframe>' +
                '</div>' +
              '</div>' +
            '</div>';
        document.body.appendChild(modal);
        return modal;
    }

    function renderPreview(picker, mediaList) {
        var preview = picker.querySelector('.gsm-mp-preview');
        preview.innerHTML = '';

        if (!mediaList.length) {
            preview.innerHTML = '<i class="fas fa-image fa-2x text-muted"></i>';
            return;
        }

        mediaList.forEach(function (m) {
            if (m.thumb_url) {
                var img = document.createElement('img');
                img.src = m.thumb_url;
                img.alt = '';
                img.style.maxHeight = '72px';
                img.style.borderRadius = '4px';
                preview.appendChild(img);
            } else {
                var icon = document.createElement('i');
                icon.className = 'fas fa-file fa-2x text-secondary m-2';
                icon.title = m.filename;
                preview.appendChild(icon);
            }
        });
    }

    document.addEventListener('click', function (e) {
        var openBtn = e.target.closest('.gsm-mp-open');
        var clearBtn = e.target.closest('.gsm-mp-clear');

        if (openBtn) {
            activePicker = openBtn.closest('.gsm-media-picker');
            var modal = ensureModal();
            document.getElementById('gsm-mp-frame').src = activePicker.dataset.pickerSrc;
            window.jQuery(modal).modal('show');
        }

        if (clearBtn) {
            var picker = clearBtn.closest('.gsm-media-picker');
            picker.querySelector('.gsm-mp-value').value = '';
            renderPreview(picker, []);
            clearBtn.classList.add('d-none');
        }
    });

    window.addEventListener('message', function (e) {
        if (e.origin !== window.location.origin) return;
        if (!e.data || e.data.type !== 'gsm-media-selected' || !activePicker) return;

        var media = e.data.media || [];
        var multiple = activePicker.dataset.multiple === '1';
        var chosen = multiple ? media : media.slice(0, 1);

        activePicker.querySelector('.gsm-mp-value').value =
            chosen.map(function (m) { return m.id; }).join(',');

        renderPreview(activePicker, chosen);
        activePicker.querySelector('.gsm-mp-clear').classList.remove('d-none');

        window.jQuery('#gsm-mp-modal').modal('hide');
    });
})();
