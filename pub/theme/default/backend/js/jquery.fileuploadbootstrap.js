(function (factory) {
    if (typeof define === "function" && define.amd) {
        define(["jquery", "modal", "./jquery.fileupload", "jquery.ui.sortable"], factory);
    } else if (typeof exports === "object") {
        factory(require(["jquery", "modal", "./jquery.fileupload", "jquery.ui.sortable"]));
    } else {
        factory(jQuery);
    }
}(function ($) {
    $(function () {
        "use strict";
        var widgetUpload = {
            target: null,
            init: function () {
                $('#resource-list').delegate('a.select', 'click', function () {
                    $(this).addClass('active');
                    $('#resource-modal').modal('hide');
                }).delegate('.filters form', 'submit', function () {
                    widgetUpload.loadImagesList($(this).serialize());
                    return false;
                });
                $('.widget-upload').delegate('.delete', 'click', function () {
                    var p = $(this).parent('.inline-box');
                    if ($(p).siblings('.inline-box').length === 0) {
                        $(p).children('[type=hidden]').val('');
                        $(p).find('img').attr('src', GLOBAL.PUB_URL + 'backend/images/placeholder.png');
                    } else {
                        $(p).remove();
                    }
                    return false;
                }).delegate('.add', 'click', function () {
                    var o = $(this).prev('.inline-box');
                    var odiv = $('<div class="inline-box"></div>');
                    $(odiv).html($(o).html());
                    $(odiv).children('[type=hidden]').val('');
                    $(odiv).find('img').attr('src', GLOBAL.PUB_URL + 'backend/images/placeholder.png');
                    $(this).before(odiv);
                    return false;
                }).delegate('.inline-box .btn', 'resource.selected', function () {
                    var a = $('#resource-list .active img');
                    $(this).children('img').attr('src', $(a).attr('src'));
                    $(this).siblings('[type=hidden]').val($(a).data('id'));
                });
                $('.widget-upload').sortable({
                    items: ".inline-box"
                });
                $('#upload-list').delegate('.upload-remove', 'click', function () {
                    $(this).parentsUntil('.item').parent('.item').remove();
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
                    },
                    'hide.bs.modal': function () {
                        var a = $('#resource-list .active');
                        if (a.length) {
                            $(widgetUpload.target).trigger('resource.selected');
                        }
                    }
                });
                $("#upload-element").fileupload({
                    dataType: 'json',
                    add: function (e, data) {
                        data.context = $('<div class="item"></div>').html('<span class="upimage-name">' +
                                data['files'][0].name +
                                '</span><span class="upload-note fa fa-pause"></span><a href="javascript:void(0);" class="upload-remove"><span class="fa fa-remove"></span></a>').bind('upload', function () {
                            var o = this;
                            $(o).find('.upload-note').removeClass('fa-pause').addClass('fa-spinner fa-spin');
                            data.submit().success(function (result, textStatus, jqXHR) {
                                if (result.error == 0) {
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
                $("#resource-list").load(GLOBAL.BASE_URL + 'admin/resource_resource/popupListImages/ .grid', data);
            }
        };
        widgetUpload.init();
    });
}));