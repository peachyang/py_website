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
        var lang = $('html').attr('lang');
        if (lang) {
            var url = GLOBAL.PUB_URL + ($('body').is('.admin') ? 'backend' : 'frontend');
            if (/^(?:de|es\-CL|fi|nl|pt)/.test(lang)) {
                $.get(url + '/js/validate/localization/methods_' + lang.replace(/^(de|es_CL|fi|nl|pt)\_.+$/, '$1') + '.min.js');
            }
            if (/^(?:bn\-BD|es\-AR|es\-PE|hy\-AM|pt\-BR|pt\-BT|sr\-lat|zh\-TW)$/.test(lang)) {
                $.get(url + '/js/validate/localization/messages_' + lang + '.min.js').success(function (response) {
                    eval(response);
                });
            } else if (/^(?:ar|bg|ca|cs|da|de|el|es|et|eu|fa|fi|fr|ge|gl|he|hr|hu|id|is|it|ja|ka|kk|ko|lt|lv|mk|my|nl|no|pl|ro|ru|si|sk|sl|sr|sv|th|th|tr|uk|vi|zh)/.test(lang)) {
                $.get(url + '/js/validate/localization/messages_' + lang.replace(/^([a-z]{2})\-.+$/, '$1') + '.min.js').success(function (response) {
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
}));
