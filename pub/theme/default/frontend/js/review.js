(function (factory) {
    if (typeof define === "function" && define.amd) {
        define(["jquery"], factory);
    } else if (typeof module === "object" && module.exports) {
        module.exports = factory(require(["jquery"]));
    } else {
        factory(jQuery);
    }
}(function ($) {
    $(function () {
        "use strict";
        $('.review-form').on('change', '[type=file]', function () {
            var odiv = $('<div class="preview"></div>');
            if (typeof FileReader !== 'undefined') {
                console.log(this.files[0].size);
                if (this.files[0].size > 2097152) {
                    alert(translate('Each image should not be over 2MB.'));
                } else {
                    var oimg = document.createElement('img');
                    $(odiv).append(oimg);
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        oimg.src = e.target.result;
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            } else {
                this.select();
                var src = document.selection.createRange().text;
                $(odiv).css('filter', 'progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale,src="' + src + '"');
            }
            $(this).before(odiv);
            if ($(this).siblings('[name="' + $(this).attr('name') + '"]').length < 4) {
                $(this).after('<input type="file" hidden="hidden" name="' + $(this).attr('name') + '" id="' + $(this).attr('id') + '" />').removeAttr('id');
            }
        }).on('click', '.preview', function () {
            var o = $(this).next('[type=file]');
            if ($(this).siblings('.preview').length) {
                if ($(o).is('[id]')) {
                    $(this).siblings('[type=file]').last().attr('id', $(o).attr('id'));
                }
                $(o).remove();
                $(this).remove();
            } else {
                $(o).val('');
                $(this).remove();
            }
        });
    });
}));
