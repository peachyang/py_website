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
            if (GLOBAL.CAROUSELTIMEOUT) {
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
                GLOBAL.CAROUSELTIMEOUT = window.setTimeout(function () {
                    $('.carousel .item').removeAttr('style');
                }, 600);
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
}));

