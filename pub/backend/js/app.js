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
            $('.nav-container,.main-container').css('transition', 'none');
            $('#nav-toggle').addClass('active');
            $('.nav-container').addClass('active');
            $('.nav-container .dropdown-toggle').removeAttr('data-toggle');
            if ($('#canvas').length) {
                $('#canvas').highcharts().reflow();
            }
            setTimeout(function () {
                $('.nav-container,.main-container').removeAttr('style');
            }, 600);
        }
        $('.nav-container').delegate('.dropdown-toggle:not([data-toggle=dropdown])', 'click', function () {
            var parent = $(this).parent('.dropdown');
            $(parent).siblings('.open').removeClass('open');
            $(parent).toggleClass('open');
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
        var addMessages = function () {
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
                var html = '';
                for (var i in json.message) {
                    html += '<div class="alert alert-' + json.message[i].level + '">' + json.message[i].message + '</div>';
                }
                $('.header .top-menu .messages .message-box').append(html);
                addMessages();
            }
            if (json.removeLine) {
                $(this).parentsUntil('tbody,ul,ol,dl').last().remove();
            }
        };
        $('a[data-method]').click(function () {
            var o = this;
            if ($(o).is('[data-params]')) {
                var data = $(o).data('params');
            } else if ($(o).is('[data-serialize]')) {
                var data = $($(o).data('serialize')).serialize();
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
        });
        $('form[data-ajax]').submit(function () {
            var o = this;
            $.ajax($(o).attr('href'), {
                type: $(o).data('method'),
                data: $(this).serialize(),
                success: function (xhr) {
                    responseHandler.call(o, xhr.responseText ? xhr.responseText : xhr);
                }
            });
            return false;
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
        $('[data-base]').each(function () {
            var o = this;
            $(o).parents('.input-box').hide();
            var base = $(o).data('base');
            try {
                var target = eval('(' + base + ')');
            } catch (e) {
                var target = base.indexOf(':') === -1 ? eval('({"' + base + '":1})') : eval('({' + base + '})');
            }
            for (var i in target) {
                if ($(i).val() == target[i]) {
                    $(o).parents('.input-box').show();
                } else {
                    $(o).parents('.input-box').hide();
                }
                $(i).change(function () {
                    if ($(this).val() == target[i]) {
                        $(o).parents('.input-box').show();
                    } else {
                        $(o).parents('.input-box').hide();
                    }
                });
            }
        });
    });
}));
