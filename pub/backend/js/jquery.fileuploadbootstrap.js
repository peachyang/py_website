$(function () {
	
	var uploadingImgI=0;

    if($('input.chooseimages').length>0){
    	$('input.chooseimages').bind('click',initiUpload);

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
  		      '<div role="tabpanel" class="tab-pane active" id="chooseimages">fsdfsdfffffff</div>'+
  		      '<div role="tabpanel" class="tab-pane" id="uploadimages">'+
  		      '<div><p><input id="imagesfileupload" type="file" name="files[]" data-url="'+GLOBAL.BASE_URL+'admin/fileUpload/UploadImages/" multiple />  <button>Upload</button></p></div>'+
  		      '<div style="height:200px;" id="imgLoad"></div>'+
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
			var str = "<span class='upimgspan' id='upImg"+imgId+"' />";
			console.log(str);
			data.context = $(str).html(data['files'][0].name+'<div id="progress'+imgId+'" class="upimgprogress"><div class="bar"></div></div>').appendTo($("div#imgLoad")).click(function() {
				data.submit().success(function (result,textStatus, jqXHR) {
					console.log(result);
					console.log(data);
					if(result.error=='success'){
						//alert(result.files[0].name);
						$('span#upImg'+imgId).remove();
						$.each(result.files, function(index, file) {
							//console.log('span#upImg'+imgId);
							//$.fwutility.uploadingImgResult=true;
						});	
					}else{
						alert(result.error);
					}

				});						
			});
			//add the image cancle button
			data.context = $('<button id="ImgMove'+imgId+'" class="imgMove" />').html('move').appendTo($("span#upImg"+imgId)).click(
					function(){
						moveImg(imgId);
					}
			);				
			uploadingImgI++;
		},
		progress: function (e,data) {
			//console.log(data.order);
			var progress = parseInt(data.loaded / data.total * 100, 10);
			$('div#progress'+data.order+' .bar').css(
					'width',
					progress + '%'
			);
			//console.log(progress);
		}
	});
  
  
  
  
    }
    
    
    /**
	 *singer image cancel
	 */
	function moveImg(imgId){
		$("span#upImg"+imgId).remove();
	}
	
	
});