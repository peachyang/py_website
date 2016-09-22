	$(document).ready(function(){
		$('.hiSlider3').each(function(){
			pic_carousel_init($(this).closest('.function-tag'));	
		});
		
	});
	
    function pic_carousel_init(obj){
    	   console.log(obj);
    		obj.find('.hiSlider3').hiSlider({
	        	isFlexible: true,
	        	titleAttr: function(curIdx){
	            return $('img', this).attr('alt');
	        	}
	    	});
    }
    
    function component_reset(){
    	$('.hiSlider3').resize();
    }