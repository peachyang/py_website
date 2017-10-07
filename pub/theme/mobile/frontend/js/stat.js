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
        $('.panel-stat [data-url]').each(function () {
            var o = this;
            $.get($(o).data('url')).success(function (xhr) {
                var r = typeof xhr === 'string' ? eval('(' + xhr + ')') : xhr;
                for (var i in r) {
                    $(o).find('.data.' + i).text(r[i]);
                }
            });
        });
    });
}));
(function i_btn(env) {
    env.next('.Management-menu').find('.panel-menu').toggle();
    var btn_i = env.find('a');
    if (btn_i.is('.fa-chevron-down')) {
        btn_i.removeClass();
        btn_i.addClass('fa fa-chevron-up text-center btn_i');
    } else {
        btn_i.removeClass();
        btn_i.addClass('fa fa-chevron-down text-center btn_i');
    }
});
