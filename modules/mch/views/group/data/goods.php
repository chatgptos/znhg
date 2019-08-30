<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2018/1/6
 * Time: 11:51
 */
use yii\widgets\LinkPager;

/* @var \app\models\User $user */
/* @var \yii\data\Pagination $pagination */

$urlManager = Yii::$app->urlManager;
$statics = Yii::$app->request->baseUrl . '/statics';
$this->title = '销售排行';
$this->params['active_nav_group'] = 10;
$this->params['is_group'] = 1;
$Gets = Yii::$app->request->get();
$this->params['page_navs'] = [
    [
        'name' => '销售统计',
        'active' => true,
        'url' => $urlManager->createUrl(['mch/group/data/goods', 'cat_id' => 1,]),
    ],
    [
        'name' => '用户统计',
        'active' => false,
        'url' => $urlManager->createUrl(['mch/group/data/user', 'cat_id' => 2,]),
    ],
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
                        <?= isset($Gets['status']) && $Gets['status'] == 2 ? '按销售额' : '按销量' ?>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton"
                         style="max-height: 200px;overflow-y: auto">
                        <a class="dropdown-item"
                           href="<?= $urlManager->createUrl(array_merge(['mch/group/data/goods'], $Gets, ['status' => 1])) ?>">按销量</a>
                        <a class="dropdown-item"
                           href="<?= $urlManager->createUrl(array_merge(['mch/group/data/goods'], $Gets, ['status' => 2])) ?>">按销售额</a>
                    </div>
                </div>
            </div>
            <div class="float-right">
                <form method="get">

                    <?php $_s = ['keyword'] ?>
                    <?php foreach ($Gets as $_gi => $_gv):if (in_array($_gi, $_s)) continue; ?>
                        <input type="hidden" name="<?= $_gi ?>" value="<?= $_gv ?>">
                    <?php endforeach; ?>

                    <div class="input-group">
                        <input class="form-control" placeholder="商品名" name="keyword"
                               value="<?= isset($Gets['keyword']) ? trim($Gets['keyword']) : null ?>">
                        <span class="input-group-btn">
                    <button class="btn btn-primary">搜索</button>
                </span>
                    </div>
                </form>
            </div>
        </div>
        <div class="fs-sm text-danger">注：销量和销售额根据已成团的订单来计算，不计算虚拟销量；</div>
        <table class="table table-hover table-bordered bg-white">
            <thead>
            <tr>
                <th class="text-center">排行</th>
                <th>商品名称</th>
                <th>销量</th>
                <th>销售额</th>
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
                                     style="background-image: url('<?= $value['cover_pic'] ?>');"></div>
                            </div>
                            <div flex="cross:center">
                                <div class="ellipsis"><?= $value['name'] ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="nowrap"><?= $value['sales_volume'] ?></td>
                    <td class="nowrap"><?= $value['sales_price'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="text-center">
            <?= LinkPager::widget(['pagination' => $pagination,]) ?>
            <div class="text-muted"><?= $row_count ?>条数据</div>
        </div>
    </div>
</div>
