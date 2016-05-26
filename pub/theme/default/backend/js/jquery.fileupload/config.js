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
                    }).sortable({
                        items: ".inline-box"
                    });
                }
                $('#resource-list').delegate('a.select', 'click', function () {
                    $(this).addClass('active');
                    $('#resource-modal').modal('hide');
                }).delegate('.filters form', 'submit', function () {
                    widgetUpload.loadImagesList($(this).serialize());
                    return false;
                });
                $('#upload-list').delegate('.upload-remove', 'click', function () {
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
                                '</span><span class="upload-note"><span class="fa fa-pause"></span></span><a href="javascript:void(0);" class="upload-remove"><span class="fa fa-remove"></span></a>').bind('upload', function () {
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
                $("#resource-list").load(GLOBAL.BASE_URL + 'admin/resource_resource/popup/ .grid', p);
            }
        };
        widgetUpload.init();
    });
}));