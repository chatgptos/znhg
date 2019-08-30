<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/6/19
 * Time: 16:52
 * @var \yii\web\View $this
 */
$urlManager = Yii::$app->urlManager;
$this->title = '首页设置';
$this->params['active_nav_group'] = 1;
?>
<style>
    body.dragging, body.dragging * {
        cursor: move !important;
    }

    .dragged {
        position: absolute;
        z-index: 2000;
        box-shadow: 2px 2px 1px rgba(0, 0, 0, .05);
    }

    .home-block {
        position: relative;
    }

    .home-block .block-content {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }

    .home-block .block-name {
        color: #fff;
        padding: 6px;
        text-align: center;
        background: rgba(0, 0, 0, .2);
        opacity: .9;
    }

    .home-block:hover .block-name {
        background: rgba(0, 0, 0, .7);
        opacity: 1;
    }

    .home-block .block-img {
        width: 100%;
        height: auto;
    }

    .sortable-ghost {
        opacity: .3;
    }

    .mobile-box {
        width: 219px;
        height: 450px;
        background-image: url("<?=Yii::$app->request->baseUrl?>/statics/images/mobile-iphone.png");
        background-size: cover;
        position: relative;
        font-size: .85rem;
        float: left;
        margin-right: 1rem;
    }

    .mobile-box .mobile-screen {
        position: absolute;
        top: 52px;
        left: 12px;
        right: 13px;
        bottom: 54px;
        border: 1px solid #999;
        background: #ccc;
        overflow-y: hidden;
    }

    .mobile-box .mobile-navbar {
        position: absolute;
        top: 0px;
        left: 0px;
        right: 0px;
        height: 38px;
        line-height: 38px;
        text-align: center;
        background: #fff;
    }

    .mobile-box .mobile-content {
        position: absolute;
        top: 38px;
        left: 0;
        right: 0;
        bottom: 0;
        overflow-y: auto;
    }

    .mobile-box .mobile-content::-webkit-scrollbar {
        width: 2px;
    }

    .edit-box {
        list-style: none;
        margin: 0;
        padding: 0;
        width: 100%;
    }

    .edit-box .module-item {
        background: #fff;
        cursor: move;
        border: none;
        margin-bottom: 1px;
    }

    .edit-box .module-item {
    }

    .right-box {
        float: left;
        width: 480px;
    }

    .all-module-list {
        border: 1px solid #eee;
        border-radius: 5px;
    }

    .all-module-list .panel-body {
        height: 280px;
        overflow: auto;
    }

    .all-module-list .module-item {
        display: inline-block;
        border: 1px solid #eee;
        background: #fff;
        width: 80px;
        height: 80px;
        overflow: hidden;
        float: left;
        margin-right: 1rem;
        margin-bottom: 1rem;
    }

    .all-module-list .module-item:hover {
        border-color: #b8dcff;
    }

    .all-module-list .module-name {
        height: 50px;
        text-align: center;
        padding: 5px 0;
    }

    .all-module-list .module-option {
        text-align: center;
        border-top: 1px dashed #eee;
        height: 30px;
        line-height: 30px;
        display: block;
    }
</style>
<div class="panel mb-3" id="app">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <form class="" method="post">

            <div class="clearfix">
                <div class="mobile-box">
                    <div class="mobile-screen">
                        <div class="mobile-navbar">商城首页</div>
                        <div class="mobile-content">
                            <ol class="edit-box" id="sortList">
                                <li v-for="(item,i) in edit_list" class="module-item"
                                    v-bind:data-module-name="item.name">
                                    <div style="position: relative;height: 0;z-index:2;">
                                        <div class="operations">
                                            <a href="javascript:" class="operate-icon item-delete"
                                               v-bind:data-index="i">
                                                <img style="width: 16px;height: 16px"
                                                     src="<?= Yii::$app->request->baseUrl ?>/statics/images/icon-delete.png">
                                            </a>
                                        </div>
                                    </div>
                                    <div v-html="item.content"></div>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="right-box">
                    <div class="panel mb-3 all-module-list">
                        <div class="panel-header">可选模块</div>
                        <div class="panel-body">
                            <div v-for="(item,i) in module_list" class="module-item"
                                 v-bind:data-module-name="item.name">
                                <div>
                                    <span hidden>{{item.name}}</span>
                                    <div class="module-name">{{item.display_name}}</div>
                                    <a href="javascript:" class="module-option item-add" v-bind:data-index="i">添加</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <a class="btn btn-primary mb-3 submit-btn" href="javascript:">保存</a>
                    <div class="text-muted">
                        提示：
                        <br>首页板块可以添加到小程序端，如果没有板块可以<a
                                href="<?= $urlManager->createUrl(['mch/store/home-block-edit']) ?>">点击这里添加板块</a>；
                        <br>首页更新小程序端下拉刷新就可以看到。
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<script src="<?= Yii::$app->request->baseUrl ?>/statics/mch/js/Sortable.min.js"></script>
<script>
    var app = new Vue({
        el: "#app",
        data: {
            module_list: <?=json_encode($module_list, true)?>,
            edit_list: <?=json_encode($edit_list, true)?>,
        }
    });
    $(document).on("click", ".item-add", function () {
        var index = $(this).attr("data-index");
        app.edit_list.push(app.module_list[index]);
    });
    $(document).on("click", ".item-delete", function () {
        var index = $(this).attr("data-index");
        var item = $(this).parents(".module-item");
        var timeout = 200;
        item.slideUp(timeout, function () {
            item.addClass("delete");
            app.edit_list[index].delete = true;
        });
    });


    Sortable.create(document.getElementById("sortList"), {
        animation: 150,
    }); // That's all.


    $(document).on("click", ".submit-btn", function () {
        var module_list = [];
        $(".edit-box .module-item").each(function (i) {
            if ($(this).hasClass("delete"))
                return;
            module_list.push({
                name: $(this).attr("data-module-name"),
            });
        });
        var btn = $(this);
        var success = $(".form-success");
        var error = $(".form-error");
        success.hide();
        error.hide();
        btn.btnLoading(btn.text());
        $.ajax({
            type: "post",
            dataType: "json",
            data: {
                _csrf: _csrf,
                module_list: JSON.stringify(module_list),
            },
            success: function (res) {
                $.alert({
                    content: res.msg,
                    confirm: function () {
                        if (res.code == 0) {
                            location.reload();
                        }
                    }
                });
            }
        });
    });
</script>