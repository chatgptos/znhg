<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/2
 * Time: 9:17
 */
$urlManager = Yii::$app->urlManager;
$this->title = '返点等级';
$this->params['active_nav_group'] = 4;
?>

<!--    <iframe style="height:1000px;width:1200px"  src="https://mta.qq.com/wechat_mini/base/ctr_realtime_data?app_id=500706424">-->
<!--    </iframe>>-->
        <div class="alert alert-info rounded-0">
    <div>注：确认超过设置的售后时间且没有在售后的订单 系统自动按最新设置的匹配等级计算</div>
    <div>注：设置层级返点      未设置0 （所有统计返点不包含自身，自身消费请单独统计）</div>
    <div>注：返点商品 分别结算栏目可以看到 分别计算出的 商城/预售/众筹 兑换暂未统计</div>
    <div>注：返点设置启用（未启用为0） 商品id 层级名称 返点比例 返点购买类型（1商城2预售3众筹）自动启用
        暂时未做导出
        <a target="_blank"
           href="<?= $urlManager->createUrl(['/mch/settlementstatistics/data/user1']) ?>">返点统计数据页面的最后6列</a>、
        <a target="_blank" href="<?= $urlManager->createUrl(['/mch/settlementstatistics/data/user']) ?>">查询自身消费情况</a>。
    </div>
</div>
<div class="panel mb-3">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <a class="btn btn-primary" href="<?= $urlManager->createUrl(['mch/settlementstatistics/choujiang/level-edit']) ?>">返点设置</a>
        <div class="float-right mb-4">
            <form method="get">

                <?php $_s = ['keyword'] ?>
                <?php foreach ($_GET as $_gi => $_gv):if (in_array($_gi, $_s)) continue; ?>
                    <input type="hidden" name="<?= $_gi ?>" value="<?= $_gv ?>">
                <?php endforeach; ?>

                <div class="input-group">
                    <input class="form-control"
                           placeholder="返点等级"
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
                <td>返点名称</td>
                <td>返点比例</td>
                <td>返点商品id </td>
                <td>返点途径 </td>
                <td>状态</td>
                <td>操作</td>
            </tr>
            <?php foreach ($list as $index => $value): ?>
                <tr>
                    <td class="nowrap"><?= $value['level'] ?></td>
                    <td class="nowrap"><?= $value['name'] ?></td>
                    <td class="nowrap"><?= $value['discount'] ?></td>
                    <td class="nowrap"><?= $value['chance'] ?></td>
                    <td class="nowrap"><?= $value['quan'] ?></td>
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
                           href="<?= $urlManager->createUrl(['mch/settlementstatistics/choujiang/level-edit', 'id' => $value['id']]) ?>">编辑</a>
                        <a class="btn btn-sm btn-danger del" href="javascript:" data-content="是否删除？"
                           data-url="<?= $urlManager->createUrl(['mch/settlementstatistics/choujiang/level-del', 'id' => $value['id']]) ?>">删除</a>
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
                    url: "<?=$urlManager->createUrl(['mch/settlementstatistics/choujiang/level-type'])?>",
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
