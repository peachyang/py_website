(function ($) {
    "use strict";
    $(function () {
        var lang = $('html').attr('lang');
        if (lang) {
            var url = GLOBAL.BASE_URL + 'pub/' + ($('body').is('.admin') ? 'backend' : 'frontend');
            if (/^(?:de|es_CL|fi|nl|pt)/.test(lang)) {
                $.get(url + '/js/validate/localization/methods_' + lang.replace(/^(de|es_CL|fi|nl|pt)\_.+$/, '$1') + '.min.js');
            }
            if (/^(?:bn_BD|es_AR|es_PE|hy_AM|pt_BR|pt_BT|sr_lat|zh_TW)$/.test(lang)) {
                $.get(url + '/js/validate/localization/messages_' + lang + '.min.js').success(function (response) {
                    eval(response);
                });
            } else if (/^(?:ar|bg|ca|cs|da|de|el|es|et|eu|fa|fi|fr|ge|gl|he|hr|hu|id|is|it|ja|ka|kk|ko|lt|lv|mk|my|nl|no|pl|ro|ru|si|sk|sl|sr|sv|th|th|tr|uk|vi|zh)/.test(lang)) {
                $.get(url + '/js/validate/localization/messages_' + lang.replace(/^([a-z]{2})\_.+$/, '$1') + '.min.js').success(function (response) {
                    eval(response);
                });
            }
        }
        $('form').each(function () {
            $(this).validate({
                errorClass: 'invalid'
            });
        });
    });
})(jQuery);
