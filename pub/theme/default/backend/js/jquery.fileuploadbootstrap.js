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
		uploadingImgI:0,
		uploadingImgResult:false,
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
	  		      '<div class="tab-pane upimage-body" id="uploadimages">'+
	  		      '<div class="breadcrumb clearfix"></div><div class="grid"><div class="filters upimage-header"><form class="form-inline">'+
	  		      '<div class="input-box"><label class="control-label" for="category_id">Category </label><div class="cell">'+
	  		      '<select class="form-control " id="category_id" name="category_id"><option value="">(Top category)</option><option value="21">Prosuct Images</option><option value="22">Dress</option></select>'+
	  		      '</div></div>'+
	  		      '<div class="input-box"><label class="control-label" for="imagesfileupload">Choose Images </label><div class="cell">'+
	  		      '<input id="imagesfileupload" type="file" name="files[]" class="form-control" data-url="'+GLOBAL.BASE_URL+'admin/Resource_Resource/UploadImages/" multiple />'+
	  		      '</div></div>'+
	  		      '<div class="input-box"><a class="control-a" onclick="$.fileuploadbootstrap.upImgFro();">Upload</a></div>'+
	  		      '</form></div>'+
	  		      '<div id="imgLoad" class="image-body-list"></div>'+
	  		      '</div>'+
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
				var imgId =$.fileuploadbootstrap.uploadingImgI;
				//将需要上传的图片保存变成HTML，在页面上方便的调用
				data.order=imgId;
				var str = '<div id="upImg'+imgId+'" class="upimgdiv"/>';
				data.context =$(str).html('<span class="upimage-name">'+data['files'][0].name+'</span><span id="uploadnote'+imgId+'"  class="upimage-note">Waiting...</span>').appendTo($("div#imgLoad")).click(function() {
					
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
				data.context = $('<span class="fa fa-remove upimage-move" id="imgmove'+imgId+'" />').appendTo($("div#upImg"+imgId)).click(
						function(){
							$.fileuploadbootstrap.moveImg(imgId);
						}
				);				
				$.fileuploadbootstrap.uploadingImgI++;
			}
			
		});
	  
	    },
	   loadImagesList:function(dataA){
	    	$("div#chooseimagesbody").load(GLOBAL.BASE_URL+'admin/Resource_Resource/popupListImages/',dataA,function(){
	    		
	    		$("div#chooseimagesbody img").on('click',function(){
	    			if($('a[rel='+$("input#reldata").val()+'] img').length>0){
	    				$('a[rel='+$("input#reldata").val()+'][order='+$("input#reldata").attr('order')+'] img').prop('src',$(this).attr('src')).prop('alt',$(this).attr('alt')).prop('id',$(this).attr('id'));
	    			}
	    			var dataStr='';
	    			if($('a[rel='+$("input#reldata").val()+'] img').length>0){
	    				$('a[rel='+$("input#reldata").val()+'] img').each(function(){
	    					console.log($(this));
	    					if($(this).attr('alt')!=''&&$(this).attr('id')!=''){
	    						dataStr=dataStr+$(this).attr('alt')+':'+$(this).attr('id')+':'+$(this).attr('order')+',';
	    					}
	    				});
	    			}
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
		},
	    upImgFro:function(imagsrcname,imagname){	
			var anchors = $("div.upimgdiv");
			if(anchors.length!=0){
				for(var i=0;i<anchors.length;i++){
					$.fileuploadbootstrap.uploadingImgResult=false;
					anchors[i].click();
					if(i==(anchors.length-1)){
						this.checkUploadTolist(i,anchors.length-1,imagsrcname,imagname);
					}
				}	
			}else{
				alert('Please choose images!');
			}
					
		},
		/**
		 *检查图片是否全部上传完
		 */
		checkUploadTolist:function(i,vlength,imagsrcname,imagname){
			if(i==vlength && $.fileuploadbootstrap.uploadingImgResult){
				this.listImg(1,this.merchantId,imagsrcname,imagname);
				clearTimeout($.fileuploadbootstrap.checkUploadImgFunTimeOut);
			}else{
				$.fileuploadbootstrap.checkUploadImgFunTimeOut=setTimeout('$.fileuploadbootstrap.checkUploadTolist('+i+','+vlength+',"'+imagsrcname+'","'+imagname+'")',1000);
			}
		}

}

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