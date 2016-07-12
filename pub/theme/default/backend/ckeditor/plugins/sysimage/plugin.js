(function () {
     
      CKEDITOR.plugins.add('sysimage', {
    	 icons:'sysimage',
         init: function (editor) {
             editor.addCommand('sysimage', CKEDITOR.plugins.sysimage.commands.sysimage);
        	 editor.ui.addButton('SysImage', {
                 label: 'SysImage',
                 icon: this.path+'images/sysimage.png',
                 command: 'sysimage'
                	 
             });
             CKEDITOR.dialog.add("sysimage", this.path + "dialogs/sysimage.js")
         }
         
     });
CKEDITOR.plugins.sysimage = {
    	        commands: {  
    	        	sysimage: {
    	                exec: function(editor) {
    	                $('#resource-modal').modal('show',editor);   	               
    	                $(editor).one('resource.selected',function(editor){
    	                	//console.log($('#resource-list .active img').attr('src'));
    	                	//alert('===================');
    	                	//editor.setData(html);
    	                	//console.log($(editor));
    	                	CKEDITOR.instances.content.insertHtml("<img src='"+$('#resource-list .active img').attr('src').replace(/resized\/\d+x\d*\//,'')+"'>");
    	                });
   
    	                }  
    	            }  
    	        }  
    	    };      
 })();



