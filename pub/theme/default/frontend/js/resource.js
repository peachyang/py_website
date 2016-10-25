(function (factory) {
    if (typeof define === "function" && define.amd) {
        define(["jquery", "modal", "collapse", "jquery.fileupload", "jquery.ui.selectable"], factory);
    } else if (typeof exports === "object") {
        factory(require(["jquery", "modal", "collapse", "jquery.fileupload", "jquery.ui.selectable"]));
    } else {
        factory(jQuery);
    }
}(function ($) {
    $(function () {
        "use strict";
        var widgetUpload = {
            target: null,
            init: function () {
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
                    }).on('resource.selected', '.inline-box .btn', function () {
                        var a = $('#resource-list .active img');
                        $(this).children('img').attr('src', $(a).attr('src'));
                        $(this).siblings('[type=hidden]').val($(a).data('id'));
                    });
                }
                $('#upload-list').on('click', '.upload-remove', function () {
                    $(this).parent('.item').remove();
                    return false;
                });
                $('#upload-form').submit(function () {
                    var anchors = $("#upload-list .item");
                    if (anchors.length) {
                        $(anchors).trigger('upload');
                    }
                    return false;
                });
                $('#resource-modal .nav-tabs [href="#select-resource"]').on('shown.bs.tab', function () {
                    widgetUpload.loadImagesList();
                });
                $('#resource-modal').on({
                    'show.bs.modal': function (e) {
                        widgetUpload.target = e.relatedTarget;
                        widgetUpload.loadImagesList();
                        if ($(widgetUpload.target).data('upload')) {
                            $('#resource-modal .nav-tabs [href="#upload-resource"]').tab('show');
                        }
                    },
                    'hide.bs.modal': function () {
                        var a = $('#resource-list .active');
                        if (a.length) {
                            $(widgetUpload.target).trigger('resource.selected');
                        }
                        if ($(widgetUpload.target).is('[data-reload]')) {
                            location.reload();
                        }
                    }
                });
                $("#upload-element").fileupload({
                    dataType: 'json',
                    add: function (e, data) {
                        data.context = $('<div class="item"></div>').html('<span class="upload-name">' +
                                data['files'][0].name +
                                '</span><span class="upload-note"><span class="fa fa-pause"></span></span><a href="javascript:void(0);" class="upload-remove"><span class="fa fa-remove"></span></a>').on('upload', function () {
                            var o = this;
                            $(o).find('.upload-note .fa').removeClass('fa-pause').addClass('fa-spinner fa-spin');
                            data.submit().success(function (result, textStatus, jqXHR) {
                                if (result.error == 0) {
                                    addMessages(result.message);
                                    if ($(o).siblings('.item').length === 0) {
                                        $('#resource-modal .nav-tabs [href="#select-resource"]').tab('show');
                                    }
                                    $(o).remove();
                                } else {
                                    alert(result.error);
                                }
                            });
                        }).appendTo($("#upload-list"));
                    }
                });
                if (!GLOBAL.TIMEOUT) {
                    GLOBAL.TIMEOUT = {};
                }
                $('.resource-explorer .nav').on('click', 'a[data-toggle=collapse]', function () {
                    if (!$(this).is('.active')) {
                        var flag = $(this).siblings('ul').find('a.active').length;
                        $('.resource-explorer .nav a.active').removeClass('active');
                        $(this).addClass('active');
                        $('.resource-explorer header .title .folder-name').text($(this).text());
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
                }).on('click', '.rename', function () {
                    $('.resource-explorer #resource-list .item.selected .filename').attr(function () {
                        return {
                            contenteditable: 'true',
                            'data-old-name': $(this).text()
                        };
                    }).focus();
                }).on('click', '.delete', function () {
                    var ids = '';
                    $('.resource-explorer #resource-list .item.selected').each(function () {
                        ids += 'id[]=' + $(this).data('id') + '&';
                    });
                    if (ids !== '') {
                        console.log(GLOBAL.BASE_URL + (GLOBAL.ADMIN_PATH ? GLOBAL.ADMIN_PATH + '/resource_resource/delete/' : 'retailer/resource/delete/'));
                        $.ajax(GLOBAL.BASE_URL + (GLOBAL.ADMIN_PATH ? GLOBAL.ADMIN_PATH + '/resource_resource/delete/' : 'retailer/resource/delete/'), {
                            type: 'delete',
                            data: ids + 'csrf=' + $('.resource-explorer .toolbar').data('csrf'),
                            success: function () {
                                widgetUpload.loadFileList();
                            }
                        });
                    }
                }).on('change', '[name=desc]', function () {
                    widgetUpload.loadFileList();
                }).on('click', '.grid', function () {
                    $('.resource-explorer #resource-list.list').removeClass('list');
                    if (window.localStorage) {
                        window.localStorage.listMode = 0;
                    }
                    return false;
                }).on('click', '.list', function () {
                    $('.resource-explorer #resource-list').addClass('list');
                    if (window.localStorage) {
                        window.localStorage.listMode = 1;
                    }
                    return false;
                });
                if (window.localStorage && window.localStorage.listMode) {
                    $('.resource-explorer #resource-list').addClass('list');
                }
                $('.resource-explorer #resource-list').on('click', '.item', function () {
                    if ($(this).is('.selected')) {
                        if (!$(this).find('[contenteditable]').length) {
                            $(this).removeClass('selected');
                        }
                    } else {
                        $('.resource-explorer #resource-list [contenteditable]').trigger('blur');
                        $(this).siblings('.selected').removeClass('selected');
                        $(this).addClass('selected');
                        $('#resource-list').trigger('selected.seahinet');
                    }
                }).on('blur', '.item.selected .filename[contenteditable]', function () {
                    $(this).removeAttr('contenteditable');
                    var name = $(this).text();
                    if (name !== $(this).data('old-name')) {
                        $.post(GLOBAL.BASE_URL + (GLOBAL.ADMIN_PATH ? GLOBAL.ADMIN_PATH + '/resource_resource/rename/' : 'retailer/resource/rename/'),
                                'id=' + $(this).parents('.item[data-id]').first().data('id') + '&name=' + $(this).text() + '&csrf=' + $('.resource-explorer .toolbar').data('csrf'));
                        $(this).removeClass('edited');
                    }
                }).on('keypress', '.item.selected .filename[contenteditable]', function (e) {
                    if (e.keyCode == 13) {
                        $(this).trigger('blur');
                        return false;
                    }
                }).on('submit', '.filters form', function () {
                    widgetUpload.loadFileList();
                    return false;
                }).on('click', '.pager a', function () {
                    $('.resource-explorer .pager .active').removeClass('active');
                    $(this).parent('li').addClass('active');
                    widgetUpload.loadFileList();
                    return false;
                }).on('dblclick', '.item.folder', function () {
                    $('.resource-explorer .nav a[data-toggle=collapse][href="#resource-category-' + $(this).data('id') + '"]').trigger('click');
                });
                $('.resource-explorer #resource-list:not(.list)').on('mouseenter', '.item', function () {
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
                }).on('mouseleave', '.item', function () {
                    var k = 'resource-item-' + $(this).prevAll('.item').length;
                    if (GLOBAL.TIMEOUT[k]) {
                        $(this).off('mousemove');
                        window.clearTimeout(GLOBAL.TIMEOUT[k]);
                    } else {
                        $(this).children('.info').fadeOut();
                    }
                });
                $('.resource-explorer .nav a.active+.collapse').collapse('show');
                $('.resource-explorer header .title .folder-name').text($('.resource-explorer .nav a[data-toggle=collapse].active').text());
                widgetUpload.loadFileList();
            },
            loadImagesList: function (data) {
                var p = $(this.target).data('param');
                if (data) {
                    p = p ? (p + '&' + data) : data;
                }
                $("#resource-list").load(GLOBAL.BASE_URL + 'retailer/product/popup/', p);
            },
            loadFileList: function () {
                if (!GLOBAL.AJAX) {
                    GLOBAL.AJAX = {};
                } else if (GLOBAL.AJAX['load.resource']) {
                    GLOBAL.AJAX['load.resource'].abort();
                }
                $('#resource-list').addClass('loading');
                var page = $('.resource-explorer .pager .active');
                var url = GLOBAL.BASE_URL + (GLOBAL.ADMIN_PATH ? GLOBAL.ADMIN_PATH + '/resource_resource/' : 'retailer/resource/') +
                        '?' + $('.resource-explorer .filters form').serialize() +
                        '&category_id=' + $('.resource-explorer .nav a.active').data('id') +
                        '&desc=' + $('.resource-explorer .content #desc').val() +
                        '&page=' + (page.length ? parseInt($('.resource-explorer .pager .active').data('page')) : 1);
                GLOBAL.AJAX['load.resource'] = $.get(url).success(function (r) {
                    GLOBAL.AJAX['load.resource'] = null;
                    $('#resource-list').html(r);
                    $('#resource-list').removeClass('loading');
                    window.history.pushState({}, '', url);
                });
            }
        };
        widgetUpload.init();
        $(window).on('popstate', function () {
            if (GLOBAL.AJAX['load.resource']) {
                GLOBAL.AJAX['load.resource'].abort();
            }
            $('#resource-list').addClass('loading');
            GLOBAL.AJAX['load.resource'] = $.get(location.href).success(function (r) {
                GLOBAL.AJAX['load.resource'] = null;
                $('#resource-list').html(r);
                $('#resource-list').removeClass('loading');
            });
        });
    });
}));