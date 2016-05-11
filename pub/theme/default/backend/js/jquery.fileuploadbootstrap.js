$(function () {
	
	var uploadingImgI=0;
	var uploadingImgResult=false;
    if($('a.chooseimages').length>0){
    	$('a.chooseimages').bind('click',initiUpload);

    }
    
    function initiUpload(){
    	if($("div.uploadfilepopupb").length>0){
    		$("div.uploadfilepopupb").remove();
    	}
    	$('<div class="modal fade uploadfilepopupb" id="imagesModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'+
  			  '<div class="modal-dialog uploadfilepopupb" role="document"><div class="modal-content">'+
  		      '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
  		      '<h4 class="modal-title" id="imagesModalLabel">Images Managment</h4></div>'+
  		      '<div class="modal-body">'+
  		      '<ul class="nav nav-tabs images-tabs" role="tablist"><li role="presentation" class="active"><a href="#chooseimages" aria-controls="chooseimages" role="tab" data-toggle="tab">Choose Images</a></li>'+
  		      '<li role="presentation"><a href="#uploadimages" aria-controls="uploadimages" role="tab" data-toggle="tab">Upload Images</a></li></ul>'+
  		      '<div role="tabpanel" class="tab-pane active" id="chooseimages">'+
  		      '<div class="upimage-header"><p><select></select></p></div>'+
  		      '<div class="upimage-body">dgfdgfdgg</div></div>'+
  		      '<div role="tabpanel" class="tab-pane" id="uploadimages">'+
  		      '<div class="upimage-header"><p><input id="imagesfileupload" type="file" name="files[]" data-url="'+GLOBAL.BASE_URL+'admin/Resource_Resource/UploadImages/" multiple />  <button>Upload</button></p></div>'+
  		      '<div id="imgLoad" class="upimage-body"></div>'+
  		      '</div>'+
  		      '</div>'+
  		    '</div>'+
  		  '</div>'+
  		'</div').appendTo(document.body);
  $('div#imagesModal').modal({
  	  keyboard: false
  }).on('show.bs.modal', function (e) {
  	$('ul.images-tabs a').click(function (e) {
		  e.preventDefault()
		  $(this).tab('show')
		});

  });
 
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
						moveImg(imgId);
					}
			);				
			uploadingImgI++;
		}
		
	});
  
  
  
  
    }
    
    
    /**
	 *singer image cancel
	 */
	function moveImg(imgId){
		$("div#upImg"+imgId).remove();
	}
	
	
});