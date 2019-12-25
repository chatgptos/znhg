<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2018/1/6
 * Time: 15:09
 */
use yii\widgets\LinkPager;

/* @var \app\models\User $user */
/* @var \yii\data\Pagination $pagination */

$urlManager = Yii::$app->urlManager;
$statics = Yii::$app->request->baseUrl . '/statics';
$this->title = '用户排行';
$this->params['active_nav_settlementstatistics'] = 10;
$this->params['is_settlementstatistics'] = 1;
$Gets = Yii::$app->request->get();
$this->params['page_navs'] = [
    [
        'name' => '销售统计',
        'active' => false,
        'url' => $urlManager->createUrl(['mch/settlementstatistics/data/goods', 'cat_id' => 1,]),
    ],
    [
        'name' => '用户统计',
        'active' => true,
        'url' => $urlManager->createUrl(['mch/settlementstatistics/data/user', 'cat_id' => 2,]),
    ],
//    [
//        'name' => '推荐用户数',
//        'active' => true,
//        'url' => $urlManager->createUrl(['mch/settlementstatistics/data/user', 'cat_id' => 3,]),
//    ],
//    [
//        'name' => '推荐付费用户数',
//        'active' => true,
//        'url' => $urlManager->createUrl(['mch/settlementstatistics/data/user', 'cat_id' => 4,]),
//    ],
//    [
//        'name' => '用户积分',
//        'active' => true,
//        'url' => $urlManager->createUrl(['mch/settlementstatistics/data/user', 'cat_id' => 5,]),
//    ],
];
?>
<style>
    table {
        table-layout: fixed;
    }

    .goods-pic {
        width: 2rem;
        height: 2rem;
        display: inline-block;
        background-color: #ddd;
        background-size: cover;
        background-position: center;
        margin-right: 1rem;
        border-radius: 2rem;
    }

    th {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }

    .ellipsis {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }

    td.nowrap {
        white-space: nowrap;
        overflow: hidden;
    }

    td.data {
        text-align: center;
        vertical-align: middle;
    }

    td.data.active {
        color: #ff4544
    }

