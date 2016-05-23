(function( factory ) {
	if ( typeof define === "function" && define.amd ) {

		// AMD. Register as an anonymous module.
		define([ "jquery" ], factory );

	} else if ( typeof exports === "object" ) {

		// Node/CommonJS
		factory( require( "jquery" ) );

	} else {

		// Browser globals
		factory( jQuery );
	}
}(function( $ ) {
	
$.fileuploadbootstrap ={
		initiUpload:function(){
	    	
	    	if($("div.uploadfilepopupb").length>0){
	    		$("div.uploadfilepopupb").remove();
	    	}
	    	$("div#chooseimagesbody").load(GLOBAL.BASE_URL+'admin/Resource_Resource/popupListImages/');
	    	$('<div class="modal fade uploadfilepopupb" id="imagesModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'+
	  			  '<div class="modal-dialog uploadfilepopupb" role="document"><div class="modal-content">'+
	  		      '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
	  		      '<h4 class="modal-title" id="imagesModalLabel">Images Managment</h4></div>'+
	  		      '<div class="modal-body"><input type="hidden" name="reldata" id="reldata" value="'+$(this).attr('widget')+'" order="'+$(this).attr('order')+'" />'+
	  		      '<ul class="nav nav-tabs images-tabs" role="tablist"><li role="presentation" class="active"><a class="chooseimages" href="#chooseimages" aria-controls="chooseimages" role="tab" data-toggle="tab">Choose Images</a></li>'+
	  		      '<li role="presentation"><a href="#uploadimages" aria-controls="uploadimages" role="tab" data-toggle="tab">Upload Images</a></li></ul>'+
	  		      '<div role="tabpanel" class="tab-pane active" id="chooseimages">'+
	  		      '<div class="upimage-body" id="chooseimagesbody"></div></div>'+
	  		      '<div role="tabpanel" class="tab-pane" id="uploadimages">'+
	  		      '<div class="upimage-header"><p><input id="imagesfileupload" type="file" name="files[]" data-url="'+GLOBAL.BASE_URL+'admin/Resource_Resource/UploadImages/" multiple />  <button>Upload</button></p></div>'+
	  		      '<div id="imgLoad" class="upimage-body"></div>'+
	  		      '</div>'+
	  		      '</div>'+
	  		    '</div>'+
	  		  '</div>'+
	  		'</div').appendTo(document.body);
	  $('div#imagesModal').on('show.bs.modal', function (e) {
		  $('a.chooseimages').on('shown.bs.tab', function (e) {
			  $.fileuploadbootstrap.loadImagesList('{limit: 25}');
		})
	  }).modal({
	  	  keyboard: false
	  });
	  $.fileuploadbootstrap.loadImagesList('{limit: 25}');
	  $("input#imagesfileupload").fileupload({
			dataType: 'json',
			add : function(e, data) {
				var imgId =uploadingImgI;
				//将需要上传的图片保存变成HTML，在页面上方便的调用
				data.order=imgId;
				var str = "<div id='upImg"+imgId+"' />";
				data.context ='<span class="upimage-span-name">'+$(str).html(data['files'][0].name+'</span><span id="uploadnote'+imgId+'"  class="upimage-span-note">Waiting...</span>').appendTo($("div#imgLoad")).click(function() {
					$('span#uploadnote'+imgId).html('Uploading...');
					data.submit().success(function (result,textStatus, jqXHR) {
						//console.log(result);
						if(result.error==0){
							$('span#uploadnote'+imgId).html('Finished...');
							//alert(result.files[0].name);
							$('div#upImg'+imgId).remove();
							$.each(result.files, function(index, file) {
								//console.log('span#upImg'+imgId);
								uploadingImgResult=true;
							});	
						}else{
							alert(result.error);
						}

					});						
				});
				//add the image cancle button
				data.context = $('<span class="fa fa-remove upimage-span-move" id="ImgMove'+imgId+'" />').appendTo($("div#upImg"+imgId)).click(
						function(){
							$.fileuploadbootstrap.moveImg(imgId);
						}
				);				
				uploadingImgI++;
			}
			
		});
	  
	    },
	   loadImagesList:function(dataA){
	    	$("div#chooseimagesbody").load(GLOBAL.BASE_URL+'admin/Resource_Resource/popupListImages/',dataA,function(){
	    		
	    		$("div#chooseimagesbody img").on('click',function(){
	    			if($('a[rel='+$("input#reldata").val()+'] img').length>0){
	    				$('a[rel='+$("input#reldata").val()+'][order='+$("input#reldata").attr('order')+'] img').prop('src',$(this).attr('src')).prop('alt',$(this).attr('alt'));
	    			}
	    			
	    			var dataStr=$(this).attr('alt')+':'+$(this).attr('id')+':'+$("input#reldata").attr('order');
	    			$('input#'+$("input#reldata").val()).val(dataStr);
	    			$('div#imagesModal').modal('hide');
	    		});
	    		
	    	});
	    },
	    /**
		 *singer image cancel
		 */
		moveImg:function(imgId){
			$("div#upImg"+imgId).remove();
		}

}


var uploadingImgI=0;
var uploadingImgResult=false;
if($('a.chooseimages img').length>0){
	$('a.chooseimages img').bind('click',$.fileuploadbootstrap.initiUpload);

}
$("i.resourceimageaplusi").bind('click',function(){
	var str='<a class="resourceimagea chooseimages" rel="'+$(this).attr("rel")+'" order="'+$(this).parent().find('a').length+'">'+
    '<img src="'+GLOBAL.PUB_URL+'backend/images/upload_image.jpg" alt="" id="" widget="'+$(this).attr("rel")+'" order="'+$(this).parent().find('a').length+'" /><br />'+
    '<i class="fa fa-arrow-left left"></i><i class="fa fa-trash-o delete"></i><i class="fa fa-arrow-right right"></i></a>';
	$(this).before(str);
	$('a.chooseimages img').bind('click',$.fileuploadbootstrap.initiUpload);
});
	
}));