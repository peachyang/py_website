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
        "use strict";
        $(".tabs-nav li").hover(function(){
            if($(this).hasClass('current')){
                return false;
            }else{
                $(this).siblings().removeClass('current');
                $(this).addClass("current");
                $(".tabs-panels .info-box").hide().eq($(this).index()).show();
            }
        });
        
        if($("#add_time_from").length > 0){
            $("#add_time_from").datepicker();
        }
        if($("#add_time_to").length > 0){
            $("#add_time_to").datepicker();
        }
        
        $(".products-content").on('click', '[type=checkbox]', function () {
            var flag = this.checked;
            var parent = $(this).parents('.table').last();
            console.log(flag);
            if ($(this).is('.selectall')) {
                $(".list-info").find('[type=checkbox]').each(function () {
                    this.checked = flag;
                });
                $(".selectall[type=checkbox]").prop('checked', flag);
            } else if (flag && !$(".transaction-list-sales").find('[type=checkbox]').not('.selectall,:checked').length) {
                $(".selectall[type=checkbox]").prop('checked', flag);
            } else if (!flag && $(".transaction-list-sales").find('[type=checkbox]').not('.selectall,:checked').length) {
                $(".selectall[type=checkbox]").prop('checked', flag);
            }
        });
    });
    
}));