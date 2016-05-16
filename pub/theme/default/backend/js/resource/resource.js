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
        $('select#language_id--').bind('change',function(){
        	var languageHtml='<table>';
        	$(this).find('option:selected').each(function(){
        		languageHtml=languageHtml+'<tr><td>( '+$(this).text()+' )</td><td><input type="text" name=name_'+$(this).val()+' id=name_'+$(this).val()+' class="form-control required" /></td></tr>';
        	});
        	languageHtml=languageHtml+'</table>';
        	$('div#category_name').html(languageHtml);
        });
 
    });
}));