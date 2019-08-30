/*---- 对话框 start ----*/
$.confirm = function (args) {
    args = args || {};
    var content = args.content || "";
    var confirmText = args.confirmText || "确认";
    var cancelText = args.cancelText || "取消";
    var confirm = args.confirm || function () {
    };
    var cancel = args.cancel || function () {
    };
    var id = $.randomString();
    var html = '';
    html += '<div class="modal fade" data-backdrop="static" id="' + id + '">';
    html += '<div class="modal-dialog modal-sm" role="document">';

    html += '<div class="panel">';
    if (args.title) {
        html += '<div class="panel-header"><b>' + args.title + '</b></div>';
    }
    html += '<div class="panel-body">' + content + '</div>';
    html += '<div class="panel-footer text-right">';
    html += '  <button type="button" class="btn btn-secondary alert-cancel-btn">' + cancelText + '</button>';
    html += '  <button type="button" class="btn btn-primary alert-confirm-btn">' + confirmText + '</button>';
    html += '</div>';
    html += '</div>';

    html += '</div>';
    html += '</div>';
    $("body").append(html);
    $("#" + id).modal("show");
    $(document).on("click", "#" + id + " .alert-confirm-btn", function () {
        $("#" + id).modal("hide");
        confirm();
    });
    $(document).on("click", "#" + id + " .alert-cancel-btn", function () {
        $("#" + id).modal("hide");
        cancel();
    });
};

$.prompt = function (args) {
    args = args || {};
    var content = args.content || "";
    var confirmText = args.confirmText || "确认";
    var cancelText = args.cancelText || "取消";
    var confirm = args.confirm || function () {
    };
    var cancel = args.cancel || function () {
    };
    var id = $.randomString();
    var html = '';
    html += '<div class="modal fade" data-backdrop="static" id="' + id + '">';
    html += '<div class="modal-dialog modal-sm" role="document">';

    html += '<div class="panel">';
    if (args.title) {
        html += '<div class="panel-header"><b>' + args.title + '</b></div>';
    }
    html += '  <div class="panel-body">';
    html += '    <div>' + content + '</div>';
    html += '    <div class="mt-3"><input class="form-control"></div>';
    html += '  </div>';
    html += '  <div class="panel-footer text-right">';
    html += '    <button class="btn btn-secondary alert-cancel-btn">' + cancelText + '</button>';
    html += '    <button class="btn btn-primary alert-confirm-btn">' + confirmText + '</button>';
    html += '  </div>';
    html += '</div>';

    html += '</div>';
    html += '</div>';
    $("body").append(html);
    $("#" + id).modal("show");
    $(document).on("click", "#" + id + " .alert-confirm-btn", function () {
        $("#" + id).modal("hide");
        var val = $("#" + id).find(".form-control").val();
        confirm(val);
    });
    $(document).on("click", "#" + id + " .alert-cancel-btn", function () {
        $("#" + id).modal("hide");
        var val = $("#" + id).find(".form-control").val();
        cancel(val);
    });
};

$.alert = function (args) {
    args = args || {};
    var content = args.content || "";
    var confirmText = args.confirmText || "确认";
    var confirm = args.confirm || function () {
    };
    var id = $.randomString();
    var html = '';
    html += '<div class="modal fade" data-backdrop="static" id="' + id + '">';
    html += '<div class="modal-dialog modal-sm" role="document">';
    html += '<div class="panel">';
    if (args.title) {
        html += '<div class="panel-header"><b>' + args.title + '</b></div>';
    }
    html += '<div class="panel-body">' + content + '</div>';
    html += '<div class="panel-footer text-right"><button class="btn btn-primary alert-confirm-btn">' + confirmText + '</button></div>';
    html += '</div>';
    html += '</div>';
    html += '</div>';
    $("body").append(html);
    $("#" + id).modal("show");
    $(document).on("click", "#" + id + " .alert-confirm-btn", function () {
        $("#" + id).modal("hide");
        confirm();
    });
};

$.loading = function (args) {
    args = args || {};
    var text = args.title || (args.content || '');
    if ($("#myLoading").length > 0) {
        $("#myLoading .loading-text").html(text);
    } else {
        var html = '';
        html += '<div class="modal" data-backdrop="static" id="myLoading" aria-hidden="true">';
        html += '<div class="modal-dialog modal-sm" role="document">';

        html += '<div class="panel">';
        html += '<div class="panel-body">';
        html += '<div class="loading-icon text-center mt-3 mb-3"><img style="width: 24px;height: 24px" src="data:image/gif;base64,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"></div>';
        html += '<div class="loading-text text-center mt-3 mb-3">' + text + '</div>';
        html += '</div>';
        html += '</div>';

        html += '</div>';
        html += '</div>';
        $("body").append(html);
    }
    $("#myLoading").modal("show");
};
$.loadingHide = function () {
    $("#myLoading").modal("hide");
};
/*---- 对话框 end ----*/


