<?php

use yii\helpers\Url;

$urlManager = Yii::$app->urlManager;
$this->params['active_nav_group'] = isset($this->params['active_nav_group']) ? $this->params['active_nav_group'] : 0;
$version = $this->context->getVersion();

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">
    <title><?= $this->title ?></title>
    <link href="//at.alicdn.com/t/font_353057_lxz6kujlw4mfgvi.css" rel="stylesheet">
    <link href="<?= Yii::$app->request->baseUrl ?>/statics/mch/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= Yii::$app->request->baseUrl ?>/statics/mch/css/jquery.datetimepicker.min.css" rel="stylesheet">
    <link href="<?= Yii::$app->request->baseUrl ?>/statics/css/flex.css?version=<?= $version ?>" rel="stylesheet">
    <link href="<?= Yii::$app->request->baseUrl ?>/statics/css/common.css?version=<?= $version ?>" rel="stylesheet">
    <link href="<?= Yii::$app->request->baseUrl ?>/statics/mch/css/common.v2.css?version=<?= $version ?>"
          rel="stylesheet">

    <script>var _csrf = "<?=Yii::$app->request->csrfToken?>";</script>
    <script>var _upload_url = "<?=Yii::$app->urlManager->createUrl(['upload/file'])?>";</script>
    <script>var _upload_file_list_url = "<?=Yii::$app->urlManager->createUrl(['mch/store/upload-file-list'])?>";</script>

    <script src="<?= Yii::$app->request->baseUrl ?>/statics/mch/js/jquery.min.js"></script>
    <script src="<?= Yii::$app->request->baseUrl ?>/statics/mch/js/vue.js"></script>
    <script src="<?= Yii::$app->request->baseUrl ?>/statics/mch/js/tether.min.js"></script>
    <script src="<?= Yii::$app->request->baseUrl ?>/statics/mch/js/bootstrap.min.js"></script>
    <script src="<?= Yii::$app->request->baseUrl ?>/statics/mch/js/plupload.full.min.js"></script>
    <script src="<?= Yii::$app->request->baseUrl ?>/statics/mch/js/jquery.datetimepicker.full.min.js"></script>
    <script src="<?= Yii::$app->request->baseUrl ?>/statics/js/common.js?version=<?= $version ?>"></script>
    <script src="<?= Yii::$app->request->baseUrl ?>/statics/mch/js/common.v2.js?version=<?= $version ?>"></script>
    <script src="<?= Yii::$app->request->baseUrl ?>/statics/mch/js/clipboard.js"></script>
    <script src="<?= Yii::$app->request->baseUrl ?>/statics/mch/vendor/layer/layer.js"></script>
    <script src="<?= Yii::$app->request->baseUrl ?>/statics/mch/vendor/laydate/laydate.js"></script>
</head>
<body>
<?= $this->render('/components/pick-link.php') ?>
<!-- 文件选择模态框 Modal -->
<div class="modal fade" id="file_select_modal" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="panel">
            <div class="panel-header">
                <span>选择文件</span>
                <a href="javascript:" class="panel-close" data-dismiss="modal">&times;</a>
            </div>
            <div class="panel-body">
                <div class="file-list"></div>
                <div class="file-loading text-center" style="display: none">
                    <img style="height: 1.14286rem;width: 1.14286rem"
                         src="<?= Yii::$app->request->baseUrl ?>/statics/images/loading-2.svg">
                </div>
                <div class="text-center">
                    <a style="display: none" href="javascript:" class="file-more">加载更多</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$menu_list = $this->context->getMenuList();

//echo '<pre>';print_r($menu_list);die;


$route = Yii::$app->requestedRoute;
$current_menu = getCurrentMenu($menu_list, $route);
function activeMenu($item, $route)
{
    if (isset($item['route']) && ($item['route'] == $route || (isset($item['sub']) && is_array($item['sub']) && in_array($route, $item['sub']))))
        return 'active';
    if (isset($item['list']) && is_array($item['list'])) {
        foreach ($item['list'] as $sub_item) {
            $active = activeMenu($sub_item, $route);
            if ($active != '')
                return $active;
        }
    }
    return '';
}

/**
 * 获取当前二级菜单列表
 * @param $menu_list
 * @param $route
 * @return null
 */
