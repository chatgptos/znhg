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
                           href="<?= $urlManager->createUrl(array_merge(['mch/settlementstatistics/data/user'], $Gets, ['status' => 1])) ?>">按消费金额</a>
                        <a class="dropdown-item"
                           href="<?= $urlManager->createUrl(array_merge(['mch/settlementstatistics/data/user'], $Gets, ['status' => 2])) ?>">按订单数</a>
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
        <div class="fs-sm text-danger">注：消费金额和订单数根据已成团的订单来计算；</div>
        <table class="table table-hover table-bordered bg-white">
            <thead>
            <tr>
                <th class="text-center">排行</th>
                <th>用户信息</th>
                <th>消费金额</th>
                <th>父级</th>
                <th>子分类</th>
                <th>消费金额</th>
                <th>消费金额</th>
                <th>订单数</th>
            </tr>
            </thead>
            <col style="width: 10%;">
            <col style="width: 70%;">
            <col style="width: 10%;">
            <col style="width: 10%;">
            <tbody>
            <?php foreach ($cat_list as $index => $cat): ?>
                <tr>
                    <td><?= $cat['id'] ?></td>
                    <td><?= $cat['nickname'] ?></td>
                    <td>
                        <?php if (!empty($cat['pic_url'])): ?>
                            <img src="<?= $cat['pic_url'] ?>"
                                 style="width: 20px;height: 20px;">
                        <?php endif; ?>
                    </td>
                    <td>

                        <a class="btn btn-sm btn-primary"
                           href="<?= $urlManager->createUrl(['mch/store/cat-edit', 'id' => $cat['id']]) ?>">修改</a>
                        <a class="btn btn-sm btn-primary copy" data-clipboard-text="/pages/cat/cat"
                           href="javascript:" hidden>复制链接</a>
                        <a class="btn btn-sm btn-danger del"
                           href="<?= $urlManager->createUrl(['mch/store/cat-del', 'id' => $cat['id']]) ?>">删除</a>
                    </td>
                </tr>
                <?php foreach ($cat->childrenList as $sub_cat): ?>
                    <tr class="bg-faded">
                        <td><?= $sub_cat['id'] ?></td>
                        <td><span class="mr-2">●</span><?= $sub_cat['nickname'] ?></td>
                        <td>
                            <?php if (!empty($sub_cat['pic_url'])): ?>
                                <img src="<?= $sub_cat['pic_url'] ?>"
                                     style="width: 20px;height: 20px;">
                            <?php endif; ?>
                        </td>
                        <td>

                            <a class="btn btn-sm btn-primary"
                               href="<?= $urlManager->createUrl(['mch/store/cat-edit', 'id' => $sub_cat['id']]) ?>">修改</a>
                            <a class="btn btn-sm btn-primary copy"
                               data-clipboard-text="/pages/list/list?cat_id=<?= $sub_cat['id'] ?>"
                               href="javascript:" hidden>复制链接</a>
                            <a class="btn btn-sm btn-danger del"
                               href="<?= $urlManager->createUrl(['mch/store/cat-del', 'id' => $sub_cat['id']]) ?>">删除</a>
                        </td>
                    </tr>



                    <?php foreach ($sub_cat->childrenList as $sub_cat1): ?>
                        <tr class="bg-faded">
                            <td><?= $sub_cat1['id'] ?></td>
                            <td><span class="mr-2">●●●</span><?= $sub_cat1['nickname'] ?></td>
                            <td>
                                <?php if (!empty($sub_cat1['pic_url'])): ?>
                                    <img src="<?= $sub_cat1['pic_url'] ?>"
                                         style="width: 20px;height: 20px;">
                                <?php endif; ?>
                            </td>
                            <td>

                                <a class="btn btn-sm btn-primary"
                                   href="<?= $urlManager->createUrl(['mch/store/cat-edit', 'id' => $sub_cat1['id']]) ?>">修改</a>
                                <a class="btn btn-sm btn-primary copy"
                                   data-clipboard-text="/pages/list/list?cat_id=<?= $sub_cat1['id'] ?>"
                                   href="javascript:" hidden>复制链接</a>
                                <a class="btn btn-sm btn-danger del"
                                   href="<?= $urlManager->createUrl(['mch/store/cat-del', 'id' => $sub_cat1['id']]) ?>">删除</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>



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
                    <td class="nowrap"><?= $value['parent_id'] ?></td>


                <?php var_dump($value->childrenList);  foreach ($value->childrenList as $sub_cat): ?>
                    <tr class="bg-faded">
                        <td><?= $sub_cat['id'] ?></td>
                        <td><span class="mr-2">●</span><?= $sub_cat['name'] ?></td>
                        <td>
                            <?php if (!empty($sub_cat['pic_url'])): ?>
                                <img src="<?= $sub_cat['pic_url'] ?>"
                                     style="width: 20px;height: 20px;">
                            <?php endif; ?>
                        </td>
                        <td><?= $sub_cat['sort']; ?></td>
                        <td>

                            <a class="btn btn-sm btn-primary"
                               href="<?= $urlManager->createUrl(['mch/store/cat-edit', 'id' => $sub_cat['id']]) ?>">修改</a>
                            <a class="btn btn-sm btn-primary copy"
                               data-clipboard-text="/pages/list/list?cat_id=<?= $sub_cat['id'] ?>"
                               href="javascript:" hidden>复制链接</a>
                            <a class="btn btn-sm btn-danger del"
                               href="<?= $urlManager->createUrl(['mch/store/cat-del', 'id' => $sub_cat['id']]) ?>">删除</a>
                        </td>
                    </tr>
                <?php endforeach; ?>





                    <td class="nowrap"><?= $value['parent_id'] ?></td>
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
