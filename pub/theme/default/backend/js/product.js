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
        var ajax = null;
        $('.grid').on('click', '.filters [formaction],.sort-by a,.pager a', function () {
            var p = $(this).parents('.grid');
            if (ajax) {
                ajax.readyState < 4 ? ajax = null : ajax.abort();
            }
            var u = $(p).find('.filters [formaction]').attr('formaction');
            var m = $(p).find('.filters [name]').serialize() + '&id=' + $('[type=hidden][name=id]').val();
            var s = '&' + ($(this).is('.sort-by a') ? $(this).attr('href').match(/(?:a|de)sc=[^\&]+/) : ($(p).find('.sort-by .asc,.sort-by .desc').length ? $(p).find('.sort-by .asc,.sort-by .desc').attr('href').match(/(?:a|de)sc=[^\&]+/) : ''));
            var e = '&page=' + ($(this).is('.pager a') ? $(this).parents('[data-page]').data('page') : ($(p).find('.pager .current').length ? $(p).find('.pager .current').parents('[data-page]').data('page') : 1));
            ajax = $.get(u, m + s + e, function (response) {
                var fg = document.createDocumentFragment();
                $(fg).html(response);
                $(p).html($(fg).find('.grid').html());
            });
            return false;
        }).on('click', '.filters [href].btn', function () {
            var p = $(this).parents('.grid');
            if (ajax) {
                ajax.readyState < 4 ? ajax = null : ajax.abort();
            }
            ajax = $.get($(this).attr('href'), 'id=' + $('[type=hidden][name=id]').val(), function (response) {
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
            $('input[type=hidden][name=options][value=null]').remove();
        }).on('click', '.delete-option', function () {
            if ($(this).parents('.option').first().siblings('.option').length === 0) {
                $('table.option').first().before('<input type="hidden" name="options" value="null" />');
            }
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
            var pushStack = function (n, l, t) {
                if ($.inArray(n, ['select', 'radio', 'checkbox', 'multiselect']) === -1) {
                    var sku = $(this).find('[name^="options[sku]"]').val();
                    var title = $(this).find('[name^="options[label]"]').val();
                    if (sku) {
                        if (l) {
                            for (var i = 0; i < l; i++) {
                                t.push({sku: result[i].sku + '-' + sku, title: result[i].title + '-' + title});
                            }
                        } else {
                            t.push({sku: $('#sku').val() + '-' + sku, title: title});
                        }
                    }
                } else {
                    $(this).find('.value tr').each(function () {
                        var sku = $(this).find('[name$="[sku][]"]').val();
                        var title = $(this).find('[name$="[label][]"]').val();
                        if (sku) {
                            if (l) {
                                for (var i = 0; i < l; i++) {
                                    t.push({sku: result[i].sku + '-' + sku, title: result[i].title + '-' + title});
                                }
                            } else {
                                t.push({sku: $('#sku').val() + '-' + sku, title: title});
                            }
                        }
                    });
                }
                result = t;
            };
            var hasRequired = false;
            $('#custom-options .option').each(function () {
                if (parseInt($(this).find('[name^="options[is_required]"]').val())) {
                    var input = $(this).find('[name^="options[input]"]').val();
                    var l = result.length;
                    hasRequired = true;
                    pushStack.call(this, input, l, []);
                }
            });
            $('#custom-options .option').each(function () {
                if (!parseInt($(this).find('[name^="options[is_required]"]').val())) {
                    var input = $(this).find('[name^="options[input]"]').val();
                    var l = result.length;
                    pushStack.call(this, input, l, result);
                }
            });
            if (result.length) {
                if (!hasRequired) {
                    result.unshift({sku: $('#sku').val(), title: ''});
                }
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
        $('.table.additional').on('click', '.add', function () {
            $('.table.additional .target').append($('#tmpl-additional').html());
        }).on('click', '.delete', function () {
            $(this).parents('tr').first().remove();
        });
        $('#tab-category .dropdown-toggle').on('click', function () {
            $(this).toggleClass('active');
            $(this).siblings('ul').slideToggle();
        });
        var checkTree = function () {
            var p = $(this).parent();
            if (this.checked) {
                var o = $(p).parent().siblings('[type=checkbox]');
                $(o).prop('checked', true);
                if (!$(p).parent().is('.category')) {
                    checkTree.call(o[0]);
                }
            } else {
                var f = true;
                $(p).siblings().each(function () {
                    if ($(this).children('[type=checkbox]').prop('checked')) {
                        f = false;
                        return false;
                    }
                });
                if (f) {
                    var o = $(p).parent().siblings('[type=checkbox]').first();
                    $(o).prop('checked', false);
                    if (!$(p).parent().is('.category')) {
                        checkTree.call(o[0]);
                    }
                }
            }
        };
        $('#tab-category [type=checkbox]').on('click', function () {
            checkTree.call(this);
        });
        var recalcGroupPrice = function () {
            var v = {};
            $('.table.group-price tbody tr').each(function () {
                if ($(this).find('.price').val() !== '') {
                    v[$(this).find('.group').val()] = $(this).find('.price').val();
                }
            });console.log(v);
            $('.table.group-price~input[name]').val(JSON.stringify(v));
        };
        $('.table.group-price').on('change', 'select,input', recalcGroupPrice)
                .on('click', '.add', function () {
                    $(this).parentsUntil('table').last().siblings('tbody').append($(this).parentsUntil('table').last().parent().next('template').html());
                }).on('click', '.delete', function () {
            $(this).parentsUntil('tbody').last().remove();
            recalcGroupPrice();
        });
        var recalcTierPrice = function () {
            var v = {};
            $('.table.tier-price tbody tr').each(function () {
                if ($(this).find('.qty').val() !== '' && $(this).find('.price').val() !== '') {
                    if (typeof v[$(this).find('.group').val()] === 'undefined') {
                        v[$(this).find('.group').val()] = {}
                    }
                    v[$(this).find('.group').val()][$(this).find('.qty').val()] = $(this).find('.price').val();
                }
            });
            $('.table.tier-price~input[name]').val(JSON.stringify(v));
        };
        $('.table.tier-price').on('change', 'select,input', recalcTierPrice)
                .on('click', '.add', function () {
                    $(this).parentsUntil('table').last().siblings('tbody').append($(this).parentsUntil('table').last().parent().next('template').html());
                }).on('click', '.delete', function () {
            $(this).parentsUntil('tbody').last().remove();
            recalcTierPrice();
        });
    });
}));
