(function (factory) {
    if (typeof define === "function" && define.amd) {
        define(["jquery", "jquery.ui.sortable"], factory);
    } else if (typeof module === "object" && module.exports) {
        module.exports = factory(require(["jquery", "jquery.ui.sortable"]));
    } else {
        factory(jQuery);
    }
}(function ($) {
    $(function () {
        "use strict";
        $('#attribute-options').on('click', 'a.add', function () {
            var o = $('<div class="template"></div>');
            $(o).html($('#attribute-options .template').first().html());
            $(o).find('input').attr('name', function () {
                return $(this).attr('name').replace(/\[\-\d+\]$/, '[]');
            }).val('');
            $(this).before(o);
            return false;
        }).on('click.seahinet', 'a.delete', function () {
            var p = $(this).parents('#attribute-options .template');
            if ($(p).siblings('.template').length) {
                $(p).remove();
            } else {
                $(p).find('input').val('');
            }
            return false;
        });
        if ($('#unapplied-attribute').length) {
            var sortParams = {
                items: '.item',
                connectWith: '#unapplied-attribute,#attribute-groups .group .content',
                placeholder: 'item placeholder',
                revert: true,
                update: function (e, ui) {
                    $(ui.item).removeAttr('style');
                    $(ui.item).find('input').each(function () {
                        if ($(this).parents('[data-id]').length) {
                            this.disabled = false;
                            this.name = 'attributes[' + $(this).parents('[data-id]').last().data('id') + '][]';
                        } else {
                            this.disabled = true;
                        }
                    });
                }
            };
            $('#unapplied-attribute').sortable(sortParams);
            $('#attribute-groups .group .content').sortable(sortParams).on('click.seahinet', 'a.remove', function () {
                $(this).siblings('input').attr({'disabled': 'disabled', 'name': 'attributes[]'});
                $(this).parent('.item').appendTo('#unapplied-attribute');
            });
            $('#attribute-groups').on('click.seahinet', '.group>.remove', function () {
                $(this).next('.content').find('.remove').trigger('click');
            });
            $('#new-group form').on('afterajax.seahinet', function (e, json) {
                var o = $('#attribute-groups .group>.remove');
                if (o.length) {
                    var odiv = document.createElement('div');
                    $(odiv).attr({class: 'group', 'data-id': json.data.id}).html('<h4 class="title">' + json.data.name +
                            '</h4><a href="' + $(o).attr('href') +
                            '" data-method="delete" class="remove" data-params="id=' +
                            $(o).data('params').replace(/^id\=[^\&]+&/, json.data.id +
                            '&') + '">' + $(o).html() + '</a><div class="content"></div>');
                    $('#attribute-groups').append(odiv);
                    $(odiv).children('.content').sortable(sortParams).on('click.seahinet', 'a.remove', function () {
                        $(this).parent('.item').appendTo('#unapplied-attribute');
                    });
                } else {
                    location.reload();
                }
            });
        }
    });
}));