/* global path */

$.cssHooks.backgroundColor = {
    get: function (elem) {
        if (elem.currentStyle)
            var  bg = elem.currentStyle["background-color"];
        else if (window.getComputedStyle)
            var  bg = document.defaultView.getComputedStyle(elem,
                    null).getPropertyValue("background-color");
        if (bg.search("rgb") == -1)
            return bg;
        else {
            bg = bg.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
            function hex(x) {
                return ("0" + parseInt(x).toString(16)).slice(-2);
            }

        }
    }
}

'use strict';
//Make sure jQuery has been loaded before app.js
if (typeof jQuery === "undefined") {
    throw new Error("HtmlEditor requires jQuery");
}


$(function () {
    //Set up the object
    _init();
});

/* ----------------------------------
 * ----------------------------------
 * All HTMLeditor functions are implemented below.
 */


function _init() {
    $(window).resize(function () {
        $("body").css("min-height", $(window).height() - 90);
        $(".htmlpage").css("min-height", $(window).height() - 160)
    });

    tinymce.init({
        menubar: false,
        force_p_newlines: true,
        extended_valid_elements : "*[*]",
        valid_elements: "*[*]",
        selector: "#html5editor",
        plugins: [
            "advlist autolink lists link charmap anchor",
            "visualblocks code ",
            "insertdatetime  table contextmenu paste textcolor colorpicker"
        ],
        toolbar: "styleselect | bold italic |  forecolor backcolor |alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link code",
    });

    tinymce.init({
        menubar: false,
        force_br_newlines: false,
        force_p_newlines: false,
        forced_root_block: '',
        extended_valid_elements : "*[*]",
    valid_elements: "*[*]",
        selector: "#html5editorLite",
        plugins: [
        ],
        toolbar: "forecolor backcolor | alignleft aligncenter alignright alignjustify code",
    });

    $("body").css("min-height", $(window).height() - 90);
    $(".htmlpage").css("min-height", $(window).height() - 160);
    $(".htmlpage, .htmlpage .column").sortable({connectWith: ".column", opacity: .35, handle: ".drag"});
    $(".sidebar-nav .lyrow").draggable({
        connectToSortable: ".htmlpage",
        helper: "clone",
        handle: ".drag",
        drag: function (e, t) {
            t.helper.width(400);
        },
        stop: function (e, t) {
            $(".htmlpage .column").sortable({opacity: .35, connectWith: ".column"});
           
        }
    });

    $(".sidebar-nav .box").draggable({
        connectToSortable: ".column",
        helper: "clone",
        handle: ".preview",
        start:function (e,t){
        	var obj = t.helper;
        	var dataType = $(obj).attr('data-type');
        	var showType = $(obj).attr('show-type');
        	//console.log($("#hot_product").html());
        	if(showType=="1")
        	{
        		var htmls = template(dataType);
        		
        		$(".box.box-element[data-type='"+dataType+"']").find(".view").html(htmls);
        		$(obj).find(".view").html('模块');
 			}
        	
        },
        drag: function (e, t) {
            t.helper.width(400);
            
        },
        stop: function (e, t) {
			//console.log(t.helper);
			if($(".htmlpage .lyrow").length<=0)
			alert("功能模块必须拖入表格内容区\n请先拖入表格内容区");
        }
    });

    $(document).on('click', 'a.clone', function (e) {
        e.preventDefault();
        var  _s = $(this);

        var  _row = _s.parent().clone();
        _row.hide();
        _row.insertAfter(_s.parent());
        _row.slideDown();

    });

    $(document).on('click', 'a.settings', function (e) {
        e.preventDefault();
        var  _s = $(this);

        var  part_id = _s.parent().parent().assignId();

        var  part = _s.parent().parent();
        var  column = _s.parent().parent().parent('.column');
        var  row = _s.parent().parent().parent().parent('.row');

        prepareEditor(part, row, column);
    });

    $('a.btnpropa').on('click', function () {
        var  rel = $(this).attr('rel');
        $('#buttonContainer a.btn').removeClass('btn-default')
                .removeClass('btn-success')
                .removeClass('btn-info')
                .removeClass('btn-danger')
                .removeClass('btn-info')
                .removeClass('btn-primary')
                .removeClass('btn-link')
                .addClass(rel);
    });
    
    $('a.btnpropb').on('click', function () {
        var  rel = $(this).attr('rel');
        $('#buttonContainer a.btn').removeClass('btn-lg')
                .removeClass('btn-md')
                .removeClass('btn-sm')
                .removeClass('btn-xs')
                .addClass(rel);
    });

    $('a.btnprop').on('click', function () {
        var  rel = $(this).attr('rel');
        $('#buttonContainer a.btn').toggleClass(rel);
    });

    $('#preferences').on('hidden.bs.modal', function () {
        $('#ht').hide();
        $('#image').hide();
        $('#text').hide();
        $('#code').hide();
        $('#buttons').hide();
    });

    $("#save").click(function (e) {
        downloadLayoutSrc();
        saveData();
    });
    



    $("#clear").click(function (e) {
        e.preventDefault();
        clearDemo()
    });
    
    $("#devpreview").click(function () {
        $("body").removeClass("edit sourcepreview");
        $("body").addClass("devpreview");
        removeMenuClasses();
        $(this).addClass("active");
        return false
    });


    $("#edit").click(function () {
        $('#add').hide();
        $("body").removeClass("devpreview sourcepreview");
        $("body").removeClass("tablet mobile");
        $("body").addClass("edit");
        removeMenuClasses();
        $(this).addClass("active");
        $('.htmlpage .column').css('padding','39px 19px 24px');
        $(".htmlpage .box .view").css('padding','7px');
        return false
    });


    $('#gallery').click(function(){
        $('#thepref').slideUp();
        $('#mediagallery').slideDown();
    });


    $("#sourcepreview").click(function () {
    	$(".edit.container").removeClass('edit')
        //$('#pc').addClass('active');
        //$('#add').show();
        $("body").removeClass("edit");
        $("body").addClass("devpreview sourcepreview");
        removeMenuClasses();
        $(this).addClass("active");
        $(".htmlpage .box .view").css('padding','0px');
        $('.htmlpage .column').css('padding','11px');
        return false
    });



    $('#pc').click(function () {
        $("body").removeClass("tablet mobile");
        $('#app button').removeClass('active');
        $(this).addClass('active');
    });


    $('#mobile').click(function () {
        $("body").removeClass("tablet");
        $('#app button').removeClass('active');
        $(this).addClass('active');
        $("body").addClass("mobile");
    });


    $('#tablet').click(function () {
        $("body").removeClass("mobile");
        $('#app button').removeClass('active');
        $(this).addClass('active');
        $("body").addClass("tablet");
    });

    $(".nav-header").click(function () {
        $(".sidebar-nav .boxes, .sidebar-nav .rows").hide();
        $(this).next().slideDown()
    });

    removeElm();
    gridSystemGenerator();

}

