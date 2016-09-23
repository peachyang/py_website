	$(document).ready(function(){
		$('.hiSlider3').each(function(){
			pic_carousel_init($(this).closest('.function-tag'));	
		});
				
	});
	
    function pic_carousel_init(obj){
    		obj.find('.hiSlider3').hiSlider({
	        	isFlexible: true,
	        	titleAttr: function(curIdx){
	            return $('img', this).attr('alt');
	        	}
	    	});
	    	
	    	var data_param = obj.attr("data-param");
	    	var data_id = obj.attr("data-id");
	    	//console.log(data_id);
	    	
	    	if($.trim(data_param)!="")
	    	{
	    		data_param = JSON.parse(decodeURIComponent(data_param));
	    	
	    		if(data_param.show_column==1)
	    			$("#" + data_id).find('.pic_carousel .title').css('display','');
	    		else
	    			$("#" + data_id).find('.pic_carousel .title').css('display','none');
	    	
	    		if(data_param.heightSetType==1)
	    			obj.find('.hiSlider3').css('height',data_param.heightSet+"px");
	    		else
	    			obj.find('.hiSlider3').css('height','100%');
	    		
	    		$("#" + data_id).find('.pic_carousel .title h2').html(data_param.title);
	    		
	    		
	    	
	    	}
	    	
	    	$('.hiSlider3').closest('.box').mouseover(function(){
	    		component_reset();
	    	});
	    	
	        $('.hiSlider3').closest('.box').mouseout(function(){
	    		component_reset();
	    	});
    }
    
    function component_reset(){
    	$('.hiSlider3').resize();
    }