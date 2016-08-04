(function (factory) {
    if (typeof define === "function" && define.amd) {
        define(["jquery"], factory);
    } else if (typeof module === "object" && module.exports) {
        module.exports = factory(require("jquery"));
    } else {
        factory(jQuery);
    }
}(function ($) {
    $(function () {
        "use strict";
        $('.product-essential .product-info .options .input-box.radio,.product-essential .product-info .options .input-box.checkbox').each(function () {
            var f = false;
            $(this).find('.cell label').each(function () {
                var t = $('#product-media .carousel-indicators [data-group="' + $(this).text() + '"]');
                if (t.length) {
                    f = true;
                    var oimg = document.createElement('img');
                    oimg.src = $(t).first().attr('src');
                    $(this).attr('data-group', $(t).first().data('group')).html(oimg);
                }
                if ($(this).is(':checked+label')) {
                    var t = $(this).next('label').data('group');
                    $('#product-media .carousel-indicators li').hide();
                    var s = $('#product-media .carousel-indicators [data-group="' + t + '"]').parent('li');
                    $(s).show().first().trigger('click');
                }
            });
            if (f) {
                $(this).find('[type=radio],[type=checkbox]').on('click', function () {
                    var t = $(this).next('label').data('group');
                    $('#product-media .carousel-indicators li').hide();
                    var s = $('#product-media .carousel-indicators [data-group="' + t + '"]').parent('li');
                    $(s).show().first().trigger('click');
                });
                return false;
            }
        });
        $('.product-essential .product-info .btn').on('click', function () {
            if ($(this).is('.btn-checkout')) {
                $('.product-essential').parent('form').attr('data-ajax', true);
            } else {
                $('.product-essential').parent('form').removeAttr('data-ajax');
            }
        });
    });
}));