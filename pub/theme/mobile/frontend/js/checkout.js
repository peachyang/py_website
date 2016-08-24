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
        if (!GLOBAL.AJAX) {
            GLOBAL.AJAX = {};
        }
        var csrf = $('[name=csrf]').first().val();
        var load = function () {
            $('.section.review .btn.btn-checkout').hide();
            $('.section.review .total').html('<img src="data:image/gif;base64, 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" alt="" />');
        };
        var loadShipping = function () {
            load();
            var store = [];
            $('.section.review [name^=shipping_method]').each(function () {
                store.push($(this).attr('name').replace(/\D/g, ''));
            });
            while (store.length) {
                var s = store.pop();
                var url = GLOBAL.BASE_URL + 'checkout/order/shipping/?store=' + s;
                if (GLOBAL.AJAX[url]) {
                    GLOBAL.AJAX[url].abort();
                }
                GLOBAL.AJAX[url] = $.ajax(url, {
                    type: 'get',
                    success: function (xhr) {
                        GLOBAL.AJAX[url] = null;
                        $('.section.review [name="shipping_method[' + s + ']"]').parent().html(xhr);
                        if (!store.length) {
                            loadPayment();
                            loadReview();
                        }
                    }
                });
            }
        };
        var loadPayment = function () {
            var url = GLOBAL.BASE_URL + 'checkout/order/payment/';
            if (GLOBAL.AJAX[url]) {
                GLOBAL.AJAX[url].abort();
            }
            load();
            GLOBAL.AJAX[url] = $.ajax(url, {
                type: 'get',
                success: function (xhr) {
                    GLOBAL.AJAX[url] = null;
                    $('.section.payment').html($(xhr).find('.section.payment').html());
                }
            });
        };
        var loadReview = function () {
            var url = GLOBAL.BASE_URL + 'checkout/order/review/';
            if (GLOBAL.AJAX[url]) {
                GLOBAL.AJAX[url].abort();
            }
            load();
            GLOBAL.AJAX[url] = $.ajax(url, {
                type: 'get',
                success: function (xhr) {
                    GLOBAL.AJAX[url] = null;
                    $('.section.review .total').html(xhr);
                    $('.section.review .btn.btn-checkout').show();
                }
            });
        };
        $('.section.address').on('click', '.edit', function () {
            var f = $('.section.address .form-edit-address form');
            var json = $(this).parent('[data-id]').data('json');
            var t = $(f).find('select,input,textarea');
            $(t).each(function () {
                var v = json[$(this).attr('name')];
                if (v) {
                    if ($(this).is('[type=radio],[type=checkbox]')) {
                        if ($(this).val() === v) {
                            this.checked = true;
                        }
                    } else if ($(this).is('select')) {
                        $(this).attr('data-default', v).val(v).trigger('change');
                    } else {
                        $(this).val(v);
                    }
                }
            });
            if (!$('.section.address .form-edit-address').is(':visible')) {
                $('.section.address .form-edit-address').slideDown();
            }
        }).on('click', '[name=shipping_address_id]', function () {
            $('.section.address .form-edit-address').slideUp();
            var url = GLOBAL.BASE_URL + 'checkout/order/selectaddress/';
            if (GLOBAL.AJAX[url]) {
                GLOBAL.AJAX[url].abort();
            }
            GLOBAL.AJAX[url] = $.ajax(url, {
                type: 'post',
                data: $(this).serialize() + '&csrf=' + csrf,
                success: function () {
                    GLOBAL.AJAX[url] = null;
                }
            });
            loadShipping();
        });
        $('.section.address .btn-add').on('click', function () {
            $('.section.address .form-edit-address').slideToggle();
            $('.section.address .form-edit-address form').trigger('reset');
        });
        $('.section.address .btn-cancel').on('click', function () {
            $('.section.address .form-edit-address').slideUp();
            $('.section.address .form-edit-address form').trigger('reset');
        });
        $('.section.address .form-edit-address form').on('submit', function () {
            if ($(this).valid()) {
                $('.section.address .form-edit-address').slideUp();
            }
        }).on('afterajax.seahinet', function (e, json) {
            var t = $('.section.address [data-id=' + json.data.id + ']');
            if (t.length) {
                $(t).attr('data-json', JSON.stringify(json.data.json))
                        .find('label').text(json.data.content);
            } else {
                var tmpl = $('#tmpl-address-list').html();
                $('.section.address .list').append(tmpl.replace(/\{id\}/g, json.data.id).replace(/\{content\}/g, json.data.content).replace(/\{json\}/g, JSON.stringify(json.data.json)));
            }
        });
        $('.section.review').on('change', '[name^=shipping_method]', function () {
            var url = GLOBAL.BASE_URL + 'checkout/order/selectshipping/';
            if (GLOBAL.AJAX[url]) {
                GLOBAL.AJAX[url].abort();
            }
            GLOBAL.AJAX[url] = $.ajax(url, {
                type: 'post',
                data: $('.section.review [name^=shipping_method]').serialize() + '&csrf=' + csrf,
                success: function () {
                    GLOBAL.AJAX[url] = null;
                    loadPayment();
                    loadReview();
                }
            });
        });
        $('.section.payment').on('click', '[name=payment_method]', function () {
            var url = GLOBAL.BASE_URL + 'checkout/order/selectpayment/';
            if (GLOBAL.AJAX[url]) {
                GLOBAL.AJAX[url].abort();
            }
            GLOBAL.AJAX[url] = $.ajax(url, {
                type: 'post',
                data: $(this).serialize() + '&csrf=' + csrf,
                success: function () {
                    GLOBAL.AJAX[url] = null;
                    loadReview();
                }
            });
        });
        $('.section.review .btn-checkout').on('click', function () {
            var url = GLOBAL.BASE_URL + 'checkout/order/place/';
            var o = this;
            if (GLOBAL.AJAX[url]) {
                GLOBAL.AJAX[url].abort();
            }
            GLOBAL.AJAX[url] = $.ajax(url, {
                type: 'post',
                data: $('.checkout-steps select,.checkout-steps textarea,.checkout-steps input:not([type=radio],[type=checkbox]),.checkout-steps [type=radio]:checked,.checkout-steps [type=checkbox]:checked').serialize(),
                success: function (xhr) {
                    GLOBAL.AJAX[url] = null;
                    responseHandler.call(o, xhr.responseText ? xhr.responseText : xhr);
                }
            });
        });
    });
}));