(function (factory) {
    if (typeof define === "function" && define.amd) {
        define(['jquery', 'app'], factory);
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
        var recursiveSelect = function (flag) {
            $(this).find('[type=checkbox]').prop('checked', flag)
            var next = $(this).next('.product-list');
            if (next) {
                recursiveSelect.call(next, flag);
            }
        };
        $('#cart').on('check.seahinet', function () {
            $(this).find('.store [type=checkbox]').each(function () {
                recursiveSelect.call($(this).parents('.store').first().next('.product-list'), this.checked);
            });
            $(this).find('[type=checkbox].selectall,.selectall [type=checkbox]').not('.store [type=checkbox]').each(function () {
                this.checked = $('#cart .store [type=checkbox]:not(:checked)').length ? false : true;
            });
        });
        var cartSelectItem = function () {
            if (this) {
                if ($(this).is('.selectall,.selectall [type=checkbox]')) {
                    if (!$(this).is('.store [type=checkbox]')) {
                        $('#cart .store [type=checkbox]').prop('checked', this.checked);
                    }
                } else {
                    var p = $(this).parents('.product-list').first();
                    if (this.checked && !$(p).find('[type=checkbox]:not(:checked)').length) {
                        $(p).prevAll('.store').first().find('[type=checkbox]').prop('checked', true);
                    } else if (!this.checked) {
                        $('#cart .selectall,#cart .selectall [type=checkbox]').prop('checked', false);
                    }
                }
                $('#cart').trigger('check.seahinet');
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
