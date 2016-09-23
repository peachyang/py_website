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
            if (json.cookie && $.cookie) {
                $.cookie(json.cookie.key, json.cookie.value, json.cookie);
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
        window.formatPrice = function (price) {
            return GLOBAL.FORMAT.replace(/\%(?:\d\$)?(?:\.\d+)?[fd]/, parseFloat(price).toFixed(GLOBAL.FORMAT.indexOf('.') === -1 ? 0 : GLOBAL.FORMAT.replace(/^.+\.(\d+)[fd]$/, '$1')))
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
        $('.modal').on({
            'show.bs.modal': function (e) {
                if ($(e.relatedTarget).is('[data-info]')) {
                    var info = $(e.relatedTarget).data('info');
                    if (typeof info === 'string') {
                        info = eval('(' + info + ')');
                    }
                    $(this).find('form').trigger('reset');
                    for (var i in info) {
                        var t = $(this).find('[name="' + i + '"]');
                        if (t.length) {
                            $(t).each(function () {
                                if ($(this).is('[type=radio],[type=checkbox]')) {
                                    if ($(this).val() == info[i]) {
                                        this.checked = true;
                                    }
                                } else {
                                    if ($(this).is('select')) {
                                        $(this).attr('data-default-value', info[i]);
                                    }
                                    $(this).val(info[i]).trigger('change.seahinet');
                                }
                            });
                        }
                    }
                }
            }
        });
        $('.carousel .carousel-inner').on('touchstart', function (e) {
            GLOBAL.PAGEX = e.originalEvent.touches[0].pageX;
            GLOBAL.PAGEY = e.originalEvent.touches[0].pageY;
            if (GLOBAL.CAROUSELTIMEOUT) {
                window.clearTimeout(GLOBAL.CAROUSELTIMEOUT);
                GLOBAL.CAROUSELTIMEOUT = null;
            }
            $('.carousel .item').css('transition', 'none');
        }).on('touchmove', function (e) {
            var x = e.originalEvent.touches[0].pageX;
            var y = e.originalEvent.touches[0].pageY;
            if (Math.abs(y - GLOBAL.PAGEY) < 30) {
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
            }
        }).on('touchend', function (e) {
            var t = $(this).children('.item.prev,.item.next');
            if (t.length) {
                var c = $(this).children('.item.active');
                if (Math.abs($(c).css('left').replace('px', '')) > $(c).width() / 20) {
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
                GLOBAL.CAROUSELTIMEOUT = window.setTimeout(function () {
                    $('.carousel .item').removeAttr('style');
                    $('.carousel .carousel-indicators li').removeClass('active');
                    $('.carousel .carousel-indicators [data-slide-to=' + $('.carousel .item.active').prevAll('item').length + ']').addClass('active');
                }, 600);
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
        $('.filters .more a').click(function () {
            $(this).parents('dd').toggleClass('all');
        });
        $('[type=file].preview').each(function () {
            $(this).change(function () {
                $(this).siblings('.preview').remove();
                if (typeof FileReader !== 'undefined') {
                    var oimg = document.createElement('img');
                    $(oimg).addClass('preview');
                    $(this).after(oimg);
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        oimg.src = e.target.result;
                    }
                    reader.readAsDataURL(this.files[0]);
                } else {
                    this.select();
                    var src = document.selection.createRange().text;
                    $(this).after('<div class="preview" style=\'filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale,src="' + src + '"\'></div>')
                }
            });
        });
    });
    $(function () {
        $('aside.slide-wrapper').on(function (e) {
            $(this).addClass('current').siblings('li').removeClass('current');
        });

        $('a.slide-menu').on('click', function (e) {
            var wh = $('div.wrapperhove' + 'rtree').height();
            $('div.slide-mask').css('height', wh).show();
            $('aside.slide-wrapper').css('height', wh).addClass('moved');
        });

        $('div.slide-mask').on('click', function () {
            $('div.slide-mask').hide();
            $('aside.slide-wrapper').removeClass('moved');
        });
    });
}));


