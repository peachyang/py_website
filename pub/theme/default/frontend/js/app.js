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
        $('.pager .btn').click(function () {
            location.href = $(this).data('url') + $(this).siblings('input').val();
        });
        window.addMessages = function (messages) {
            var html = '';
            for (var i in messages) {
                html += '<div class="alert alert-' + messages[i].level + '">' + messages[i].message + '</div>';
            }
            $('.message-box').append(html);
        };
        window.responseHandler = function (json) {
            var o = this;
            if (typeof json === 'string') {
                json = eval('(' + json + ')');
            }
            if (json.redirect) {
                location.href = json.redirect;
                return;
            } else if (json.reload) {
                location.reload();
                return;
            } else if (json.message.length) {
                addMessages(json.message);
            }
            if (json.removeLine) {
                if ($(o).is('menu a')) {
                    var t = $('[data-params="' + $(o).data('params') + '"]').parentsUntil('tbody,ul,ol,dl').last();
                } else {
                    var t = $(o).parentsUntil('tbody,ul,ol,dl').last();
                }
                if ($(t).is('tr,li,dt,dd')) {
                    $(t).remove();
                } else {
                    $(json.removeLine).each(function () {
                        $(o).parents('[data-id=' + this + ']').first().remove();
                    });
                }
            }
            $(o).trigger('afterajax.seahinet', json);
        };
        $(document.body).on('click.seahinet.ajax', 'a[data-method]', function () {
            var o = this;
            if ($(o).data('method') !== 'delete' || confirm(translate($(o).is('[data-serialize]') ? 'Are you sure to delete these records?' : 'Are you sure to delete this record?'))) {
                if ($(o).is('[data-params]')) {
                    var data = $(o).data('params');
                } else if ($(o).is('[data-serialize]')) {
                    var data = $($(o).data('serialize')).find('input:not([type=radio]):not([type=checkbox]),[type=radio]:checked,[type=checkbox]:checked,select,textarea,button[name]').serialize();
                } else {
                    var data = '';
                }
                if (!GLOBAL.AJAX) {
                    GLOBAL.AJAX = {};
                } else if (GLOBAL.AJAX[$(o).attr('href')]) {
                    GLOBAL.AJAX[$(o).attr('href')].abort();
                }
                GLOBAL.AJAX[$(o).attr('href')] = $.ajax($(o).attr('href'), {
                    type: $(o).data('method'),
                    data: data,
                    success: function (xhr) {
                        GLOBAL.AJAX[$(o).attr('href')] = null;
                        responseHandler.call(o, xhr.responseText ? xhr.responseText : xhr);
                    }
                });
            }
            return false;
        }).on('submit.seahinet.ajax', 'form[data-ajax]', function () {
            var o = this;
            if (!GLOBAL.AJAX) {
                GLOBAL.AJAX = {};
            } else if (GLOBAL.AJAX[$(o).attr('action')]) {
                GLOBAL.AJAX[$(o).attr('action')].abort();
            }
            GLOBAL.AJAX[$(o).attr('action')] = $.ajax($(o).attr('action'), {
                type: $(o).attr('method'),
                data: $(this).serialize(),
                success: function (xhr) {
                    GLOBAL.AJAX[$(o).attr('action')] = null;
                    responseHandler.call(o, xhr.responseText ? xhr.responseText : xhr);
                    if ($(o).parents('.modal').length) {
                        $(o).parents('.modal').modal('hide');
                    }
                }
            });
            return false;
        });
    });
    $('.carousel .carousel-inner').on('touchstart', function (e) {
        GLOBAL.PAGEX = e.originalEvent.touches[0].pageX;
        if(GLOBAL.CAROUSELTIMEOUT){
            window.clearTimeout(GLOBAL.CAROUSELTIMEOUT);
            GLOBAL.CAROUSELTIMEOUT = null;
        }
        $('.carousel .item').css('transition', 'none');
    }).on('touchmove', function (e) {
        var x = e.originalEvent.touches[0].pageX;
        var c = $(this).children('.item.active');
        if (x < GLOBAL.PAGEX) {
            var t = $(c).is('.item:last-child') ? $(this).children('.item:first-child') : $(c).next('.item');
            $(t).removeClass('prev').addClass('next');
            $(c).css('left', x - GLOBAL.PAGEX);
            $(t).css('left', x - GLOBAL.PAGEX);
        } else {
            var t = $(c).is('.item:first-child') ? $(this).children('.item:last-child') : $(c).prev('.item');
            $(t).removeClass('next').addClass('prev');
            $(c).css('left', x - GLOBAL.PAGEX);
            $(t).css('left', x - GLOBAL.PAGEX);
        }
    }).on('touchend', function (e) {
        var t = $(this).children('.item.prev,.item.next');
        if (t.length) {
            var c = $(this).children('.item.active');
            if (Math.abs($(c).css('left').replace('px', '')) > $(c).width() / 2) {
                var w = $(c).width();
                if ($(t[0]).is('.prev')) {
                    $(t).animate({left: w}, 300);
                    $(c).animate({left: w}, 300, function () {
                        $(this).removeClass('active');
                        $(t).removeClass('prev').removeClass('next').addClass('active');
                    });
                } else {
                    $(t).animate({left: -w}, 300);
                    $(c).animate({left: -w}, 300, function () {
                        $(this).removeClass('active');
                        $(t).removeClass('prev').removeClass('next').addClass('active');
                    });
                }
            } else {
                $(t).animate({left: 0}, 300);
                $(c).animate({left: 0}, 300, function () {
                    $(t).removeClass('prev').removeClass('next');
                });
            }
            GLOBAL.CAROUSELTIMEOUT = window.setTimeout(function(){
                $('.carousel .item').removeAttr('style');
            },600);
        }
    });
    if ($.bttrlazyloading) {
        $.bttrlazyloading.setOptions({
            retina: true,
            placeholder: 'data:image/gif;base64, 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'
        });
    }
    $('img.bttrlazyloading').each(function () {
        $(this).bttrlazyloading();
    });
    $(".selectall").click(function () {
        var this_value = $(this).val();
        var this_status = this.checked;
        ;
        $(".checkbox-" + this_value).prop("checked", this_status);
        if (this_value == "on") {
            $("input[type='checkbox']").prop("checked", this_status);
        }
    });
    $('.qty .spin').click(function () {
        var t = $('#' + $(this).attr('for'));
        var v = parseFloat($(t).val());
        var s = parseFloat($(t).attr('step'));
        var min = parseFloat($(t).attr('min'));
        var max = parseFloat($(t).attr('max'));
        if ($(this).is('.minus') && (isNaN(min) || v > min)) {
            $(t).val(min ? Math.max(min, v - (s ? s : 1)) : v - (s ? s : 1));
            $(t).trigger('change.seahinet');
        } else if ($(this).is('.plus') && (isNaN(max) || v < max)) {
            $(t).val(max ? Math.min(max, v + (s ? s : 1)) : v + (s ? s : 1));
            $(t).trigger('change.seahinet');
        }
    });
    $('.checkout-cart .qty .form-control').on('change.seahinet', function () {
        qty_change($(this));
    });
    function qty_change(e) {
        var num = e.val();
        var price = e.parent().prev().html().replace(/[^0-9]/ig, "") / 100;
        var subtotal = $.trim(e.parent().prev().html()).substr(0, 1) + num * price + '.00';
        var qty_change = $("#qty-change").val();
        if (num == 0) {
            e.val(1);
        }
        if (qty_change == 0) {
            $("#qty-change").val(1);
        }
        e.parent().next().find("span").html(subtotal);
        total_change();
    }
    function total_change() {
        var total_num = 0;
        $("tbody").find("input.required").each(function () {
            if ($(this).parent().parent().find("td:eq(1)").find("input").is(':checked')) {
                total_num += Number($(this).val());
            }
        });
        $('.select-qyt').html(total_num);
        var total_price = 0;
        $("tbody .product-list").find("td:eq(5)").find("span.checkout-num").each(function () {
            if ($(this).parent().parent().find("td:eq(1)").find("input").is(':checked')) {
                total_price += $(this).html().replace(/[^0-9]/ig, "") / 100;
            }
        });
        $('.total-pirce').html($.trim($("#cart tbody").find("tr:eq(1)").find("td:eq(3)").html()).substr(0, 1) + total_price + '.00');
        var checked_total = 0;
        $("tbody .product-list").find("td:eq(1)").find("input").each(function () {
        	if ($(this).is(':checked')) {
        		checked_total++;
            }
        });
        if(checked_total > 0){
        	$(".btn-checkout").css("background","#fabb39");
        	$(".btn-checkout").attr("href",GLOBAL.BASE_URL+"/checkout/order/");
        }else{
        	$(".btn-checkout").css("background","none");
        	$(".btn-checkout").attr("href","javascript:viod(0)");
        }
    }
    $("input:checkbox").change(function () {
        total_change();
    });
   $("#related-menu a").click(function(){
       $(this).addClass("selected-menu").siblings().removeClass("selected-menu");
       $('#related-content .related-box:eq(' + $(this).index() + ')').show().siblings().hide();
   });
}));