function loadRowSettings(row) {
    //RowSettings
    // paddings
    $('#tabRow input[data-ref="padding-top"]').val(row.css('padding-top'));
    $('#tabRow input[data-ref="padding-left"]').val(row.css('padding-left'));
    $('#tabRow input[data-ref="padding-right"]').val(row.css('padding-right'));
    $('#tabRow input[data-ref="padding-bottom"]').val(row.css('padding-bottom'));
    // margin
    $('#tabRow input[data-ref="margin-top"]').val(row.css('margin-top'));
    $('#tabRow input[data-ref="margin-left"]').val(row.css('margin-left'));
    $('#tabRow input[data-ref="margin-right"]').val(row.css('margin-right'));
    $('#tabRow input[data-ref="margin-bottom"]').val(row.css('margin-bottom'));
    // backgroundColor
    $('#rowbg').val(row.css('background-color'));
    // image
    $('#rowbgimage').val(row.css('background-image').replace(/^url\(['"]?/,'').replace(/['"]?\)$/,''));
    // css class
    $('#rowcss').val(row.attr('class'));
}

function saveRowSettings(row) {
    //RowSettings
    //padding
    row.css('padding-top', $('#tabRow input[data-ref="padding-top"]').val());
    row.css('padding-left', $('#tabRow input[data-ref="padding-left"]').val());
    row.css('padding-right', $('#tabRow input[data-ref="padding-right"]').val());
    row.css('padding-bottom', $('#tabRow input[data-ref="padding-bottom"]').val());
    // margin
    row.css('margin-top', $('#tabRow input[data-ref="margin-top"]').val());
    row.css('margin-left', $('#tabRow input[data-ref="margin-left"]').val());
    row.css('margin-right', $('#tabRow input[data-ref="margin-right"]').val());
    row.css('margin-bottom', $('#tabRow input[data-ref="margin-bottom"]').val());
    // backgroundColor
    row.css('background-color', $('#rowbg').val());
    // image
    if($("#rowbgimage").val()!="none")
    row.css('background-image',  'url("'+$("#rowbgimage").val()+'")');
    // css class
    row.removeClass();
    row.addClass($('#rowcss').val());
    //row.attr('css', $('#rowcss').val());
}

function loadColumnSettings(column) {
    // paddings
    $('#tabCol input[data-ref="padding-top"]').val(column.css('padding-top'));
    $('#tabCol input[data-ref="padding-left"]').val(column.css('padding-left'));
    $('#tabCol input[data-ref="padding-right"]').val(column.css('padding-right'));
    $('#tabCol input[data-ref="padding-bottom"]').val(column.css('padding-bottom'));
    // margin
    $('#tabCol input[data-ref="margin-top"]').val(column.css('margin-top'));
    $('#tabCol input[data-ref="margin-left"]').val(column.css('margin-left'));
    $('#tabCol input[data-ref="margin-right"]').val(column.css('margin-right'));
    $('#tabCol input[data-ref="margin-bottom"]').val(column.css('margin-bottom'));
    // backgroundColor
    $('#colbg').val(column.css('background-color'));
    // css class
    $('#colcss').val(column.attr('class'));
}
function saveColumnSettings(column) {
    //CellSettings
    //padding
    column.css('padding-top', $('#tabCol input[data-ref="padding-top"]').val());
    column.css('padding-left', $('#tabCol input[data-ref="padding-left"]').val());
    column.css('padding-right', $('#tabCol input[data-ref="padding-right"]').val());
    column.css('padding-bottom', $('#tabCol input[data-ref="padding-bottom"]').val());
    // margin
    column.css('margin-top', $('#tabCol input[data-ref="margin-top"]').val());
    column.css('margin-left', $('#tabCol input[data-ref="margin-left"]').val());
    column.css('margin-right', $('#tabCol input[data-ref="margin-right"]').val());
    column.css('margin-bottom', $('#tabCol input[data-ref="margin-bottom"]').val());
    // backgroundColor
    column.css('background-color', $('#colbg').val());
    // css class
    column.attr('class', $('#colcss').val());
}

function prepareEditor(part, row, column) {
    var  clone = part.clone();
    var  confirm = $('#applyChanges');
    $('#preferencesTitle').html(part.data('type'));

    $('.column .box').removeClass('active');
    part.addClass('active');
    confirm.unbind('click');

    var  clonedPart = clone.find('div.view').html();
    var  type = part.data('type');
    var  panel = $('#Settings');

    loadRowSettings(row);
    loadColumnSettings(column);

    var  o = part.find('div.view').children();
    var  oid = o.assignId();
    $('#id').val(oid);
    $('#class').val(o.parent().parent().css('class'));
    $('#class').parent().show();
    $('#id').parent().show();
    switch (type) {
        
        case 'header':
            var  editor = tinyMCE.get('html5editorLite');
            editor.setContent(clonedPart);
            $('#ht').show();

            confirm.bind('click', function (e) {
                e.preventDefault();
                saveRowSettings(row);
                saveColumnSettings(column);
                o.html(editor.getContent());
                o.attr('id', $('#id').val());
                o.attr('class', $('#class').val());
            });
            break;
        
        case 'paragraph':
            var  editor = tinyMCE.get('html5editor');
            editor.setContent(clonedPart);
            $('#text').show();

            var  o = part.find('div.view');
            confirm.bind('click', function (e) {
                e.preventDefault();
                saveRowSettings(row);
                saveColumnSettings(column);
                o.html(editor.getContent());
                o.attr('id', $('#id').val());
            });
            break;

        case 'image':
            var  img = part.find('img');
            $('#imgContent').html(img.clone().attr('width', '200'));
            $('#img-url').val(img.attr('src'));
            $('#img-width').val(img.innerWidth());
            $('#img-height').val(img.innerHeight());
            $('#img-title').val(img.attr('title'));
            $('#class').val(img.attr('class'));
            $('#img-rel').val(img.attr('rel'));
            $('#img-title').val(img.attr('title'));
            $('#image').show();

            confirm.bind('click', function (e) {
                e.preventDefault();
                saveRowSettings(row);
                saveColumnSettings(column);
                img.attr('title', $('#img-title').val());
                img.attr('src', $('#img-url').val());
                img.css('width', $('#img-width').val());
                img.css('height', $('#img-height').val());
                img.attr('class', $('#class').val());
                img.attr('rel', $('#img-rel').val());
                o.attr('id', $('#id').val());
                o.removeClass();
                o.addClass($('#class').val());
            });
            break;

        case 'code':
            $('#class').parent().hide();
            $('#id').parent().hide();
            var  txt = $('#code');
            $('#codeeditor').remove();
            txt.append('<textarea id="codeeditor" style="min-height:150px;width:100%; display:block;">'+style_html(part.find('div.view').html())+'</textarea>');
            txt.show();

            confirm.bind('click', function (e) {
                e.preventDefault();
                saveRowSettings(row);
                saveColumnSettings(column);
                part.find('div.view').html($('#codeeditor').val());
            });
            break;
            
        case 'button' :
            var  btn = part.find('.view > a.btn');
            var  btn_id = btn.assignId();
            var  clone = btn.clone();
            $('#buttonContainer').html(clone);
            $('#buttonId').val(btn_id);
            $('#buttonLabel').val(btn.text());
            $('#buttonHref').val(btn.attr('href'));
            $('#buttons').show();

            confirm.bind('click', function (e) {
                e.preventDefault();
                saveRowSettings(row);
                saveColumnSettings(column);
                btn.text($('#buttonLabel').val());
                btn.attr('href', $('#buttonHref').val());
                btn.css('background', $('#colbtn').val());
                btn.css('width', $('#custombtnwidth').val());
                btn.css('height', $('#custombtnheight').val());
                btn.css('font-size', $('#custombtnfont').val());
                btn.css('padding-top', $('#custombtnpaddingtop').val());
                btn.css('color', $('#colbtncol').val());
                o.attr('id', $('#id').val());
                btn.attr('class', $('#buttonContainer > a.btn').attr('class'));
                o.attr('class', $('#class').val());
            });
            break;
    }
    $('#preferences').modal('show').draggable();
}

function handleSaveLayout() {
    var  e = $(".htmlpage").html();
    if (e != window.htmlpageHtml) {
        saveLayout();
        window.htmlpageHtml = e
    }
}

function gridSystemGenerator() {
    $(".lyrow .preview input").bind("keyup", function () {
        var  e = 0;
        var  t = "";
        var  n = false;
        var  r = $(this).val().split(" ", 12);
        $.each(r, function (r, i) {
            if (!n) {
                if (parseInt(i) <= 0)
                    n = true;
                e = e + parseInt(i);
                t += '<div class="col-md-' + i + ' column"></div>'
            }
        });
        if (e == 12 && !n) {
            $(this).parent().next().children().html(t);
            $(this).parent().find('.drag').show()
        } else {
            $(this).parent().find('.drag').hide()
        }
    })
}

function removeElm() {
    $(".htmlpage").delegate(".remove", "click", function (e) {
        var  b = $(this).parent().css('border');
        $(this).parent().css('border', '2px solid red');

        if (confirm("确定要删除吗?")) {
            e.preventDefault();
            $(this).parent().remove();

            if (!$(".htmlpage .lyrow").length > 0) {
                clearDemo();
            }
        } else {
            $(this).parent().css('border', b);
        }
    })
}

function clearDemo() {
    $(".htmlpage").empty()
}

function removeMenuClasses() {
    $("#menu-htmleditor li button").removeClass("active")
}

function changeStructure(e, t) {
    $("#download-layout ." + e).removeClass(e).addClass(t)
}

function cleanHtml(e) {
    $(e).parent().append($(e).children().html());
}

function cleanRow(row) {
    row.children('.remove , .drag, .preview').remove();
    row.find('div.ui-sortable').removeClass('ui-sortable');

    row.children('.view').find('br').remove();

    row.children('.view').children('.row').children('.column').each(function () {
        var  col = $(this);

        col.removeClass('column');
        col.children('.lyrow').each(function () {
            cleanRow($(this));
        });
        col.children('.box-element').each(function () {
            var  element = $(this);
            element.children('.remove , .drag, .configuration, .preview').remove();
            element.parent().append(element.children('.view').html());
            element.remove();
        });
    });
    row.parent().append(row.children('.view').html());
    row.remove();
}

function downloadLayoutSrc() {
	if($("#edit").hasClass("active"))
	{
		$('.htmlpage .column').css('padding','11px');
		var htmls = $(".htmlpage").html();
		$('.htmlpage .column').css('padding','39px 19px 24px');
	}
	else{
		var htmls = $(".htmlpage").html();
	}
	
    $("#download-layout").children().html(htmls);

    $("#download-layout").children('.container').each(function (i) {
        var  container = $(this);
        container.children('.lyrow').each(function (i) {
            var  row = $(this);
            cleanRow(row);
        });
    });
    //$('textarea#model').val($(".htmlpage").html());
    //$('textarea#src').val(style_html($("#download-layout").html()));
    //$('#download').modal('show');

}

$('#srcSave').click(function () {
    ///   $.post(path + '/save.php', {html: style_html($("#download-layout").html())}, function (data) {       }, 'html');
});


function getIndent(level) {
    var  result = '',
            i = level * 4;
    if (level < 0) {
        throw "Level is below 0";
    }
    while (i--) {
        result += ' ';
    }
    return result;
}

function style_html(html) {
    html = html.trim();
    var  result = '',
            indentLevel = 0,
            tokens = html.split(/</);
    for (var  i = 0, l = tokens.length; i < l; i++) {
        var  parts = tokens[i].split(/>/);
        if (parts.length === 2) {
            if (tokens[i][0] === '/') {
                indentLevel--;
            }
            result += getIndent(indentLevel);
            if (tokens[i][0] !== '/') {
                indentLevel++;
            }

            if (i > 0) {
                result += '<';
            }

            result += parts[0].trim() + ">\n";
            if (parts[1].trim() !== '') {
                result += getIndent(indentLevel) + parts[1].trim().replace(/\s+/g, ' ') + "\n";
            }

            if (parts[0].match(/^(img|hr|br)/)) {
                indentLevel--;
            }
        } else {
            result += getIndent(indentLevel) + parts[0] + "\n";
        }
    }
    return result;
}

function s4() {
    return Math.floor((1 + Math.random()) * 0x10000)
            .toString(16)
            .substring(1);
}

function changeTag(){
	$("#save_html .function-tag").each(function(){
		$(this).html("{{"+$(this).attr('data-tag')+'}}');
	});
	//console.log($("#save_html").html());
	return $("#save_html").html();
}

(function ($) {
    $.fn.assignId = function () {
        var  _self = $(this);
        var  id = _self.attr('id');
        if (typeof id === typeof undefined || id === false) {
            id = s4() + '-' + s4() + '-' + s4() + '-' + s4();
            _self.attr('id', id);
        }
        return id;
    };

})(jQuery);
