(function (factory) {
    if (typeof define === "function" && define.amd) {
        define(["jquery", "tabs"], factory);
    } else if (typeof module === "object" && module.exports) {
        module.exports = factory(require(["jquery", "tabs"]));
    } else {
        factory(jQuery);
    }
}(function ($) {
    $(function () {
        "use strict";
        $('.product-edit .btn-next').click(function () {
            var n = $('.product-edit .nav-tabs .active').next();
            if (n.length) {
                $(n).children('[data-toggle]').tab('show');
            } else {
                $(this).addClass('disabled');
            }
        });
        $('.product-edit .nav-tabs [data-toggle]').on('show.bs.tab', function () {
            if ($(this).parent().next().not('.view-more').length) {
                $('.product-edit .btn-next').removeClass('disabled');
            } else {
                $('.product-edit .btn-next').addClass('disabled');
            }
        });
        if (!$('.product-edit [name=id]').val()) {
            $('.product-edit .nav-tabs .view-more .btn').show().on('click', function () {
                $('.product-edit .nav-tabs').toggleClass('show-all');
            });
        } else {
            $('.product-edit .nav-tabs').addClass('show-all');
        }
    });
}));
