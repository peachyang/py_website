(function (factory) {
    if (typeof define === "function" && define.amd) {
        define(['jquery','app'], factory);
    } else if (typeof module === "object" && module.exports) {
        module.exports = factory(require('app'));
    } else {
        factory(jQuery);
    }
}(function ($) {
    $(function () {
        "use strict";
        var collateTotals = function () {
            var t = 0;
            var q = 0;
            $('#cart [type=checkbox][name]:checked').each(function () {
                var p = $(this).parent();
                var tq = parseFloat($(p).siblings('.qty').find('.form-control').val());
                q += tq;
                t += $(p).siblings('.price').data('price') * tq;
            });
            $('#cart .selected').text(q);
            $('#cart .total').text(formatPrice(t));
            if (q) {
                $(".btn-checkout").removeAttr('disabled');
            } else {
                $(".btn-checkout").attr('disabled', 'disabled');
            }
        };
        var cartSelectItem = function () {
            var p = $('#cart');
            if (this && $(this).is('.selectall,.selectall [type=checkbox]')) {
                var f = this.checked;
                $(p).find('[type=checkbox]').each(function () {
                    this.checked = f;
                });
            } else {
                $(p).find('.selectall,.selectall [type=checkbox]').each(function () {
                    this.checked = $(p).find('[type=checkbox]').not(':checked,.selectall,.selectall [type=checkbox]').length ? false : true;
                });
            }
            collateTotals();
        };
        cartSelectItem();
        $('.table').on('click', '[type=checkbox]', function () {
            cartSelectItem.call(this);
        });
        $('.checkout-cart .qty .form-control').on('change.seahinet', function () {
            var p = $(this).parents('.qty');
            var price = $(p).siblings('.price');
            $(p).siblings('.subtotal').text(formatPrice($(price).data('price') * $(this).val()));
            collateTotals();
        });
    });
}));
