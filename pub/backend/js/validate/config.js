(function ($) {
    "use strict";
    $(function () {
        var lang = $('html').attr('lang');
        if (lang) {
            if (/^(?:de|es_CL|fi|nl|pt)/.test(lang)) {
                $.get('js/validate/localization/methods_' + lang.replace(/^(de|es_CL|fi|nl|pt)\_.+$/, '$1') + '.min.js');
            }
            $.get('js/validate/localization/messages_' + lang + '.min.js').success(function (response) {
                eval(response);
            }).error(function () {
                if (lang.indexOf('_') !== -1) {
                    $.get('js/validate/localization/messages_' + lang.replace(/^([a-z]+)\_.+$/, '$1') + '.min.js').success(function (response) {
                        eval(response);
                    });
                }
            });
        }
        $('form').each(function () {
            $(this).validate({
                errorClass: 'invalid',
            });
        });
    });
})(jQuery);