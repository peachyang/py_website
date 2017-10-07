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
        $.fn.grid = function () {
            var t = $(this).find('[data-id]');
            if ($(this).find('[type=checkbox].selectall').length) {
                $(this).on('click', '[type=checkbox]', function () {
                    var flag = this.checked;
                    var parent = $(this).parents('.table').last();
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
            $(this).find('tbody td').click(function () {
                if ($(this).find('a,button,input,select,textarea').length) {
                    return;
                } else if ($(this).siblings('.checkbox').length) {
                    $(this).siblings('.checkbox').children('[type=checkbox]').trigger('click');
                } else if ($(this).parent('tr').data('href')) {
                    location.href = $(this).parent('tr').data('href');
                }
            });
            if ($(t).find('.action').length) {
                $(t).on('contextmenu', function (e) {
                    var m = $('<menu class="context"></menu>');
                    $(this).children('.action').children('a').each(function () {
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