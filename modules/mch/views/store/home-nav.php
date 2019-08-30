<?php

/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/6/27
 * Time: 11:14
 */

use yii\widgets\LinkPager;

$urlManager = Yii::$app->urlManager;
$this->title = '首页导航图标';
$this->params['active_nav_group'] = 1;
?>

<div class="panel mb-3">
    <div class="panel-header">
        <span><?= $this->title ?></span>
        <ul class="nav nav-right">
            <li class="nav-item">
                <a class="nav-link" href="<?= $urlManager->createUrl(['mch/store/home-nav-edit']) ?>">添加图标</a>
            </li>
        </ul>
    </div>
    <div class="panel-body">
        <table class="table table-bordered bg-white">
            <thead>
            <tr>
                <th>ID</th>
                <th>名称</th>
                <th>图标</th>
                <th>页面</th>
                <th>排序</th>
                <th>操作</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($list as $index => $item): ?>
                <tr>
                    <td><?= $item['id'] ?></td>
                    <td><?= $item['name'] ?></td>
                    <td><img src="<?= $item['pic_url'] ?>"
                             style="width: 20px;height: 20px;"></td>
                    <td><?= $item['url']; ?></td>
                    <td><?= $item['sort']; ?></td>
                    <td>
                        <a class="btn btn-sm btn-primary"
                           href="<?= $urlManager->createUrl(['mch/store/home-nav-edit', 'id' => $item['id']]) ?>">修改</a>
                        <a class="btn btn-sm btn-danger nav-del"
                           href="<?= $urlManager->createUrl(['mch/store/home-nav-del', 'id' => $item['id']]) ?>">删除</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>

        </table>
    </div>
</div>

<script>
    $(document).on('click', '.nav-del', function () {
        var a = $(this);
        $.confirm({
            content: "确认删除？",
            confirm: function () {
                $.loading();
                $.ajax({
                    url: a.attr("href"),
                    dataType: "json",
                    success: function (res) {
                        $.loadingHide();
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
            }
        });
        return false;
    });
</script>