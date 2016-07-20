(function (factory) {
    if (typeof define === "function" && define.amd) {
        define(["jquery", "modal", "tab", "jquery.fileupload", "jquery.ui.sortable"], factory);
    } else if (typeof exports === "object") {
        factory(require(["jquery", "modal", "tab", "jquery.fileupload", "jquery.ui.sortable"]));
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
                    }).sortable({
                        items: ".inline-box"
                    });
                }
                $('#resource-list').on('click', 'a.select', function () {
                    $(this).addClass('active');
                    $('#resource-modal').modal('hide');
                }).on('submit', '.filters form', function () {
                    widgetUpload.loadImagesList($(this).serialize());
                    return false;
                }).on('click', '.pager a', function () {
                    widgetUpload.loadImagesList($(this).attr('href').replace(/^[^\?]+\?/, ''));
                    return false;
                });
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
            },
            loadImagesList: function (data) {
                var p = $(this.target).data('param');
                if (data) {
                    p = p ? (p + '&' + data) : data;
                }
                $("#resource-list").load(GLOBAL.BASE_URL + GLOBAL.ADMIN_PATH + '/resource_resource/popup/', p);
            }
        };
        widgetUpload.init();
    });
}));