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
        
        $(".product_status").click(function(){
            var  product_ids = new Array();
            $(".recommend input[type=checkbox").each(function(){
                if($(this).is(":checked")){
                    product_ids.push($(this).attr("data-id"));
                }
            });
            if(product_ids.length > 0){
                $.ajax({
                   type: "POST",
                   url: GLOBAL.BASE_URL + "retailer/product/status",
                   data: {'product_ids' : product_ids},
                   dataType: "json",
                   success: function(msg){
                     addMessages(msg.message);
                     $.each(product_ids, function(i, o){
                         $(".transaction-list-sales[data-id="+o+"]").remove();
                     });
                   }
                });
            }
        });
        
        $(".product_recommend").click(function(){
            var  product_ids = new Array();
            $(".recommend input[type=checkbox").each(function(){
                if($(this).is(":checked")){
                    product_ids.push($(this).attr("data-id"));
                }
            });
            if(product_ids.length > 0){
                var  recommend_type = $(this).attr("data-type");
                $.ajax({
                    type: "POST",
                    url: GLOBAL.BASE_URL + "retailer/product/recommend",
                    data: {'product_ids' : product_ids, 'type' : recommend_type},
                    dataType: "json",
                    success: function(msg){
                        addMessages(msg.message);
                        $.each(product_ids, function(i, o){
                            if(recommend_type == 1){
                                $(".recommend input[type=checkbox][data-id="+o+"]").next("span").text("已推荐");
                            }else{
                                $(".recommend input[type=checkbox][data-id="+o+"]").next("span").text("");
                            }
                        });
                    }
                });
            }
        });
        
    });
}));