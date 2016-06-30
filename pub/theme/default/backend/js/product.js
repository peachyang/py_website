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
        });
        $('#custom-options').on('click', '.add-option', function () {
            var odiv = $('<div class="option"></div>');
            $(odiv).html($('#custom-options .option').last().html());
            $(odiv).find('input,select').val('');
            $(this).before(odiv);
        }).on('click', '.delete-option', function () {
            var p = $(this).parent('.option');
            if ($(p).siblings('.option').length) {
                $(this).remove();
            } else {
                $(p).find('input,select').val('');
            }
        }).on('click', '.add-row', function () {
            var p = $(this).parents('.table').last();
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
        });
        $('.sortable').sortable({
            item: 'tr'
        });
    });
}));