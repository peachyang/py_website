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
        $('#nav-toggle').click(function () {
            $('.nav-container .open').removeClass('open');
            if ($(this).is('.active')) {
                $(this).removeClass('active');
                $('.nav-container').removeClass('active');
                $('.nav-container .dropdown-toggle').attr('data-toggle', 'dropdown');
                var flag = 0;
            } else {
                $(this).addClass('active');
                $('.nav-container').addClass('active');
                $('.nav-container .dropdown-toggle').removeAttr('data-toggle');
                var flag = 1;
            }
            if (localStorage) {
                localStorage.admin_nav = flag;
            }
            if ($('#canvas').length) {
                setTimeout(function () {
                    $('#canvas').highcharts().reflow();
                }, 600);
            }
        });
        if (localStorage && localStorage.admin_nav == 1) {
            $('.nav-container,.main-container').addClass('no-transition');
            $('#nav-toggle').addClass('active');
            $('.nav-container').addClass('active');
            $('.nav-container .dropdown-toggle').removeAttr('data-toggle');
            if ($('#canvas').length) {
                $('#canvas').highcharts().reflow();
            }
            if (localStorage.admin_nav_open) {
                $('.nav-container>.nav>.dropdown').eq(localStorage.admin_nav_open).addClass('open');
            }
            setTimeout(function () {
                $('.nav-container,.main-container').removeClass('no-transition');
            }, 600);
        }
        $('.nav-container').on('click', '.dropdown-toggle:not([data-toggle=dropdown])', function () {
            var parent = $(this).parent('.dropdown');
            $(parent).siblings('.open').removeClass('open');
            $(parent).toggleClass('open');
            if (localStorage) {
                localStorage.admin_nav_open = $(parent).prevAll('.dropdown').length;
            }
        });
        $('img.captcha').click(function () {
            $(this).attr('src', $(this).attr('src') + '?' + (new Date().getTime()));
        });
        window.addMessages = function (messages) {
            var html = '';
            for (var i in messages) {
                html += '<div class="alert alert-' + messages[i].level + '">' + messages[i].message + '</div>';
            }
            $('.header .top-menu .messages .message-box').append(html);
            $('.header .top-menu .messages .badge').text($('.message-box>.alert').length);
            $('.header .top-menu .messages').addClass('has-message');
        };
        var responseHandler = function (json) {
            var o = this;
            if (typeof json === 'string') {
                json = eval('(' + json + ')');
            }
            if (json.redirect) {
                location.href = json.redirect;
            } else if (json.reload) {
                location.reload();
            } else if (json.message.length) {
                addMessages(json.message);
            }
            if (json.removeLine) {
                if ($(o).is('menu a')) {
                    var t = $('.grid [href="' + $(o).attr('href') + '"][data-params="' + $(o).data('params') + '"]').parentsUntil('tbody,ul,ol,dl').last();
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
            if ($(o).is('[data-params]')) {
                var data = $(o).data('params');
            } else if ($(o).is('[data-serialize]')) {
                var data = $($(o).data('serialize')).find('input:not([type=radio]):not([type=checkbox]),[type=radio]:checked,[type=checkbox]:checked,select,textarea,button[name]').serialize();
            } else {
                var data = '';
            }
            $.ajax($(o).attr('href'), {
                type: $(o).data('method'),
                data: data,
                success: function (xhr) {
                    responseHandler.call(o, xhr.responseText ? xhr.responseText : xhr);
                }
            });
            return false;
        }).on('submit.seahinet.ajax', 'form[data-ajax]', function () {
            var o = this;
            $.ajax($(o).attr('action'), {
                type: $(o).attr('method'),
                data: $(this).serialize(),
                success: function (xhr) {
                    responseHandler.call(o, xhr.responseText ? xhr.responseText : xhr);
                    if ($(o).parents('.modal').length) {
                        $(o).parents('.modal').modal('hide');
                    }
                }
            });
            return false;
        });
        $('#modal-send-email').on({
            'show.bs.modal': function (e) {
                $(this).find('#sendmail-template_id').val($(e.relatedTarget).data('id'));
            }
        });
        if ($('.message-box>.alert').length) {
            addMessages();
        }
        $('.scope').each(function () {
            var selected = $(this).find('.dropdown-menu>.selected');
            if (selected.length) {
                $(this).find('.dropdown-toggle>span').text($(selected).text());
            } else {
                $(this).find('.dropdown-toggle>span').text($(this).find('.dropdown-menu>:first-child').text());
                $('[type=hidden][name=scope]').val('m' + $(this).find('.dropdown-menu>:first-child').data('id'));
            }
        });
        $('a[href="' + location.href + '"]').addClass('active');
        $('[data-base]').each(function () {
            var o = this;
            var p = $(o).parents('.input-box').first();
            $(p).hide();
            $(o).find('input,select,textarea,button').each(function () {
                this.disabled = true;
            });
            o.disabled = true;
            var base = $(o).data('base');
            try {
                var target = eval('(' + base + ')');
            } catch (e) {
                var target = base.indexOf(':') === -1 ? eval('({"' + base + '":1})') : eval('({' + base + '})');
            }
            var toggle = function (s, t) {
                if (typeof s !== 'object') {
                    s = [s];
                }
                if (typeof t !== 'object') {
                    t = [t];
                }
                var f = false;
                for (var i in s) {
                    if ($.inArray(s[i], t) !== -1) {
                        f = true;
                        break;
                    }
                }
                if (f) {
                    $(p).show();
                    o.disabled = false;
                    $(o).find('input,select,textarea,button').each(function () {
                        this.disabled = false;
                    });
                } else {
                    $(p).hide();
                    o.disabled = true;
                    $(o).find('input,select,textarea,button').each(function () {
                        this.disabled = true;
                    });
                }
            };
            for (var i in target) {
                toggle($(i).val(), target[i]);
                $(i).change(function () {
                    toggle($(this).val(), target[i]);
                });
            }
        });
    });
}));
