/**
 * Blog post form: rich editor with Media-Library image uploads.
 */
(function () {
    'use strict';

    if (!window.jQuery || !window.jQuery.fn.summernote) return;

    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var $ = window.jQuery;

    // "Insert block": the article template's blocks (buyer journey, compare
    // rows, …), pasted at the cursor and then edited in place.
    var blocksEl = document.getElementById('gsm-article-blocks');
    var blocks = blocksEl ? JSON.parse(blocksEl.textContent) : {};
    var insertBlock = function (context) {
        var ui = $.summernote.ui;
        return ui.buttonGroup([
            ui.button({
                className: 'dropdown-toggle',
                contents: 'Insert block <span class="caret"></span>',
                tooltip: 'Insert an article block',
                data: { toggle: 'dropdown' }
            }),
            ui.dropdown({
                items: Object.keys(blocks),
                template: function (name) { return $('<span>').text(blocks[name].label).html(); },
                click: function (e) {
                    e.preventDefault();
                    var name = $(e.target).closest('[data-value]').data('value');
                    if (blocks[name]) context.invoke('editor.pasteHTML', blocks[name].html);
                }
            })
        ]).render();
    };

    window.jQuery('.gsm-summernote').summernote({
        height: 300,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link', 'picture', 'video', 'table', 'hr']],
            ['blocks', Object.keys(blocks).length ? ['insertBlock'] : []],
            ['view', ['fullscreen', 'codeview']],
        ],
        buttons: { insertBlock: insertBlock },
        callbacks: {
            onImageUpload: function (files) {
                var editor = window.jQuery(this);
                var form = new FormData();
                form.append('upload', files[0]);
                fetch('/cms/media/ckeditor', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf },
                    body: form
                }).then(function (r) { return r.json(); })
                  .then(function (json) { if (json.url) editor.summernote('insertImage', json.url); });
            }
        }
    });
})();
