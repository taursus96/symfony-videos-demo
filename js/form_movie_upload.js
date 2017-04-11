"use strict";
(function($) {
    var init = function() {
        $('#movie_file').on('change', onFileChange).change();
        $('#movie_preview').on('change', onPreviewChange).change();
        $('.form-movie-upload').on('submit', onFormSubmit);
    };

    var onFileChange = function() {
        var filePath = $(this).val();
        var fileName = getFileNameFromPath(filePath);

        updateFileName(fileName);
    };

    var updateFileName = function(fileName) {
        $('#movie-selected-file-name').text(fileName);

        if (fileName) {
            $('#movie-selected-file-container').show();
        } else {
            $('#movie-selected-file-container').hide();
        }
    };

    var onPreviewChange = function() {
        var filePath = $(this).val();
        var fileName = getFileNameFromPath(filePath);

        updatePreviewName(fileName);
    };

    var updatePreviewName = function(fileName) {
        $('#movie-selected-preview-name').text(fileName);

        if (fileName) {
            $('#movie-selected-preview-container').show();
        } else {
            $('#movie-selected-preview-container').hide();
        }
    };

    var onFormSubmit = function(e) {
        var formData = new FormData($(this)[0]);

        $.ajax({
            url: $(this).attr('action'),
            type: 'post',
            data: formData,
            async: true,
            xhr: function() {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    myXhr.upload.addEventListener('progress', onFormUploadProgress, false);
                }
                return myXhr;
            },
            success: function(data) {
                if (data.result === 'ERRORS') {
                    showErrors(data.errors);
                } else {
                    location.href = data.url;
                }
            },
            cache: false,
            contentType: false,
            processData: false
        });

        $(this).find('.movie-upload-progress')
            .removeClass('hidden')
            .find('.progress-value')
            .text('0')
            .css('width', '0%');

        return false;
    };

    var showErrors = function(errors) {
        $('.form-movie-upload-error').html('');

        for (var key in errors) {
            var message = errors[key];
            $('#form-movie-upload-errors-' + key).html('<ul><li>' + message + '</li></ul>');
        }
    }

    var onFormUploadProgress = function(e) {
        if (e.lengthComputable) {
            var percentage = Math.floor(e.loaded / e.total * 100);

            $('.form-movie-upload').find('.progress-bar')
                .css('width', percentage + '%')
                .find('.progress-value')
                .text(percentage);
        }
    };

    var getFileNameFromPath = function(filePath) {
        var startIndex = (filePath.indexOf('\\') >= 0 ? filePath.lastIndexOf('\\') : filePath.lastIndexOf('/'));
        var fileName = filePath.substring(startIndex);
        if (fileName.indexOf('\\') === 0 || fileName.indexOf('/') === 0) {
            fileName = fileName.substring(1);
        }
        return fileName;
    };

    $(document).ready(init);
})(jQuery)
