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
        $('.nav-container').delegate('.dropdown-toggle:not([data-toggle=dropdown])', 'click', function () {
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
        $('.grid .table,.grid ul,.grid ol,.grid dl').each(function () {
            if ($(this).find('[type=checkbox].selectall').length) {
                $(this).delegate('[type=checkbox]', 'click', function () {
                    var flag = this.checked;
                    var parent = $(this).parents('.grid .table,.grid ul,.grid ol,.grid dl').last();
                    if ($(this).is('.selectall')) {
                        $(parent).find('[type=checkbox]').not(this).each(function () {
                            this.checked = flag;
                        });
                    } else if (flag && !$(parent).find('[type=checkbox]').not('.selectall,:checked').length) {
                        $(parent).find('.selectall').each(function () {
                            this.checked = flag;
                        });
                    } else if (!flag && $(parent).find('[type=checkbox]').not('.selectall,:checked').length) {
                        $(parent).find('.selectall').each(function () {
                            this.checked = flag;
                        });
                    }
                });
            }
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
                var t = $(this).parentsUntil('tbody,ul,ol,dl').last();
                if ($(t).is('tr,li,dt,dd')) {
                    $(t).remove();
                } else {
                    $(json.removeLine).each(function () {
                        $('tr,li,dt,dd').filter('[data-id=' + this + ']').remove();
                    });
                }
            }
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
        $('.grid .table tbody td').click(function () {
            if ($(this).siblings('.checkbox').length) {
                $(this).siblings('.checkbox').children('[type=checkbox]').trigger('click');
            } else if ($(this).parent('tr').data('href')) {
                location.href = $(this).parent('tr').data('href');
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
            o.disabled = true;
            var base = $(o).data('base');
            try {
                var target = eval('(' + base + ')');
            } catch (e) {
                var target = base.indexOf(':') === -1 ? eval('({"' + base + '":1})') : eval('({' + base + '})');
            }
            for (var i in target) {
                var v = $(i).val();
                if (v == target[i] || $.inArray(target[i], v) !== -1) {
                    $(p).show();
                    o.disabled = false;
                } else {
                    $(p).hide();
                    o.disabled = true;
                }
                $(i).change(function () {
                    var v = $(this).val();
                    if (v == target[i] || $.inArray(target[i], v) !== -1) {
                        $(p).show();
                        o.disabled = false;
                    } else {
                        $(p).hide();
                        o.disabled = true;
                    }
                });
            }
        });
    });
}));