/*---- 文件上传 start ----*/
var _pl_file_uploader = {
    id: $.randomString(),
    uploader: null,
    input: null,
    start: null,
    progress_timer: null,
    progress: null,
    success: null,
    error: null,
    complete: null,
    dataType: 'json',
};
$(document).ready(function () {
    $('body').append('<a id="' + _pl_file_uploader.id + '" href="javascript:" style="display: none!important;">pl_file_element</a>');

    function uploader_init() {
        _pl_file_uploader.uploader = new plupload.Uploader({
            browse_button: _pl_file_uploader.id, //触发文件选择对话框的按钮，为那个元素id
            url: _upload_url, //服务器端的上传页面地址
        });
        _pl_file_uploader.uploader.bind('Init', function (uploader) {
            _pl_file_uploader.input = $('#' + _pl_file_uploader.id + ' ~ .moxie-shim input[type=file]');
        });
        _pl_file_uploader.uploader.bind('FilesAdded', function (uploader, files) {
            if (typeof _pl_file_uploader.start === 'function') {
                _pl_file_uploader.start();
            }
            if (typeof _pl_file_uploader.progress === 'function') {
                _pl_file_uploader.progress_timer = setInterval(function () {
                    _pl_file_uploader.progress(_pl_file_uploader.uploader.total);
                }, 200);
            }
            _pl_file_uploader.uploader.start();
        });
        _pl_file_uploader.uploader.bind('FileUploaded', function (uploader, file, responseObject) {
            if (responseObject.status === 200 && typeof _pl_file_uploader.success === 'function') {
                var res = null;
                if (_pl_file_uploader.dataType === 'json') {
                    res = JSON.parse(responseObject.response);
                } else {
                    res = responseObject.response;
                }
                _pl_file_uploader.success(res);
            }
        });
        _pl_file_uploader.uploader.bind('UploadComplete', function (uploader, files) {
            if (_pl_file_uploader.progress_timer)
                clearInterval(_pl_file_uploader.progress_timer);
            if (typeof _pl_file_uploader.complete === 'function') {
                _pl_file_uploader.complete();
            }
            _pl_file_uploader.uploader.destroy();
            uploader_init();
        });
        _pl_file_uploader.uploader.bind('Error', function (uploader, errObject) {
            if (typeof _pl_file_uploader.error === 'function') {
                _pl_file_uploader.error(errObject);
            }
        });
        _pl_file_uploader.uploader.init();
    }

    uploader_init();

});

$.upload_file = function (args) {
    _pl_file_uploader.input.prop('multiple', args.multiple || false);
    _pl_file_uploader.input.attr('accept', args.accept || '*/*');
    _pl_file_uploader.dataType = args.dataType || 'json';
    _pl_file_uploader.dataType = _pl_file_uploader.dataType.toLowerCase();
    _pl_file_uploader.start = args.start || null;
    _pl_file_uploader.progress = args.progress || null;
    _pl_file_uploader.success = args.success || null;
    _pl_file_uploader.error = args.error || null;
    _pl_file_uploader.complete = args.complete || null;
    document.getElementById(_pl_file_uploader.id).click();
};
/*---- 文件上传 end ----*/

/*---- 文件选择 start ----*/
var _file_select = {
    success: null,
};

$(document).on('click', '#file_select_modal .file-item', function () {
    var item = $(this);
    if (typeof _file_select.success === 'function') {
        _file_select.success({
            name: item.attr('data-name'),
            url: item.attr('data-url'),
        });
    }
    $('#file_select_modal').modal('hide');
});

$(document).on('click', '#file_select_modal .file-more', function () {
    var list_block = $('#file_select_modal .file-list');
    var more_btn = $('#file_select_modal .file-more');
    var loading_block = $('#file_select_modal .file-loading');
    var page = parseInt(more_btn.attr('data-page'));
    loading_block.show();
    more_btn.hide();
    $.ajax({
        url: _upload_file_list_url,
        data: {
            dataType: 'html',
            type: 'image',
            page: page,
        },
        success: function (res) {
            more_btn.attr('data-page', page + 1);
            loading_block.hide();
            more_btn.show();
            list_block.append(res);
        }
    });
});

$.select_file = function (args) {
    $('#file_select_modal').modal('show');
    var list_block = $('#file_select_modal .file-list');
    var more_btn = $('#file_select_modal .file-more');
    var loading_block = $('#file_select_modal .file-loading');
    list_block.html('');
    loading_block.show();
    more_btn.hide();
    $.ajax({
        url: _upload_file_list_url,
        data: {
            dataType: 'html',
            type: 'image',
            page: 1,
        },
        success: function (res) {
            more_btn.attr('data-page', 2);
            loading_block.hide();
            more_btn.show();
            list_block.append(res);
        }
    });
    if (typeof args.success === 'function') {
        _file_select.success = args.success;
    }
};
/*---- 文件选择 end ----*/

