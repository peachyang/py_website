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
    });
    
}));