(function (factory) {
    if (typeof define === "function" && define.amd) {
        define(["jquery", "jquery.validate"], factory);
    } else if (typeof module === "object" && module.exports) {
        module.exports = factory(require(["jquery", "jquery.validate"]));
    } else {
        factory(jQuery);
    }
}(function ($) {
    $(function () {
        "use strict";
        var lang = $('html').attr('lang');
        if (lang) {
            var url = GLOBAL.PUB_URL + ($('body').is('.admin') ? 'backend' : 'frontend');
            if (typeof module === "object" && module.exports) {
                var i18n = function (src) {
                    require('./localization/' + src);
                };
            } else {
                var i18n = function (src) {
                    var os = document.createElement('script');
                    os.async = 'async';
                    os.src = url + '/js/validate/localization/' + src + '.js';
                    document.head.appendChild(os);
                };
            }
            if (/^(?:de|es\-CL|fi|nl|pt)/.test(lang)) {
                i18n('methods_' + lang.replace(/^(de|es_CL|fi|nl|pt)\_.+$/, '$1') + '.min');
            }
            if (/^(?:bn\-BD|es\-AR|es\-PE|hy\-AM|pt\-BR|pt\-BT|sr\-lat|zh\-TW)$/.test(lang)) {
                i18n('messages_' + lang + '.min');
            } else if (/^(?:ar|bg|ca|cs|da|de|el|es|et|eu|fa|fi|fr|ge|gl|he|hr|hu|id|is|it|ja|ka|kk|ko|lt|lv|mk|my|nl|no|pl|ro|ru|si|sk|sl|sr|sv|th|th|tr|uk|vi|zh)/.test(lang)) {
                i18n('messages_' + lang.replace(/^([a-z]{2})\-.+$/, '$1') + '.min');
            }
        }
        $('form').each(function () {
            $(this).validate({
                errorClass: 'invalid',
                ignore: '[type=hidden]',
                errorPlacement: function (error, element) {
                    $(error).appendTo($(element).parent());
                }
            });
        });
    });
}));
