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
        $('.product-essential .product-info .options .form-control').change(function () {
            var sum = 0;
            $('.product-essential .product-info .options [data-price]').each(function () {
                if ($(this).is('[type=radio],[type=checkbox]')) {
                    if (this.checked) {
                        sum += parseFloat($(this).data('price'));
                    }
                } else if ($(this).is('option:selected')) {
                    if (this.selected) {
                        sum += parseFloat($(this).data('price'));
                    }
                } else {
                    sum += parseFloat($(this).data('price'));
                }
            });
            $('.product-essential .product-info .price-box [data-price]').text(function () {
                return formatPrice(parseFloat($(this).data('price')) + sum);
            });
        });
        $('.product-essential .product-info .btn').on('click', function () {
            var f = $('.product-essential').parent('form');
            if ($(this).is('.btn-checkout')) {
                $(f).attr('data-ajax', true);
                if ($(f).valid()) {
                    var options = '';
                    $('.product-info .options').find('input:not([type=radio]):not([type=checkbox]),select,textarea,[type=radio]:checked,[type=checkbox]:checkbox').each(function () {
                        options += $(this).parents('.input-box').children('.control-label').text() + ': ';
                        if ($(this).is('select')) {
                            options += $(this).find('option:selected').text();
                        } else if ($(this).is('[type=radio],[type=checkbox]')) {
                            options += $(this).next('label').text();
                        } else {
                            options += $(this).val();
                        }
                        options += '<br />';
                    });
                    $('.minicart .dropdown-menu .items').append(
                            $('#tmpl-minicart').html().replace(/\{\$id\}/g, $('[name=product_id]').val())
                            .replace(/\{\$url\}/g, location.href)
                            .replace(/\{\$thumbnail\}/g, $('#product-media .item.active img').attr('src'))
                            .replace(/\{\$name\}/g, $('.product-info .product-name').text())
                            .replace(/\{\$price\}/g, $('.product-info .price-box .price').not('.old-price .price').text())
                            .replace(/\{\$qty\}/g, $('.product-info [name=qty]').val())
                            .replace(/\{\$options\}/g, options));
                    $('.minicart .dropdown-toggle .badge').text(function () {
                        return parseFloat($(this).text()) + parseFloat($('.product-info [name=qty]').val());
                    });
                }
            } else {
                $(f).removeAttr('data-ajax');
            }
        });
        $(".magnifying").imagezoom();
    });
    
}));