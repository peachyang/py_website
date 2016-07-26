(function (factory) {
    if (typeof define === "function" && define.amd) {
        define(["jquery"], factory);
    } else if (typeof module === "object" && module.exports) {
        module.exports = factory(require("jquery"));
    } else {
        factory(jQuery);
    }
}(function ($) {
    $(function () {
        "use strict";
        $('.pager .btn').click(function () {
            location.href = $(this).data('url') + $(this).siblings('input').val();
        });
        window.addMessages = function (messages) {
            var html = '';
            for (var i in messages) {
                html += '<div class="alert alert-' + messages[i].level + '">' + messages[i].message + '</div>';
            }
            $('.message-box').append(html);
        };
        var responseHandler = function (json) {
            var o = this;
            if (typeof json === 'string') {
                json = eval('(' + json + ')');
            }
            if (json.redirect) {
                location.href = json.redirect;
            } else if (json.reload) {
                location.reload();
            } else if (json.message.length) {
                addMessages(json.message);
            }
            if (json.removeLine) {
                if ($(o).is('menu a')) {
                    var t = $('[data-params="' + $(o).data('params') + '"]').parentsUntil('tbody,ul,ol,dl').last();
                } else {
                    var t = $(o).parentsUntil('tbody,ul,ol,dl').last();
                }
                if ($(t).is('tr,li,dt,dd')) {
                    $(t).remove();
                } else {
                    $(json.removeLine).each(function () {
                        $(o).parents('[data-id=' + this + ']').first().remove();
                    });
                }
            }
            $(o).trigger('afterajax.seahinet', json);
        };
        $(document.body).on('click.seahinet.ajax', 'a[data-method]', function () {
            var o = this;
            if ($(o).data('method') !== 'delete' || confirm(translate($(o).is('[data-serialize]') ? 'Are you sure to delete these records?' : 'Are you sure to delete this record?'))) {
                if ($(o).is('[data-params]')) {
                    var data = $(o).data('params');
                } else if ($(o).is('[data-serialize]')) {
                    var data = $($(o).data('serialize')).find('input:not([type=radio]):not([type=checkbox]),[type=radio]:checked,[type=checkbox]:checked,select,textarea,button[name]').serialize();
                } else {
                    var data = '';
                }
                if (!GLOBAL.AJAX) {
                    GLOBAL.AJAX = {};
                } else if (GLOBAL.AJAX[$(o).attr('href')]) {
                    GLOBAL.AJAX[$(o).attr('href')].abort();
                }
                GLOBAL.AJAX[$(o).attr('href')] = $.ajax($(o).attr('href'), {
                    type: $(o).data('method'),
                    data: data,
                    success: function (xhr) {
                        responseHandler.call(o, xhr.responseText ? xhr.responseText : xhr);
                    }
                });
            }
            return false;
        }).on('submit.seahinet.ajax', 'form[data-ajax]', function () {
            var o = this;
            if (!GLOBAL.AJAX) {
                GLOBAL.AJAX = {};
            } else if (GLOBAL.AJAX[$(o).attr('action')]) {
                GLOBAL.AJAX[$(o).attr('action')].abort();
            }
            GLOBAL.AJAX[$(o).attr('action')] = $.ajax($(o).attr('action'), {
                type: $(o).attr('method'),
                data: $(this).serialize(),
                success: function (xhr) {
                    responseHandler.call(o, xhr.responseText ? xhr.responseText : xhr);
                    if ($(o).parents('.modal').length) {
                        $(o).parents('.modal').modal('hide');
                    }
                }
            });
            return false;
        });
    });
    $(".selectall").click(function(){
    	var this_value = $(this).val();
    	var this_status = this.checked;;
    	$(".checkbox-"+this_value).prop("checked",this_status);
    	if(this_value == "on"){
    		$("input[type='checkbox']").prop("checked",this_status);
    	}
    });
    $(".plus").click(function(){
    	var this_input = $(this).prev();
    	this_input.val(Number(this_input.val())+1);
    	qty_change(this_input);
    });
    $(".minus").click(function(){
    	var this_input = $(this).next();
    	if(Number(this_input.val()) !== 1){
    		this_input.val(Number(this_input.val())-1);
    		qty_change(this_input);
    	}
    });
    $(".product-list .tb-stock .required").on('input',function(){qty_change($(this))});
    function qty_change(e){
  	   var num = e.val();
  	   var price = e.parent().prev().html().replace(/[^0-9]/ig,"")/100;
  	   var subtotal = $.trim(e.parent().prev().html()).substr(0,1) + num * price + '.00';
  	   var qty_change = $("#qty-change").val();
  	   if(num == 0){
  		 e.val(1);
  		 //qty_change(e)；
  	   }
  	   if(qty_change == 0){
  		   $("#qty-change").val(1);
  	   }
  	   e.parent().next().find("span").html(subtotal);
  	   total_change();
     };
     function total_change(){
    	 var total_num = 0;
    	 $("tbody").find("input.required").each(function(){
    		 if($(this).parent().parent().find("td:eq(1)").find("input").is(':checked')){
    			 total_num += Number($(this).val());
    		 }
    	 });
    	 $('.select-qyt').html(total_num);
    	 var total_price = 0;
    	 $("tbody .product-list").find("td:eq(5)").find("span.checkout-num").each(function(){
    		 if($(this).parent().parent().find("td:eq(1)").find("input").is(':checked')){
    			 total_price += $(this).html().replace(/[^0-9]/ig,"")/100;
    		 }
    	 });
    	 $('.total-pirce').html($.trim($("#cart tbody").find("tr:eq(1)").find("td:eq(3)").html()).substr(0,1) + total_price + '.00');
     };
	 $("input:checkbox").change(function() {
		 total_change();
	 }); 
}));
















