/*---- 表单自动提交 start ----*/
$(document).ready(function () {
    $(document).on('submit', '.auto-form', function () {
        submit(this);
        return false;
    });
    $(document).on('click', '.auto-form .auto-form-btn', function () {
        var form = $(this).parents('.auto-form');
        submit(form);
        return false;
    });

    function submit(form) {
        var btn = $(form.find('.auto-form-btn'));
        btn.btnLoading(btn.text());
        $.ajax({
            url: form.attr('action') || '',
            type: form.attr('method') || 'get',
            dataType: 'json',
            data: form.serialize(),
            success: function (res) {
                if (res.code == 0) {
                    $.alert({
                        content: res.msg,
                        confirm: function () {
                            if (res.url) {
                                location.href = res.url;
                            } else if (form.attr('return')) {
                                location.href = form.attr('return');
                            } else {
                                location.reload();
                            }
                            setTimeout(function () {
                                btn.btnReset();
                            }, 30000);
                        }
                    });
                }
                if (res.code == 1) {
                    btn.btnReset();
                    $.alert({
                        content: res.msg,
                    });
                }
            },
            error: function (e) {
                btn.btnReset();
                $.alert({
                    title: '<span class="text-danger">系统错误</span>',
                    content: e.responseText,
                });
            }
        });
    }
});
/*---- 表单自动提交 end ----*/

/*---- 快速上传组件 start ----*/
$(document).on('click', '.upload-group .upload-file', function () {
    var btn = $(this);
    var group = btn.parents('.upload-group');
    var input = group.find('.file-input');
    var preview = group.find('.upload-preview');
    var preview_img = group.find('.upload-preview-img');
    $.upload_file({
        accept: group.attr('accept') || 'image/*',
        start: function () {
            btn.btnLoading(btn.text());
        },
        success: function (res) {
            btn.btnReset();
            if (group.hasClass('multiple')) {
                if (preview.find('.file-item-input').val() == '') {
                    preview.find('.file-item-input').val(res.data.url).trigger('change');
                    preview.find('.upload-preview-img').attr('src', res.data.url);
                } else {
                    var preview_item = document.createElementNS('temp_element', 'div');
                    preview_item.innerHTML = preview.prop('outerHTML');
                    preview_item = $(preview_item).find('.upload-preview');
                    var file_item_input = preview_item.find('.file-item-input');
                    var file_item_preview_img = preview_item.find('.upload-preview-img');
                    file_item_input.val(res.data.url).trigger('change');
                    file_item_preview_img.attr('src', res.data.url);
                    group.find('.upload-preview-list').append(preview_item);
                }
            } else {
                input.val(res.data.url).trigger('change');
                preview_img.attr('src', res.data.url);
            }
        },
    });
});
$(document).on('click', '.upload-group .select-file', function () {
    var btn = $(this);
    var group = btn.parents('.upload-group');
    var input = group.find('.file-input');
    var preview = group.find('.upload-preview');
    var preview_img = group.find('.upload-preview-img');
    $.select_file({
        success: function (res) {
            if (group.hasClass('multiple')) {
                if (preview.find('.file-item-input').val() == '') {
                    preview.find('.file-item-input').val(res.url).trigger('change');
                    preview.find('.upload-preview-img').attr('src', res.url);
                } else {
                    var preview_item = document.createElementNS('temp_element', 'div');
                    preview_item.innerHTML = preview.prop('outerHTML');
                    preview_item = $(preview_item).find('.upload-preview');
                    var file_item_input = preview_item.find('.file-item-input');
                    var file_item_preview_img = preview_item.find('.upload-preview-img');
                    file_item_input.val(res.url).trigger('change');
                    file_item_preview_img.attr('src', res.url);
                    group.find('.upload-preview-list').append(preview_item);
                }
            } else {
                input.val(res.url).trigger('change');
                preview_img.attr('src', res.url);
            }
        },
    });
});
$(document).on('click', '.upload-group .delete-file', function () {
    var btn = $(this);
    var group = btn.parents('.upload-group');
    var input = group.find('.file-input');
    var preview_img = group.find('.upload-preview-img');
    input.val('').trigger('change');
    preview_img.attr('src', '');
});
$(document).on('change', '.upload-group .file-input', function () {
    var input = $(this);
    var group = input.parents('.upload-group');
    var preview_img = group.find('.upload-preview-img');
    preview_img.attr('src', input.val());
});
$(document).on('click', '.upload-group .file-item-delete', function () {
    var btn = $(this);
    var preview = btn.parents('.upload-preview');
    if (preview.siblings('.upload-preview').length == 0) {
        preview.find('.file-item-input').val('');
        preview.find('.upload-preview-img').attr('src', '');
    } else {
        preview.remove();
    }
});
/*---- 快速上传组件 end ----*/