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
        var getPrice = function (qty, q) {
            var obj = $(qty).siblings('.price');
            q = parseFloat(q ? q : $(qty).find('.form-control').val());
            var tier = $(obj).data('tier');
            if (tier) {
                if (typeof tier === 'string') {
                    tier = eval('(' + tier + ')');
                }
                var r = null;
                for (var p in tier) {
                    if (q >= parseFloat(p)) {
                        r = parseFloat(tier[p]);
                    }
                }
                if (r !== null) {
                    return r;
                }
            }
            return parseFloat($(obj).data('price'));
        };
        var collateTotals = function () {
            var t = 0;
            var q = 0;
            $('#cart [type=checkbox][name]:checked').each(function () {
                var p = $(this).parent();
                var tq = parseFloat($(p).siblings('.qty').find('.form-control').val());
                t += getPrice($(p).siblings('.qty'), tq) * tq;
                q += tq;
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
            var next = $(this).next();
            if ($(next).is('.product-list')) {
                recursiveSelect.call(next, flag);
            }
        };
        var recursiveCheck = function () {
            var flag = $(this).find('[type=checkbox]').prop('checked');
            if (flag) {
                var next = $(this).next();
                if ($(next).is('.product-list')) {
                    flag = flag && recursiveCheck.call(next, flag);
                }
            }
            return flag;
        };
        var updateCartFlag = null;
        var updateCart = function () {
            var o = this;
            if (updateCartFlag) {
                updateCartFlag.readyState < 4 ? updateCartFlag = null : updateCartFlag.abort();
            }
            updateCartFlag = $.ajax(GLOBAL.BASE_URL + 'checkout/cart/update/', {
                type: 'post',
                data: $('#cart').parent('form').serialize(),
                success: function (xhr) {
                    updateCartFlag = null;
                    responseHandler.call(o, xhr.responseText ? xhr.responseText : xhr);
                }
            });
        };
        $('#cart').on('check.seahinet', function () {
            $(this).find('.store [type=checkbox]').each(function () {
                this.checked = recursiveCheck.call($(this).parents('.store').first().next('.product-list'));
            });
            $(this).find('[type=checkbox].selectall,.selectall [type=checkbox]').not('.store [type=checkbox]').each(function () {
                this.checked = $('#cart .store [type=checkbox]:not(:checked)').length ? false : true;
            });
        }).on('change.seahinet', '[name^=qty]', updateCart).on('click', '[type=checkbox]', updateCart);
        var cartSelectItem = function () {
            if (this) {
                if ($(this).is('.selectall,.selectall [type=checkbox]')) {
                    if ($(this).is('.store [type=checkbox]')) {
                        recursiveSelect.call($(this).parents('.store').first().next('.product-list'), this.checked);
                    } else {
                        $('#cart [type=checkbox]').prop('checked', this.checked);
                    }
                }
            }
            $('#cart').trigger('check.seahinet');
            collateTotals();
        };
        cartSelectItem();
        $('.table').on('click', '[type=checkbox]', function () {
            cartSelectItem.call(this);
        });
        $('.checkout-cart .qty .form-control').on('change.seahinet', function () {
            var p = $(this).parents('.qty');
            var price = $(p).siblings('.price');
            var pr = getPrice(p);
            $(price).text(formatPrice(pr, $(this).val()));
            $(p).siblings('.subtotal').text(formatPrice(pr * $(this).val()));
            collateTotals();
        });
    });
}));
