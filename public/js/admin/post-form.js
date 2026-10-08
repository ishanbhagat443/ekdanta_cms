/**
 * Blog post form: rich editor with Media-Library image uploads.
 */
(function () {
    'use strict';

    if (!window.jQuery || !window.jQuery.fn.summernote) return;

    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    window.jQuery('.gsm-summernote').summernote({
        height: 300,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link', 'picture', 'video', 'table', 'hr']],
            ['view', ['fullscreen', 'codeview']],
        ],
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
