(function ($) {
    "use strict";
    $(function () {
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
        });
        if (localStorage && localStorage.admin_nav == 1) {
            $('#nav-toggle').addClass('active');
            $('.nav-container').addClass('active');
            $('.nav-container .dropdown-toggle').removeAttr('data-toggle');
        }
        $('.nav-container').delegate('.dropdown-toggle:not([data-toggle=dropdown])', 'click', function () {
            var parent = $(this).parent('.dropdown');
            $(parent).siblings('.open').removeClass('open');
            $(parent).toggleClass('open');
        });
        $('img.captcha').click(function () {
            $(this).attr('src', $(this).attr('src') + '?' + (new Date().getTime()));
        });
        $('.grid thead th.checkbox [type=checkbox],.grid tfoot td.checkbox [type=checkbox]').click(function () {
            var flag = this.checked;
            $(this).parents('.grid .table').find('[type=checkbox]').not(this).each(function () {
                this.checked = flag;
            });
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
        };
        $('a[data-method]').click(function () {
            if ($(this).is('[data-params]')) {
                var data = $(this).data('params');
            } else if ($(this).is('[data-serialize]')) {
                var data = $($(this).data('serialize')).serialize();
            } else {
                var data = '';
            }
            $.ajax($(this).attr('href'), {
                type: $(this).data('method'),
                data: data,
                success: function (xhr) {
                    responseHandler(xhr.responseText ? xhr.responseText : xhr);
                }
            });
            return false;
        });
        $('form[data-ajax]').submit(function () {
            $.ajax($(this).attr('href'), {
                type: $(this).data('method'),
                data: $.serialize(this),
                success: function (xhr) {
                    responseHandler(xhr.responseText ? xhr.responseText : xhr);
                }
            });
            return false;
        });
        if ($('.message-box>.alert').length) {
            addMessages();
        }
    });
})(jQuery);
