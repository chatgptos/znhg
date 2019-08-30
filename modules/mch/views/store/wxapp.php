<?php

use yii\widgets\LinkPager;

$urlManager = Yii::$app->urlManager;
$this->title = '小程序发布';
$this->params['active_nav_group'] = 1;
?>
<style>
    .wxdev-tool-login-qrcode,
    .wxdev-tool-preview-qrcode {
        border: 1px solid #e3e3e3;
    }

    .wxapp-upload-body {
        background: #fafbfc;
    }

    .step-body {
        padding: 4rem 2rem;
        text-align: center;
        display: none;
    }

    .step-body.active {
        display: block;
    }
</style>

<div class="card mb-3">
    <div class="card-block">
       <!--  <p>小程序发布流程请参考文档：<a href="http://cloud.zjhejiang.com/xxx/mall/#help" target="_blank">http://cloud.zjhejiang.com/xxx/mall/#help</a>
        </p> -->
        <?php if (!strstr(Yii::$app->request->hostInfo, 'https://')): ?>
            <p><b class="text-danger">请确认您的服务器是否支持https访问，如不支持，小程序将无法正常运行。</b></p>
        <?php endif; ?>
        <a class="btn btn-primary download-wxapp" href="javascript:">打包并下载小程序</a>
        <a class="btn btn-primary wxapp-qrcode ml-3" href="javascript:">获取小程序二维码</a>
        <div>
            <img src="" class="wxapp-qrcode-img" style="max-width: 320px">
        </div>
    </div>
</div>

<script>
    var wxdev_token = '';
    $(document).on("click", ".download-wxapp", function () {
        var btn = $(this);
        btn.btnLoading(btn.text());
        $.ajax({
            type: "post",
            dataType: "json",
            data: {
                _csrf: _csrf,
                action: 'download',
            },
            success: function (res) {
                btn.btnReset();
                if (res.code == 0) {
                    window.open(res.data);
                }
                if (res.code == 1) {
                }
            }
        });
    });

    $(document).on("click", ".wxdev-tool-login", function () {
        var btn = $(this);
        btn.btnLoading(btn.text());
        $.ajax({
            type: "post",
            dataType: "json",
            data: {
                _csrf: _csrf,
                action: 'wxdev_tool_login',
            },
            success: function (res) {
                btn.btnReset();
                if (res.code == 0) {
                    btn.hide();
                    $('.wxdev-login-block').show();
                    $('.wxdev-tool-login-qrcode').attr('src', res.data.qrcode);
                    wxdev_token = res.token;
                    checkQrcodeScan();
                } else {
                    $.myAlert({
                        content: res.msg,
                        confirm: function () {
                            location.reload();
                        }
                    });
                }
            }
        });
    });

    function checkQrcodeScan() {
        $.ajax({
            type: 'post',
            dataType: "json",
            data: {
                _csrf: _csrf,
                action: 'wxdev_tool_preview',
                token: wxdev_token,
            },
            success: function (res) {
                if (res.code == 0) {
                    $('.step-title').removeClass('active');
                    $('.step-title-2').addClass('active');
                    $('.step-body').removeClass('active');
                    $('.step-body-2').addClass('active');
                    $('.wxdev-tool-preview-qrcode').attr('src', res.data.qrcode);
                }
                if (res.code == -1) {
                    checkQrcodeScan();
                }
                if (res.code == 1) {
                    $.myAlert({
                        content: res.msg,
                        confirm: function () {
                            location.reload();
                        }
                    });
                }
            },
        });
    }

    $(document).on('click', '.wxdev-tool-upload', function () {
        var btn = $(this);
        btn.btnLoading(btn.text());
        $.ajax({
            type: 'post',
            dataType: "json",
            data: {
                _csrf: _csrf,
                action: 'wxdev_tool_upload',
                token: wxdev_token,
            },
            success: function (res) {
                if (res.code == 0) {
                    $('.step-title').removeClass('active');
                    $('.step-title-3').addClass('active');
                    $('.step-body').removeClass('active');
                    $('.step-body-3').addClass('active');
                    $('.upload-version').html(res.data.version);
                    $('.upload-desc').html(res.data.desc);
                }
                if (res.code == 1) {
                    $.myAlert({
                        content: res.msg,
                        confirm: function () {
                            location.reload();
                        }
                    });
                }
            },
        });
    });

    $(document).on("click", ".wxapp-qrcode", function () {
        var btn = $(this);
        btn.btnLoading("正在处理");
        $.ajax({
            url: "<?=$urlManager->createUrl(['mch/store/wxapp-qrcode'])?>",
            type: "post",
            dataType: "json",
            data: {
                _csrf: _csrf,
            },
            success: function (res) {
                btn.btnReset();
                if (res.code == 0) {
                    $(".wxapp-qrcode-img").attr("src", res.data);
                }
                if (res.code == 1) {
                    $.myAlert({
                        content: res.msg,
                        confirm: function () {
                            location.reload();
                        }
                    });
                }
            }
        });
    });
</script>