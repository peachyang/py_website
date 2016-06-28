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
        $('.grid .table .sortable').sortable({
            items: 'li',
            connectWith: '.sortable',
            placeholder: 'placeholder',
            revert: true,
            update: function (e, ui) {
                $(ui.item).removeAttr('style');
                var id = $(ui.item).parents('[data-id]').first().data('id');
                $(ui.item).find('input').each(function () {
                    this.value = id;
                });
            }
        });
    });
}));