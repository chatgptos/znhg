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
$this->title = '用户总现金结算统计';



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
        'name' => '总结算统计',
        'active' => true,
        'url' => $urlManager->createUrl(['mch/settlementstatistics/data/user1', 'cat_id' => 2,]),
    ],
//    [
//        'name' => '推荐用户数',
//        'active' => true,
//        'url' => $urlManager->createUrl(['mch/settlementstatistics/data/user1', 'cat_id' => 3,]),
//    ],
//    [
//        'name' => '推荐付费用户数',
//        'active' => true,
//        'url' => $urlManager->createUrl(['mch/settlementstatistics/data/user1', 'cat_id' => 4,]),
//    ],
//    [
//        'name' => '用户积分',
//        'active' => true,
//        'url' => $urlManager->createUrl(['mch/settlementstatistics/data/user1', 'cat_id' => 5,]),
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
            <div class="float-left">
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?= isset($Gets['status']) && $Gets['status'] == 2 ? '按订单数' : '按消费金额' ?>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton"
                         style="max-height: 200px;overflow-y: auto">
                        <a class="dropdown-item"
                           href="<?= $urlManager->createUrl(array_merge(['mch/settlementstatistics/data/user1'], $Gets, ['status' => 3])) ?>">推荐用户数</a>
                        <a class="dropdown-item"
                           href="<?= $urlManager->createUrl(array_merge(['mch/settlementstatistics/data/user1'], $Gets, ['status' => 4])) ?>">推荐付费用户数</a>
                        <a class="dropdown-item"
                           href="<?= $urlManager->createUrl(array_merge(['mch/settlementstatistics/data/user1'], $Gets, ['status' => 1])) ?>">按消费金额</a>
                        <a class="dropdown-item"
                           href="<?= $urlManager->createUrl(array_merge(['mch/settlementstatistics/data/user1'], $Gets, ['status' => 2])) ?>">按订单数</a>

                        <a class="dropdown-item"
                           href="<?= $urlManager->createUrl(array_merge(['mch/settlementstatistics/data/user1'], $Gets, ['status' => 5])) ?>">用户积分</a>

                        <a class="dropdown-item"
                           href="<?= $urlManager->createUrl(array_merge(['mch/settlementstatistics/data/user1'], $Gets, ['status' => 6])) ?>">总消费</a>
                        <a class="dropdown-item"
                           href="<?= $urlManager->createUrl(array_merge(['mch/settlementstatistics/data/user1'], $Gets, ['status' => 7])) ?>">总奖励</a>
                    </div>
                </div>
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
        <div class="fs-sm text-danger">注：请白天不要查询；总金额不包含自己</div>
        <table class="table table-hover table-bordered bg-white">
            <thead>
            <tr>
                <th class="text-center">排行</th>
                <th>用户</th>
                <th>id</th>
                <th>用户数</th>
                <th>付费数</th>
                <th>总金额</th>
                <th>总返点</th>
                <th>总预售</th>
                <th>(总预售点)</th>
                <th>众筹</th>
                <th>(总众筹点)</th>
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
                    <td class="nowrap"><?= $value['allson_num'] ?></td>
                    <td class="nowrap"><?= $value['allson_num_haslevel'] ?></td>
                    <td class="nowrap"><?= $value['all_son_sum_price'] ?></td>
                    <td class="nowrap"><?= $value['all_son_sum_price_level'] ?></td>
                    <td class="nowrap"><?= $value['all_son_sum_price_bookmall'] ?></td>
                    <td class="nowrap"><?= $value['all_son_sum_price_level_bookmall'] ?></td>
                    <td class="nowrap"><?= $value['all_son_sum_price_crowdc'] ?></td>
                    <td class="nowrap"><?= $value['all_son_sum_price_level_crowdc'] ?></td>
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