function getCurrentMenu($menu_list, $route)
{
    foreach ($menu_list as $item) {
        if (isset($item['route']) && ($item['route'] == $route || (isset($item['sub']) && is_array($item['sub']) && in_array($route, $item['sub'])))) {
            return $item;
        }
        if (isset($item['list']) && is_array($item['list'])) {
            foreach ($item['list'] as $sub_item) {
                if (isset($sub_item['route']) && ($sub_item['route'] == $route || (isset($sub_item['sub']) && is_array($sub_item['sub']) && in_array($route, $sub_item['sub']))))
                    return $item;
                if (isset($sub_item['list']) && is_array($sub_item['list'])) {
                    foreach ($sub_item['list'] as $sub_sub_item) {
                        if (isset($sub_sub_item['route']) && ($sub_sub_item['route'] == $route || (isset($sub_sub_item['sub']) && is_array($sub_sub_item['sub']) && in_array($route, $sub_sub_item['sub']))))
                            return $item;
                    }
                }
            }
        }
    }
    return null;
}

?>
<div class="sidebar <?= $current_menu && count($current_menu['list']) ? 'sidebar-sub' : null ?>">
    <div class="sidebar-1">
        <div class="logo">
            <a class="home-link"
               href="<?= $urlManager->createUrl(['mch/default/index']) ?>"><?= $this->context->store->name ?></a>
        </div>
        <div>
            <?php foreach ($menu_list as $item): ?>
                <a class="nav-item <?= activeMenu($item, $route) ?>"
                   href="<?= $urlManager->createUrl($item['route']) ?>">
                    <span class="nav-icon iconfont <?= $item['icon'] ?>"></span>
                    <span><?= $item['name'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php if ($current_menu && count($current_menu['list'])): ?>
        <div class="sidebar-2">
            <div class="current-menu-name"><?= $current_menu['name'] ?></div>
            <div class="sidebar-content">
                <?php foreach ($current_menu['list'] as $item): ?>
                    <?php if (isset($item['list']) && is_array($item['list']) && count($item['list']) > 0): ?>
                        <a class="nav-item <?= activeMenu($item, $route) ?>"
                           href="javascript:">
                            <span class="nav-pointer iconfont icon-play_fill"></span>
                            <span><?= $item['name'] ?></span>
                        </a>
                        <div class="sub-item-list">
                            <?php foreach ($item['list'] as $sub_item): ?>
                                <a class="nav-item <?= activeMenu($sub_item, $route) ?>"
                                   href="<?= $urlManager->createUrl($sub_item['route']) ?>">
                                    <span><?= $sub_item['name'] ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <a class="nav-item <?= activeMenu($item, $route) ?>"
                           href="<?= $urlManager->createUrl($item['route']) ?>">
                            <span><?= $item['name'] ?></span>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<div class="main">
    <div class="main-header">
        <?php if (isset($this->params['page_navs']) && is_array($this->params['page_navs'])): ?>
            <div class="btn-group">
                <?php foreach ($this->params['page_navs'] as $page_nav): ?>
                    <a href="<?= $page_nav['url'] ?>"
                       class="btn btn-secondary <?= $page_nav['active'] ? 'active' : null ?>"><?= $page_nav['name'] ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="float-right">
            <div class="btn-group float-left message">
                <a href="javascript:" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown"
                   style="position: relative">
                    <span>订单消息</span>
                    <div class="text-center ml-2 totalNum"
                         hidden
                         style="width: 18px;height: 18px;line-height: 18px;border-radius:999px;display: inline-block;background-color: #ff4544;color:#fff;">
                        5
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-right message-list" hidden>
                </div>
            </div>
            <div class="btn-group float-left">
                <a href="javascript:" class="btn btn-secondary dropdown-toggle"
                   data-toggle="dropdown"><?= $this->context->store->name ?></a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="<?= Url::to(['store/index']) ?>">后台首页</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?= Url::to(['account/index']) ?>">账户设置</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?= Url::to(['public/logout']) ?>">注销</a>
                </div>
            </div>
        </div>
        <script>

            var checkUrl = "<?= Url::to(['get-data/order']) ?>";
            var sound = "<?=Yii::$app->request->baseUrl . '/statics/'?>/5611.wav";
            var t;

            function playSound() {
                var borswer = window.navigator.userAgent.toLowerCase();
                if (borswer.indexOf("ie") >= 0) {
                    //IE内核浏览器
                    var strEmbed = '<embed name="embedPlay" src="' + sound + '" autostart="true" hidden="true" loop="false"></embed>';
                    if ($("body").find("embed").length <= 0)
                        $("body").append(strEmbed);
                    var embed = document.embedPlay;

                    //浏览器不支持 audion，则使用 embed 播放
                    embed.volume = 100;
                } else {
                    //非IE内核浏览器
                    var strAudio = "<audio id='audioPlay' src='" + sound + "' hidden='true'>";
                    if ($("body").find("audio").length <= 0)
                        $("body").append(strAudio);
                    var audio = document.getElementById("audioPlay");

                    //浏览器支持 audion
                    audio.play();
                }
            }

            function checkmessage() {
                $.ajax({
                    url: checkUrl,
                    type: 'get',
                    dataType: 'json',
                    success: function (res) {
                        if (res.code == 0) {
                            var count = res.data.length;
                            if (count == 0) {
                                return;
                            }
                            $('.message-list').empty();
                            for (var i = 0; i < count; i++) {
                                $('.message-list').prop('hidden', false);
                                $('.totalNum').prop('hidden', false).html(count);
                                var html = "<a class='dropdown-item' data-index='" + res.data[i].id + "' href='<?=Yii::$app->urlManager->createUrl(['mch/order/index', 'status' => 1])?>'>" + res.data[i].name + "下了一个订单</a>";
                                $('.message-list').append(html);
                                if (res.data[i].is_sound == 0) {
                                    playSound();
                                }
                            }
                        }
                    }
                });
                t = setTimeout("checkmessage()", 60000)
            }

            $(document).ready(function () {
                $('.message').hover(function () {
                    $('.message-list').show();
                }, function () {
                    $('.message-list').hide();
                });
                $('.message-list').on('click', 'a', function () {
                    var num = $('.totalNum');
                    num.text(num.text() - 1);
                    if (num.text() == 0) {
                        num.prop('hidden', true);
                        $('.message-list').prop('hidden', true)
                    }
                    $.ajax({
                        url: '<?=Yii::$app->urlManager->createUrl(['mch/get-data/message-del'])?>',
                        type: 'get',
                        dataType: 'json',
                        data: {
                            'id': $(this).data('index')
                        },
                        success: function (res) {
                            if (res.code == 0) {
                                window.location.href = $(this).data('url');
                            }
                        }
                    });
                    $(this).remove();
//            return false;
                });
                checkmessage();

            });
        </script>
    </div>
    <div class="main-body">
        <?= $content ?>
    </div>
</div>

<script>
    /*
     * 获取浏览器竖向滚动条宽度
     * 首先创建一个用户不可见、无滚动条的DIV，获取DIV宽度后，
     * 再将DIV的Y轴滚动条设置为永远可见，再获取此时的DIV宽度
     * 删除DIV后返回前后宽度的差值
     *
     * @return    Integer     竖向滚动条宽度
     **/
    function getScrollWidth() {
        var noScroll, scroll, oDiv = document.createElement("DIV");
        oDiv.style.cssText = "position:absolute; top:-1000px; width:100px; height:100px; overflow:hidden;";
        noScroll = document.body.appendChild(oDiv).clientWidth;
        oDiv.style.overflowY = "scroll";
        scroll = oDiv.clientWidth;
        document.body.removeChild(oDiv);
        return noScroll - scroll;
    }

    if ($('.sidebar-content')) {
        $('.sidebar-content').css('width', ($('.sidebar-content').width() + getScrollWidth()) + 'px');
    }


    $(document).on("click", "body > .sidebar .sidebar-2 .nav-item", function () {
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
        } else {
            $(this).addClass('active');
        }
    });

    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })

    $(document).on("click", ".input-hide .tip-block", function () {
        $(this).hide();
    });


    $(document).on("click", ".input-group .dropdown-item", function () {
        var val = $.trim($(this).text());
        $(this).parents(".input-group").find(".form-control").val(val);
    });
</script>

</body>
</html>