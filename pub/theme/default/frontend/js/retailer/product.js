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
            if ($(this).parent().next().length) {
                $('.product-edit .btn-next').removeClass('disabled');
            } else {
                $('.product-edit .btn-next').addClass('disabled');
            }
        });
    });
}));
