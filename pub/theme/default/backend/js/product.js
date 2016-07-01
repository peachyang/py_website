(function (factory) {
    if (typeof define === "function" && define.amd) {
        define(["jquery"], factory);
    } else if (typeof module === "object" && module.exports) {
        module.exports = factory(require(["jquery"]));
    } else {
        factory(jQuery);
    }
}(function ($) {
    $(function () {
        var ajax = null;
        $('.grid').on('click', '.filters [formaction]', function () {
            var p = $(this).parents('.grid');
            if (ajax) {
                ajax.abort();
            }
            ajax = $.get($(this).attr('formaction'), $(p).find('.filters [name]').serialize(), function (response) {
                var fg = document.createDocumentFragment();
                $(fg).html(response);
                $(p).html($(fg).find('.grid').html());
            });
            return false;
        }).on('click', '.filters [href].btn,.sort-by a,.pager a', function () {
            var p = $(this).parents('.grid');
            if (ajax) {
                ajax.abort();
            }
            ajax = $.get($(this).attr('href'), function (response) {
                var fg = document.createDocumentFragment();
                $(fg).html(response);
                $(p).html($(fg).find('.grid').html());
            });
            return false;
        }).on('click', '[type=checkbox]', function () {
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
        $('#custom-options').on('click', '.add-option', function () {
            var id = -1000;
            $(this).prevAll('.option').each(function () {
                if ($(this).data('id') > id) {
                    id = $(this).data('id');
                }
            });
            $(this).before($('#custom-options #tmpl-option').html().replace(/\{\$id\}/g, id + 1));
            $(this).prev('.option').find('.sortable').sortable({
                item: 'tr'
            });
        }).on('click', '.delete-option', function () {
            $(this).parents('.option').remove();
        }).on('click', '.add-row', function () {
            var p = $(this).parents('.table').first();
            var otr = $('<tr></tr>');
            $(otr).html($(p).find('tbody tr').html());
            $(otr).find('input,select').val('');
            $(p).find('tbody').append(otr);
        }).on('click', '.delete-row', function () {
            var p = $(this).parents('tr');
            if ($(p).siblings('tr').length) {
                $(this).remove();
            } else {
                $(p).find('input,select').val('');
            }
        }).on('change', 'select[name^="options[input]"]', function () {
            var p = $(this).parents('tr');
            if ($.inArray($(this).val(), ['select', 'radio', 'checkbox', 'multiselect']) === -1) {
                $(p).siblings('.value').hide();
                $(p).siblings('.non-value').show();
            } else {
                $(p).siblings('.value').show();
                $(p).siblings('.non-value').hide();
            }
        });
        $('.sortable').sortable({
            item: 'tr'
        });
    });
}));