</style>
<div class="panel mb-3">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <div class="mb-3 clearfix">
            <form method="get" class="input-group mb-3" style="max-width: 30rem;">
                <input type="hidden" name="status" value="<?= Yii::$app->request->get('status') ?>">
                <span class="input-group-addon">日期查找</span>
                <input class="form-control" id="date_begin" value="<?= Yii::$app->request->get('date_begin') ?>" name="date_begin">
                <span class="input-group-addon">~</span>
                <input class="form-control" id="date_end" value="<?= Yii::$app->request->get('date_end') ?>" name="date_end">
                <span class="input-group-btn">
                    <button class="btn btn-secondary">查找</button>
                </span>
            </form>
            <div class="float-left">
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?= isset($Gets['status']) && $Gets['status'] == 2 ? '按订单数' : '按消费金额' ?>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton"
                         style="max-height: 200px;overflow-y: auto">
                        <a class="dropdown-item"
                           href="<?= $urlManager->createUrl(array_merge(['mch/settlementstatistics/data/user'], $Gets, ['status' => 3])) ?>">推荐用户数</a>
                        <a class="dropdown-item"
                           href="<?= $urlManager->createUrl(array_merge(['mch/settlementstatistics/data/user'], $Gets, ['status' => 4])) ?>">推荐付费用户数</a>
                        <a class="dropdown-item"
                           href="<?= $urlManager->createUrl(array_merge(['mch/settlementstatistics/data/user'], $Gets, ['status' => 1])) ?>">按消费金额</a>
                        <a class="dropdown-item"
                           href="<?= $urlManager->createUrl(array_merge(['mch/settlementstatistics/data/user'], $Gets, ['status' => 2])) ?>">按订单数</a>

                        <a class="dropdown-item"
                           href="<?= $urlManager->createUrl(array_merge(['mch/settlementstatistics/data/user'], $Gets, ['status' => 5])) ?>">用户积分</a>

                    </div>
                </div>
            </div>


            <div class="form-group">
                <button class="btn btn-primary mr-2">筛选</button>
                <a class="btn btn-secondary"
                   href="<?= Yii::$app->request->url . "&flag=EXPORT" ?>">批量导出(含条件)</a>
                <a class="btn btn-secondary"
                   href="<?= Yii::$app->request->url . "?flag=EXPORT" ?>">批量导出全部</a>
            </div>
            <div class="float-right">
                <form method="get">
                    <?php $_s = ['keyword'] ?>
                    <?php foreach ($Gets as $_gi => $_gv):if (in_array($_gi, $_s)) continue; ?>
                        <input type="hidden" name="<?= $_gi ?>" value="<?= $_gv ?>">
                    <?php endforeach; ?>

                    <div class="input-settlementstatistics">
                        <input class="form-control" placeholder="用户昵称" name="keyword"
                               value="<?= isset($Gets['keyword']) ? trim($Gets['keyword']) : null ?>">
                    <span class="input-settlementstatistics-btn">
                    <button class="btn btn-primary">搜索</button>
                </span>
                    </div>
                </form>
            </div>
        </div>
        <div class="fs-sm text-danger">注：消费金额和订单数根据已订单来计算；</div>
        <table class="table table-hover table-bordered bg-white">
            <thead>
            <tr>
                <th class="text-center">排行</th>
                <th>用户信息</th>
                <th>用户id</th>
                <th>推荐总人数</th>
                <th>推荐付费用户人数</th>
                <th>积分数</th>
                <th>消费金额</th>
                <th>订单数</th>
            </tr>
            </thead>
            <col style="width: 10%;">
            <col style="width: 70%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
            <tbody>


            <?php foreach ($list as $index => $value): ?>
                <tr>
                    <td class="data <?= ($index + 1 + ($pagination->page * $pagination->limit)) <= 3 ? 'active' : '' ?>"><?= $index + 1 + ($pagination->page * $pagination->limit) ?></td>
                    <td>
                        <div flex="dir:left box:first">
                            <div style="height: 2rem;">
                                <div class="goods-pic"
                                     style="background-image: url('<?= $value['avatar_url'] ?>');"></div>
                            </div>
                            <div flex="cross:center">
                                <div class="ellipsis"><?= $value['nickname'] ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="nowrap"><?= $value['id'] ?></td>
                    <td class="nowrap"><?= $value['son_num'] ?></td>
                    <td class="nowrap"><?= $value['son_num_haslevel'] ?></td>
                    <td class="nowrap"><?= $value['integral'] ?></td>
                    <td class="nowrap"><?= $value['sales_price'] ?></td>
                    <td class="nowrap"><?= $value['sales_count'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="text-center">
            <?= LinkPager::widget(['pagination' => $pagination]) ?>
            <div class="text-muted"><?= $row_count ?>条数据</div>
        </div>
    </div>
</div>

<script>
    $(document).on("click", ".delete-btn", function () {
        var url = $(this).attr("href");
        $.confirm({
            content: "确认删除？",
            confirm: function () {
                $.loading();
                $.ajax({
                    url: url,
                    type: "get",
                    dataType: "json",
                    success: function (res) {
                        location.reload();
                    }
                });
            }
        });
        return false;
    });


    $.datetimepicker.setLocale('zh');

    $(function () {
        $('#date_begin').datetimepicker({
            format: 'Y-m-d',
            onShow: function (ct) {
                this.setOptions({
                    maxDate: $('#date_end').val() ? $('#date_end').val() : false
                })
            },
            timepicker: false
        });
        $('#date_end').datetimepicker({
            format: 'Y-m-d',
            onShow: function (ct) {
                this.setOptions({
                    minDate: $('#date_begin').val() ? $('#date_begin').val() : false
                })
            },
            timepicker: false
        });
    });

</script>