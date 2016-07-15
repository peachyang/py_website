(function (factory) {
    if (typeof define === "function" && define.amd) {
        define(["jquery","jquery.validate"], factory);
    } else if (typeof module === "object" && module.exports) {
        module.exports = factory(require(["jquery","jquery.validate"]));
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
            $(this).parents('.table').first().find('tbody')
                    .append($('#custom-options #tmpl-option-value').html().replace(/\{\$id\}/g, $(this).data('id')));
        }).on('click', '.delete-row', function () {
            $(this).parents('tr').first().remove();
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
        $('[href="#tab-inventory"]').on('show.bs.tab', function () {
            var result = [];
            $('#custom-options .option').each(function () {
                var input = $(this).find('[name^="options[input]"]').val();
                if ($.inArray(input, ['select', 'radio', 'checkbox', 'multiselect']) === -1) {
                    var sku = $(this).find('[name^="options[sku]"]').val();
                    var title = $(this).find('[name^="options[label]"]').val();
                    if (sku) {
                        var l = result.length;
                        if (l) {
                            for (var i = 0; i < l; i++) {
                                result.push({sku: result[i].sku + '-' + sku, title: result[i].title + '-' + title});
                            }
                        }
                        result.push({sku: $('#sku').val() + '-' + sku, title: title});
                    }
                } else {
                    var tmp = [];
                    $(this).find('.value tr').each(function () {
                        var sku = $(this).find('[name$="[sku][]"]').val();
                        var title = $(this).find('[name$="[label][]"]').val();
                        if (sku) {
                            if (result.length) {
                                for (var i = 0; i < result.length; i++) {
                                    tmp.push({sku: result[i].sku + '-' + sku, title: result[i].title + '-' + title});
                                }
                            } else {
                                tmp.push({sku: $('#sku').val() + '-' + sku, title: title});
                            }
                        }
                    });
                    result = tmp;
                }
            });
            if (result.length) {
                $('#tab-inventory .branch').each(function () {
                    var fg = document.createDocumentFragment();
                    var tmpl = $(this).next('.tmpl-inventory-branch');
                    var inventory = $(tmpl).data('inventory');
                    $(result).each(function () {
                        $(fg).append($(tmpl).html().replace(/\{\$title\}/g, this.title)
                                .replace(/\{\$sku\}/g, this.sku)
                                .replace(/\{\$qty\}/g, inventory[this.sku] && inventory[this.sku].qty ? inventory[this.sku].qty : 0)
                                .replace(/\{\$barcode\}/g, inventory[this.sku] && inventory[this.sku].barcode ? inventory[this.sku].barcode : ''));
                    });
                    $(this).show().find('tbody').html(fg);
                });
            }
        });
        $('.widget-upload').on('resource.selected', '.btn[data-toggle=modal]', function () {
            $(this).parents('.inline-box').find('[type=radio]').val($(this).siblings('input').val());
        });
        $('.edit form').on('submit', function () {
            if ($(this).valid()) {
                $(this).find('.grid .filters input,.grid .filters select,.grid .pager input').each(function () {
                    this.disabled = true;
                });
            }
        });
    });
}));