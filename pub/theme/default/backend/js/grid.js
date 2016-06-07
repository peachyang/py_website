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
        $.fn.grid = function () {
            var t = $(this).find('[data-id]');
            if ($(t).has('.action')) {
                $(t).on('contextmenu', function (e) {
                    var m = $('<menu class="context"></menu>');
                    $(this).find('.action').children('a').each(function () {
                        var oa = $('<a href="' + this.href + '">' + $(this).html() + '</a>');
                        if ($(this).is('[data-method]')) {
                            $(oa).attr('data-method', $(this).data('method'));
                            if ($(this).is('[data-params]')) {
                                $(oa).attr('data-params', $(this).data('params'));
                            } else if ($(this).is('[data-serialize]')) {
                                $(oa).attr('data-serialize', $(this).data('serialize'));
                            }
                        }
                        $(oa).find('.sr-only').removeClass('sr-only');
                        var oli = $('<li></li>');
                        oli.append(oa);
                        $(m).append(oli);
                    });
                    $(m).css({top: e.pageY - 4 + 'px', left: e.pageX - 4 + 'px'}).on('mouseleave', function () {
                        $(this).remove();
                    }).appendTo(document.body);
                    return false;
                });
            }
        };
        $('.grid .table').grid();
    });
}));