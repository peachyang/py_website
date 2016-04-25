window.onload = function()
{
	if($('html').attr('lang')!='null'&&$('html').attr('lang')!=''){
		var cklanguage=$('html').attr('lang');
	}else{
		var cklanguage='zh-cn';
	}
	// custombar or fullbar toolbar 
	$('textarea.htmleditor').each(function(){
		if($(this).hasClass('fullbar')){
			$(this).ckeditor(
					function(){
						
					},
					{height:'500',
					 width:'auto',
					 language:cklanguage,
					 toolbarGroups: [
					{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
					{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
					{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
					{ name: 'forms', groups: [ 'forms' ] },
					'/',
					{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
					{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
					{ name: 'links', groups: [ 'links' ] },
					{ name: 'insert', groups: [ 'insert' ] },
					'/',
					{ name: 'styles', groups: [ 'styles' ] },
					{ name: 'colors', groups: [ 'colors' ] },
					{ name: 'tools', groups: [ 'tools' ] },
					{ name: 'others', groups: [ 'others' ] },
					{ name: 'about', groups: [ 'about' ] }
					],
					removeButtons:'About'
					}
			);
		}else{
			$(this).ckeditor(
					function(){
						
					},
					{height:'500',
					 width:'auto',
					 language:cklanguage,
					 toolbarGroups: [
					 				{"name":"basicstyles","groups":["basicstyles"]},
					 				{"name":"links","groups":["links"]},
					 				{"name":"paragraph","groups":["list","blocks"]},
					 				{"name":"document","groups":["mode"]},
					 				{"name":"insert","groups":["insert"]},
					 				{"name":"styles","groups":["styles"]},
					 			],
					 removeButtons: 'Underline,Strike,Subscript,Superscript,Anchor,Styles,Specialchar,about'
					}
			);
		}
	});
};


