<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/2
 * Time: 9:17
 */
$urlManager = Yii::$app->urlManager;
$this->title = '会员等级';
$this->params['active_nav_group'] = 4;
?>
<div class="panel mb-3">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <a class="btn btn-primary" href="<?= $urlManager->createUrl(['mch/user/level-edit']) ?>">会员设置</a>
        <div class="float-right mb-4">
            <form method="get">

                <?php $_s = ['keyword'] ?>
                <?php foreach ($_GET as $_gi => $_gv):if (in_array($_gi, $_s)) continue; ?>
                    <input type="hidden" name="<?= $_gi ?>" value="<?= $_gv ?>">
                <?php endforeach; ?>

                <div class="input-group">
                    <input class="form-control"
                           placeholder="会员等级"
                           name="keyword"
                           autocomplete="off"
                           value="<?= isset($_GET['keyword']) ? trim($_GET['keyword']) : null ?>">
                    <span class="input-group-btn">
                    <button class="btn btn-primary">搜索</button>
                </span>
                </div>
            </form>
        </div>
        <table class="table table-bordered bg-white">
            <tr>
                <td>等级</td>
                <td>等级名称</td>
                <td>折扣</td>
                <td>升级条件</td>
                <td>状态</td>
                <td>操作</td>
            </tr>
            <?php foreach ($list as $index => $value): ?>
                <tr>
                    <td class="nowrap"><?= $value['level'] ?></td>
                    <td class="nowrap"><?= $value['name'] ?></td>
                    <td class="nowrap"><?= $value['discount'] ?></td>
                    <td class="nowrap"><?= $value['money'] ?></td>
                    <td class="nowrap">
                        <?php if ($value['status'] == 1): ?>
                            <span class="badge badge-success">启用</span>
                            |
                            <a href="javascript:" class="status" data-type="0" data-id="<?= $value['id'] ?>">禁用</a>
                        <?php else: ?>
                            <span class="badge badge-danger">禁用</span>
                            |
                            <a href="javascript:" class="status" data-type="1" data-id="<?= $value['id'] ?>">启用</a>
                        <?php endif; ?>
                    </td>
                    <td class="nowrap">
                        <a class="btn btn-sm btn-primary"
                           href="<?= $urlManager->createUrl(['mch/user/level-edit', 'id' => $value['id']]) ?>">编辑</a>
                        <a class="btn btn-sm btn-danger del" href="javascript:" data-content="是否删除？"
                           data-url="<?= $urlManager->createUrl(['mch/user/level-del', 'id' => $value['id']]) ?>">删除</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <div class="text-center">
            <?= \yii\widgets\LinkPager::widget(['pagination' => $pagination,]) ?>
            <div class="text-muted"><?= $row_count ?>条数据</div>
        </div>
    </div>
</div>
<script>
    $(document).on('click', '.status', function () {
        var type = $(this).data('type');
        var id = $(this).data('id');
        var text = '';
        if (type == 0) {
            text = "禁用";
        } else {
            text = "启用";
        }
        $.myConfirm({
            title: '提示',
            content: '是否' + text + '？',
            confirm: function () {
                $.ajax({
                    url: "<?=$urlManager->createUrl(['mch/user/level-type'])?>",
                    dataType: 'json',
                    type: 'get',
                    data: {
                        type: type,
                        id: id
                    },
                    success: function (res) {
                        if (res.code == 0) {
                            window.location.reload();
                        } else {
                            $.myAlert({
                                title: '提示',
                                content: res.msg
                            });
                        }
                    }
                });
            }
        });
    });
</script>
<script>
    $(document).on('click', '.del', function () {
        var a = $(this);
        $.myConfirm({
            title: '提示',
            content: a.data('content'),
            confirm: function () {
                $.ajax({
                    url: a.data('url'),
                    dataType: 'json',
                    type: 'get',
                    success: function (res) {
                        if (res.code == 0) {
                            window.location.reload();
                        } else {
                            $.myAlert({
                                title: '提示',
                                content: res.msg
                            });
                        }
                    }
                });
            }
        });
    });
</script>
