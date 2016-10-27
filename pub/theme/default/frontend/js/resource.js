(function (factory) {
    if (typeof define === "function" && define.amd) {
        define(["jquery", "modal", "collapse", "jquery.fileupload", "jquery.ui.droppable", "jquery.ui.draggable"], factory);
    } else if (typeof exports === "object") {
        factory(require(["jquery", "modal", "collapse", "jquery.fileupload", "jquery.ui.droppable", "jquery.ui.draggable"]));
    } else {
        factory(jQuery);
    }
}(function ($) {
    $(function () {
        "use strict";
        var widgetUpload = {
            target: null,
            init: function () {
                if (!GLOBAL.TIMEOUT) {
                    GLOBAL.TIMEOUT = {};
                }
                if ($('.widget-upload').length) {
                    $('.widget-upload').on('click', '.delete', function () {
                        var p = $(this).parents('.inline-box');
                        if ($(p).siblings('.inline-box').length === 0) {
                            $(p).children('input,select,textarea').not('[type=radio],[type=checkbox]').val('');
                            $(p).find('img').attr('src', GLOBAL.PUB_URL + 'backend/images/placeholder.png');
                        } else {
                            $(p).remove();
                        }
                        return false;
                    }).on('click', '.add', function () {
                        var o = $('.widget-upload .inline-box').last();
                        var odiv = $('<' + o[0].tagName + ' class="inline-box"></' + o[0].tagName + '>');
                        $(odiv).html($(o).html());
                        $(odiv).children('input').val('');
                        $(odiv).find('img').attr('src', GLOBAL.PUB_URL + 'backend/images/placeholder.png');
                        $(o).after(odiv);
                        return false;
                    }).on('resource.selected', '.inline-box .btn', function (e, a) {
                        $(this).find('img').attr('src', $(a).find('img').attr('src'));
                        $(this).siblings('[type=hidden]').val($(a).data('id'));
                    });
                }
                $('#modal-upload').on({
                    'show.bs.modal': function (e) {
                        $('#modal-upload .nav a.active').removeClass('active');
                        $('#modal-upload .nav a[data-id=' + $(e.relatedTarget).data('category') + ']').addClass('active');
                        $('#modal-upload .nav a.active+.collapse').collapse('show');
                        $('#modal-upload #upload-form').trigger('reset');
                        $('#modal-upload #upload-list').html('');
                    },
                    'hide.bs.modal': function () {
                        widgetUpload.loadFileList();
                    }
                });
                $('#modal-insert').on({
                    'show.bs.modal': function (e) {
                        widgetUpload.target = e.relatedTarget;
                    },
                    'hide.bs.modal': function () {
                        var a = $('.resource-list .selected:not(.folder)', this);
                        if (a.length) {
                            $(widgetUpload.target).trigger('resource.selected', a);
                        }
                        widgetUpload.loadFileList();
                    }
                });
                var unit = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB', 'BB', 'NB', 'DB'];
                var calcSize = function (s, u) {
                    return s > 1024 && unit[u + 1] ?
                            calcSize(s / 1024, u + 1) :
                            s.toFixed(2) + unit[u];
                };
                $('#modal-upload').on('click', '.upload-remove', function () {
                    $(this).parents('tr.item').remove();
                });
                $("#modal-upload #upload-element").fileupload({
                    dataType: 'json',
                    add: function (e, data) {
                        data.context = $('<tr class="item"></tr>').html('<td>' +
                                data['files'][0].name + '</td><td>' + calcSize(data['files'][0].size, 0) +
                                '</td><td class="upload-note"><span class="fa fa-pause"></span></td><td><a href="javascript:void(0);" class="upload-remove"><span class="fa fa-remove"></span></a></td>').on('upload', function () {
                            var o = this;
                            $(o).find('.upload-note .fa').removeClass('fa-pause').addClass('fa-spinner fa-spin');
                            data.submit().success(function (result) {
                                if (result.error) {
                                    $(o).find('.upload-note .fa').removeClass('fa-spinner fa-spin').addClass('fa-exclamation-triangle');
                                    alert(result.error);
                                } else {
                                    $(o).find('.upload-note .fa').removeClass('fa-spinner fa-spin').addClass('fa-check');
                                }
                            });
                        }).appendTo($("#modal-upload #upload-list"));
                    }
                });
                $('#modal-upload #upload-form').submit(function () {
                    var anchors = $("#modal-upload #upload-list .item");
                    if (anchors.length) {
                        $(anchors).trigger('upload');
                    }
                    return false;
                });
                $('#modal-upload .nav').on('click', 'a[data-toggle=collapse]', function () {
                    if (!$(this).is('.active')) {
                        var flag = $(this).siblings('ul').find('a.active').length;
                        $('#modal-upload .nav a.active').removeClass('active');
                        $(this).addClass('active');
                        $('#modal-upload #upload-form [name=category_id]').val($(this).data('id'));
                        if (flag) {
                            return false;
                        }
                    }
                }).on('show.bs.collapse', '.collapse', function () {
                    $(this).parents('.collapse').first().collapse('show');
                });
                $('.resource-explorer .nav').on('click', 'a[data-toggle=collapse]', function () {
                    if (!$(this).is('.active')) {
                        var flag = $(this).siblings('ul').find('a.active').length;
                        $('.resource-explorer .nav a.active').removeClass('active');
                        $(this).addClass('active');
                        $('.resource-explorer header .title .folder-name').text($(this).text());
                        $('.resource-explorer header .buttons-set .btn').attr('data-category', $(this).data('id'));
                        widgetUpload.loadFileList();
                        if (flag) {
                            return false;
                        }
                    }
                }).on('show.bs.collapse', '.collapse', function () {
                    $(this).parents('.collapse').first().collapse('show');
                });
                $('.resource-explorer .filters form').on('submit', function () {
                    widgetUpload.loadFileList();
                    return false;
                });
                $('.resource-explorer .toolbar').on('click', '.back', function () {
                    if ('pushState' in history) {
                        history.go(-1);
                    } else {
                        $('.resource-explorer .nav a.active').parents('ul.collapse').first().siblings('a').trigger('click');
                    }
                }).on('click', '.forward', function () {
                    if ('pushState' in history) {
                        history.go(1);
                    }
                }).on('click', '.folder', function () {
                    var p = $(this).parents('.resource-explorer');
                    $('.resource-list .item.selected', p).removeClass('selected');
                    var oli = document.createElement('li');
                    $(oli).attr({'class': 'item folder new selected', 'data-id': '0', 'data-old-name': ''}).html('<span class="fa fa-folder" aria-hidden="true"></span><span class="filename" contenteditable="true"></span>');
                    var l = $('.resource-list .item.folder', p);
                    if (l.length) {
                        $(l).last().after(oli);
                    } else {
                        $('.resource-list', p).prepend(oli);
                    }
                    $(oli).children('.filename').focus();
                }).on('click', '.rename', function () {
                    $('.resource-list .item.selected .filename', $(this).parents('.resource-explorer')).attr('contenteditable', 'true').attr('data-old-name', function () {
                        return $(this).text();
                    }).focus();
                }).on('click', '.delete', function () {
                    var ids = '';
                    $('.resource-list .item.selected', $(this).parents('.resource-explorer')).each(function () {
                        ids += ($(this).is('.folder') ? 'f[]=' : 'r[]=') + $(this).data('id') + '&';
                    });
                    if (ids !== '') {
                        console.log(GLOBAL.BASE_URL + (GLOBAL.ADMIN_PATH ? GLOBAL.ADMIN_PATH + '/resource_resource/delete/' : 'retailer/resource/delete/'));
                        $.ajax(GLOBAL.BASE_URL + (GLOBAL.ADMIN_PATH ? GLOBAL.ADMIN_PATH + '/resource_resource/delete/' : 'retailer/resource/delete/'), {
                            type: 'delete',
                            data: ids + 'csrf=' + $('.resource-explorer .toolbar').data('csrf'),
                            success: function () {
                                widgetUpload.loadNav();
                                widgetUpload.loadFileList();
                            }
                        });
                    }
                }).on('change', '[name=desc]', function () {
                    widgetUpload.loadFileList();
                }).on('click', '.grid', function () {
                    $('.resource-list.list', $(this).parents('.resource-explorer')).removeClass('list');
                    if (window.localStorage) {
                        window.localStorage.listMode = 0;
                    }
                    return false;
                }).on('click', '.list', function () {
                    $('.resource-list', $(this).parents('.resource-explorer')).addClass('list');
                    if (window.localStorage) {
                        window.localStorage.listMode = 1;
                    }
                    return false;
                }).on('mouseleave', 'menu.context', function () {
                    $(this).fadeOut('fast');
                }).on('click', 'menu.context a', function () {
                    $('.toolbar menu.context', $(this).parents('.resource-explorer')).fadeOut('fast');
                });
                if (window.localStorage && window.localStorage.listMode === '1') {
                    $('.resource-explorer .resource-list').addClass('list');
                }
                $('.resource-explorer .resource-list').on('click', '.item', function () {
                    if ($(this).is('.selected')) {
                        if (!$(this).find('[contenteditable]').length) {
                            $(this).removeClass('selected');
                        }
                    } else {
                        $('.resource-list [contenteditable]', $(this).parents('.resource-explorer')).trigger('blur');
                        $(this).siblings('.selected').removeClass('selected');
                        $(this).addClass('selected');
                        if (!$(this).is('.folder') && $(this).is('#modal-insert .resource-list .item')) {
                            $('#modal-insert').modal('hide');
                        }
                    }
                }).on('blur', '.item.selected .filename[contenteditable]', function () {
                    var p = $(this).parents('.item[data-id]').first();
                    $(this).removeAttr('contenteditable');
                    var name = $(this).text();
                    if (name && name !== $(this).data('old-name')) {
                        $.post(GLOBAL.BASE_URL + (GLOBAL.ADMIN_PATH ? GLOBAL.ADMIN_PATH + '/resource_resource/rename/' : 'retailer/resource/rename/'),
                                'id=' + p.data('id') + '&pid=' + $('.resource-explorer .nav a.active').data('id') + (p.is('.folder') ? '&type=f&name=' : '&type=r&name=') + $(this).text() + '&csrf=' + $('.resource-explorer .toolbar').data('csrf'), function () {
                            widgetUpload.loadNav();
                        });
                        if ($(p).is('.new')) {
                            $(p).removeClass('new');
                        }
                        $(this).removeClass('edited');
                    } else if ($(p).is('.new')) {
                        $(p).remove();
                    } else {
                        $(this).text($(this).data('old-name'));
                    }
                }).on('contextmenu', '.item', function (e) {
                    if (!$(this).is('.selected')) {
                        $(this).addClass('selected');
                    }
                    $('.toolbar menu.context').css({left: e.clientX - 10, top: e.clientY - 10}).show();
                    return false;
                }).on('keypress', '.item.selected .filename[contenteditable]', function (e) {
                    if (e.keyCode == 13) {
                        $(this).trigger('blur');
                        return false;
                    }
                }).on('submit', '.filters form', function () {
                    widgetUpload.loadFileList();
                    return false;
                }).on('click', '.pager a', function () {
                    $('.active', $(this).parents('.pager')).removeClass('active');
                    $(this).parent('li').addClass('active');
                    widgetUpload.loadFileList();
                    return false;
                }).on('dblclick', '.item.folder', function () {
                    $('.nav a[data-toggle=collapse][href="#resource-category-' + $(this).data('id') + '"]', $(this).parents('.resource-explorer')).trigger('click');
                });
                $('.resource-explorer').on('mouseenter', '.resource-list:not(.list) .item', function () {
                    var o = this;
                    var k = 'resource-item-' + $(o).prevAll('.item').length;
                    $(o).on('mousemove', function (e) {
                        $(this).children('.info').css({left: e.clientX + 20, top: e.clientY + 20});
                    });
                    GLOBAL.TIMEOUT[k] = window.setTimeout(function () {
                        GLOBAL.TIMEOUT[k] = null;
                        $(o).off('mousemove');
                        $(o).children('.info').fadeIn();
                    }, 2000);
                }).on('mouseleave', '.resource-list:not(.list) .item', function () {
                    var k = 'resource-item-' + $(this).prevAll('.item').length;
                    if (GLOBAL.TIMEOUT[k]) {
                        $(this).off('mousemove');
                        window.clearTimeout(GLOBAL.TIMEOUT[k]);
                    } else {
                        $(this).children('.info').fadeOut();
                    }
                });
                $('.resource-explorer header .title .folder-name').text($('.resource-explorer .nav a[data-toggle=collapse].active').text());
                widgetUpload.initNav();
                widgetUpload.loadFileList();
            },
            moveFile: function (t) {
                t.hide();
                $.post(GLOBAL.BASE_URL + (GLOBAL.ADMIN_PATH ? GLOBAL.ADMIN_PATH + '/resource_resource/move/' : 'retailer/resource/move/'),
                        'csrf=' + $('.resource-explorer .toolbar').data('csrf') + '&category_id=' + $(this).data('id') + (t.is('.folder') ? '&type=f&id=' : '&type=r&id=') + t.data('id'), function () {
                    widgetUpload.loadNav();
                    widgetUpload.loadFileList();
                });
            },
            initNav: function () {
                $('.resource-explorer .nav a').droppable({
                    accept: '.item',
                    drop: function (event, ui) {
                        widgetUpload.moveFile.call(this, ui.draggable);
                    }
                });
                $('.resource-explorer .nav a.active+.collapse').collapse('show');
            },
            loadNav: function () {
                if (!GLOBAL.AJAX) {
                    GLOBAL.AJAX = {};
                } else if (GLOBAL.AJAX['load.nav']) {
                    GLOBAL.AJAX['load.nav'].abort();
                }
                var url = GLOBAL.BASE_URL + (GLOBAL.ADMIN_PATH ? GLOBAL.ADMIN_PATH + '/resource_resource/nav/' : 'retailer/resource/nav/') +
                        '?category_id=' + $('.resource-explorer .nav a.active').data('id');
                GLOBAL.AJAX['load.nav'] = $.get(url).success(function (r) {
                    GLOBAL.AJAX['load.nav'] = null;
                    $('.resource-explorer .nav').html(r);
                    widgetUpload.initNav();
                });
            },
            loadFileList: function () {
                if (!GLOBAL.AJAX) {
                    GLOBAL.AJAX = {};
                } else if (GLOBAL.AJAX['load.resource']) {
                    GLOBAL.AJAX['load.resource'].abort();
                }
                $('.resource-list').addClass('loading');
                var page = $('.resource-explorer .pager .active');
                var url = GLOBAL.BASE_URL + (GLOBAL.ADMIN_PATH ? GLOBAL.ADMIN_PATH + '/resource_resource/' : 'retailer/resource/') +
                        '?' + $('.resource-explorer .filters form').serialize() +
                        '&category_id=' + $('.resource-explorer .nav a.active').data('id') +
                        '&desc=' + $('.resource-explorer .content [name=desc]').val() +
                        '&page=' + (page.length ? parseInt($('.resource-explorer .pager .active').data('page')) : 1);
                GLOBAL.AJAX['load.resource'] = $.get(url).success(function (r) {
                    GLOBAL.AJAX['load.resource'] = null;
                    $('.resource-list').html(r);
                    $('.resource-list').removeClass('loading');
                    $('.resource-explorer .resource-list .item').draggable({revert: "invalid"});
                    $('.resource-explorer .resource-list .item.folder').droppable({
                        accept: '.item',
                        drop: function (event, ui) {
                            widgetUpload.moveFile.call(this, ui.draggable);
                        }
                    });
                    if ($('.resource-list').length > $('#modal-insert .resource-list').length) {
                        window.history.pushState({}, '', url);
                    }
                });
            }
        };
        widgetUpload.init();
        $(window).on('popstate', function () {
            if (GLOBAL.AJAX['load.resource']) {
                GLOBAL.AJAX['load.resource'].abort();
            }
            $('.resource-list').addClass('loading');
            GLOBAL.AJAX['load.resource'] = $.get(location.href).success(function (r) {
                GLOBAL.AJAX['load.resource'] = null;
                $('.resource-list').html(r);
                $('.resource-list').removeClass('loading');
            });
        });
    });
}));
