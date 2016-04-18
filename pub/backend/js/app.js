(function ($) {
    "use strict";
    $(function () {
        $('#nav-toggle').click(function () {
            $('.nav-container .open').removeClass('open');
            if ($(this).is('.active')) {
                $(this).removeClass('active');
                $('.nav-container').removeClass('active');
                $('.nav-container .dropdown-toggle').attr('data-toggle', 'dropdown');
            } else {
                $(this).addClass('active');
                $('.nav-container').addClass('active');
                $('.nav-container .dropdown-toggle').removeAttr('data-toggle');
            }
        });
        $('.nav-container').delegate('.dropdown-toggle:not([data-toggle=dropdown])', 'click', function () {
            var parent = $(this).parent('.dropdown');
            $(parent).siblings('.open').removeClass('open');
            $(parent).toggleClass('open');
        });
        $('img.captcha').click(function () {
            $(this).attr('src', $(this).attr('src') + '?' + (new Date().getTime()));
        });
    });
})(jQuery);
