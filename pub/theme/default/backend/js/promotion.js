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
        $('.condition').on('click', '.add', function () {
            $(this).parent('li').before(function () {
                return $($(this).parent('ul').data('tmpl')).html();
            });
        }).on('change', 'select', function () {
            $(this).find(':selected').text();
        });
        var addCoupon = function (code) {
            $('.coupon .target').prepend($('#tmpl-coupon').html().replace('{$code}', code ? code : ''));
        };
        $('.coupon .btn-add').click(function () {
            addCoupon();
        });
        $('.coupon .btn-generate').click(function () {
            var c = parseInt(prompt($(this).attr('title'), 5));
            if (c > 0) {
                var current = $('.coupon .target .code').serialize();
                $.post(GLOBAL.BASE_URL + GLOBAL.ADMIN_PATH + '/promotion/generateCoupon/', current + '&count=' + c, function (response) {
                    for (var code in response) {
                        addCoupon(response[code]);
                    }
                });
            }
        });
        $('.coupon .target').on('click', '.delete:not([data-method])', function () {
            $(this).parents('tr').first().remove();
        });
    });
}));

