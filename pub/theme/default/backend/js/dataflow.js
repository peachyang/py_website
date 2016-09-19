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
        var doImport = function (url, processer) {
            $.get(url + (processer ? ('?p=' + processer) : '')).success(function (response) {
                if (response.message) {
                    $('.progress.active').append('<div class="alert alert-' + (response.error ? 'danger' : 'success') + '">' + response.message + '</div>');
                }
                if (response.processer) {
                    doImport(url, processer);
                } else {
                    $('.fa-spinner.fa-spin').remove();
                }
            });
        };
        $('#btn-start-import').click(function () {
            $(this).before('<span class="fa fa-spinner fa-spin fa-2x"></span>');
            doImport($(this).data('import'), 0);
            $(this).remove();
        });
    });
}));

