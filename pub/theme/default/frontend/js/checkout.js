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
            $('.section.address .form-edit-address').slideUp();
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