$(function () {
    $('#fileupload').fileupload({
        dataType: 'json',
        add: function (e, data) {
            data.context = $('<button/>').text('Upload')
                .appendTo(document.body)
                .click(function () {
                    data.context = $('<p/>').text('Uploading...').replaceAll($(this));
                    data.submit();
                });
        },
        done: function (e, data) {
            data.context.text('Upload finished.');
        }
    });
    if($('input.chooseimages').length>0){
    	var uploadHtml='<div class="uploadfilepopup"></div>';
    	
    	$('<div class="uploadfilepopupb"><div class="uploadfilepopup"><h3>图片管理</h3><div><ul class="nav nav-tabs" role="tablist">'+
    			'<li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">Home</a></li>'+
    			    '<li role="presentation"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">Profile</a></li></ul>'+
    			  '<div class="tab-content">'+
    			    '<div role="tabpanel" class="tab-pane active" id="home">ttttt</div>'+
    			    '<div role="tabpanel" class="tab-pane" id="profile">iiiiii</div></div></div></div></div>').appendTo(document.body);	
    	
    }
});