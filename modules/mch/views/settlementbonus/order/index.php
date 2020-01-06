<?php

/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/6/29
 * Time: 9:50
 */

use yii\widgets\LinkPager;

$urlManager = Yii::$app->urlManager;
$statics = Yii::$app->request->baseUrl . '/statics';
$this->title = '奖金记录列表';
$this->params['active_nav_group'] = 10;
$this->params['is_book'] = 1;
$status = Yii::$app->request->get('status', -1);
$condition = [
    'user_id' => Yii::$app->request->get('user_id'),
    'is_offline' => Yii::$app->request->get('is_offline'),
    'clerk_id' => Yii::$app->request->get('clerk_id'),
    'shop_id' => Yii::$app->request->get('shop_id'),
    'cat_id' => Yii::$app->request->get('cat_id')
];
//
//$cat = [
//    1 => '业绩奖金',
//    2 => '加权分红',
//    3 => '返点奖金',
//    4 => '福利分红',
//    5 => '服务权奖金',
//];
$urlManager = Yii::$app->urlManager;
$this->title = $cat[$cat_id];


$page_navs=[
    [
        'name' => '业绩奖金',
        'active' => $cat_id == 1,
        'url' => $urlManager->createUrl(['mch/settlementbonus/order/offline', 'cat_id' => 1,]),
    ],
    [
        'name' => '加权分红',
        'active' => $cat_id == 2,
        'url' => $urlManager->createUrl(['mch/settlementbonus/order/offline', 'cat_id' => 2,]),
    ],
    [
        'name' => '返点奖金',
        'active' => $cat_id == 3,
        'url' => $urlManager->createUrl(['mch/settlementbonus/order/offline', 'cat_id' => 3,]),
    ],
    [
        'name' => '福利分红',
        'active' => $cat_id == 4,
        'url' => $urlManager->createUrl(['mch/settlementbonus/order/offline', 'cat_id' => 4,]),
    ],
    [
        'name' => '服务权奖金',
        'active' => $cat_id == 5,
        'url' => $urlManager->createUrl(['mch/settlementbonus/order/offline', 'cat_id' => 5,]),
    ],
];

$page_navs_list=[];

 foreach ($page_navs as $key => $nav){
     $page_navs_list[]= $nav['url']='';


 }

// var_dump($page_navs_list);

//
//
//$this->params['page_navs'] = [
//    [
//        'name' => '业绩奖金',
//        'active' => $cat_id == 1,
//        'url' => $urlManager->createUrl(['mch/settlementbonus/order/offline', 'cat_id' => 1,]),
//    ],
//    [
//        'name' => '加权分红',
//        'active' => $cat_id == 2,
//        'url' => $urlManager->createUrl(['mch/settlementbonus/order/offline', 'cat_id' => 2,]),
//    ],
//    [
//        'name' => '返点奖金',
//        'active' => $cat_id == 3,
//        'url' => $urlManager->createUrl(['mch/settlementbonus/order/offline', 'cat_id' => 3,]),
//    ],
//    [
//        'name' => '福利分红',
//        'active' => $cat_id == 4,
//        'url' => $urlManager->createUrl(['mch/settlementbonus/order/offline', 'cat_id' => 4,]),
//    ],
//    [
//        'name' => '服务权奖金',
//        'active' => $cat_id == 5,
//        'url' => $urlManager->createUrl(['mch/settlementbonus/order/offline', 'cat_id' => 5,]),
//    ],
//];
?>
<style>
    .order-item {
        border: 1px solid transparent;
        margin-bottom: 1rem;
    }

    .order-item table {
        margin: 0;
    }

    .order-item:hover {
        border: 1px solid #3c8ee5;
    }

    .goods-item {
        margin-bottom: .75rem;
    }

    .goods-item:last-child {
        margin-bottom: 0;
    }

    .goods-pic {
        width: 5.5rem;
        height: 5.5rem;
        display: inline-block;
        background-color: #ddd;
        background-size: cover;
        background-position: center;
        margin-right: 1rem;
    }

    .goods-name {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }

    .order-tab-1 {
        width: 40%;
    }

    .order-tab-2 {
        width: 20%;
        text-align: center;
    }

    .order-tab-3 {
        width: 10%;
        text-align: center;
    }

    .order-tab-4 {
        width: 20%;
        text-align: center;
    }

    .order-tab-5 {
        width: 10%;
        text-align: center;
    }

    .status-item.active {
        color: inherit;
    }
</style>
<script language="JavaScript" src="<?= $statics ?>/mch/js/LodopFuncs.js"></script>
<object id="LODOP_OB" classid="clsid:2105C259-1E0C-4534-8141-A753534CB4CA" width=0 height=0>
    <embed id="LODOP_EM" type="application/x-print-lodop" width=0 height=0></embed>
</object>




<div class="panel mb-3">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <div class="mb-3 clearfix">
            <div class="p-4 bg-shaixuan">
                <form method="get">
                    <?php $_s = ['keyword', 'keyword_1', 'date_start', 'date_end'] ?>
                    <?php foreach ($_GET as $_gi => $_gv):if (in_array($_gi, $_s)) continue; ?>
                        <input type="hidden" name="<?= $_gi ?>" value="<?= $_gv ?>">
                    <?php endforeach; ?>
                    <div flex="dir:left">
                        <div class="mr-4">
                            <div class="form-group row w-20">
                                <div class="col-5">
                                    <select class="form-control" name="keyword_1">
                                        <option value="1" <?= Yii::$app->request->get('keyword_1') == 1 ? "selected" : "" ?>>
                                            奖金编号
                                        </option>
                                        <option value="2" <?= Yii::$app->request->get('keyword_1') == 2 ? "selected" : "" ?>>
                                            用户
                                        </option>
                                        <option value="3" <?= Yii::$app->request->get('keyword_1') == 3 ? "selected" : "" ?>>
                                            奖金名
                                        </option>
                                    </select>
                                </div>
                                <div class="col-7">
                                    <input class="form-control"
                                           name="keyword"
                                           autocomplete="off"
                                           value="<?= isset($_GET['keyword']) ? trim($_GET['keyword']) : null ?>">
                                </div>
                            </div>
                        </div>
                        <div class="mr-4">
                            <div class="form-group row">
                                <div>
                                    <label>申请时间：</label>
                                </div>
                                <div>
                                    <div class="input-group">
                                        <input class="form-control" id="date_start" name="date_start"
                                               autocomplete="off"
                                               value="<?= isset($_GET['date_start']) ? trim($_GET['date_start']) : '' ?>">
                                        <span class="input-group-btn">
                                            <a class="btn btn-secondary" id="show_date_start" href="javascript:">
                                                <span class="iconfont icon-daterange"></span>
                                            </a>
                                        </span>
                                        <span class="middle-center" style="padding:0 4px">至</span>
                                        <input class="form-control" id="date_end" name="date_end"
                                               autocomplete="off"
                                               value="<?= isset($_GET['date_end']) ? trim($_GET['date_end']) : '' ?>">
                                        <span class="input-group-btn">
                                            <a class="btn btn-secondary" id="show_date_end" href="javascript:">
                                                <span class="iconfont icon-daterange"></span>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                                <div class="middle-center">
                                    <a href="javascript:" class="new-day" data-index="7">近7天</a>
                                    <a href="javascript:" class="new-day" data-index="30">近30天</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div flex="dir:left">
                        <div class="mr-4">
                            <div class="form-group">
                                <button class="btn btn-primary mr-2">筛选</button>
                            </div>
                        </div>
                    </div>


                    <div flex="dir:left">
                        <div class="mr-4">
                            <?php if (isset($user)): ?>
                                <span class="status-item mr-3">会员：<?= $user->nickname ?>的订单</span>
                            <?php endif; ?>
                            <?php if (isset($clerk)): ?>
                                <span class="status-item mr-3">发放员：<?= $clerk->nickname ?>的订单</span>
                            <?php endif; ?>
                            <?php if (isset($shop)): ?>
                                <span class="status-item mr-3">货柜：<?= $shop->name ?>的订单</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>





        <div class="mb-3 clearfix">
            <div class="float-left">
                <div class="dropdown float-right ml-2">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?php $cat_name = null;
                        foreach ($cat_list as $index => $value) {
                            if ($value['id'] == $_GET['cat']) {
                                $cat_name = $value['name'];
                            }
                        } ?>

                        <?= $cat_name ? $cat_name : '全部类型' ?>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton"
                         style="max-height: 200px;overflow-y: auto">
                        <a class="dropdown-item" href="<?= $urlManager->createUrl(['mch/settlementbonus/order/index']) ?>">全部类型</a>
                        <?php foreach ($cat_list as $index => $value): ?>
                            <a class="dropdown-item"
                               href="<?= $urlManager->createUrl(array_merge(['mch/settlementbonus/order/index'], $_GET, ['cat' => $value['id']])) ?>"><?= $value['name'] ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="float-right">
                <form method="get">

                    <?php $_s = ['keyword'] ?>
                    <?php foreach ($_GET as $_gi => $_gv):if (in_array($_gi, $_s)) continue; ?>
                        <input type="hidden" name="<?= $_gi ?>" value="<?= $_gv ?>">
                    <?php endforeach; ?>

                    <div class="input-group">
                        <input class="form-control" placeholder="奖金名/奖金类型" name="keyword"
                               value="<?= isset($_GET['keyword']) ? trim($_GET['keyword']) : null ?>">
                        <span class="input-group-btn">
                    <button class="btn btn-primary">搜索</button>
                </span>
                    </div>
                </form>
            </div>
        </div>







        <div class="mb-4">
            <ul class="nav nav-tabs status">
                <li class="nav-item">
                    <a class="status-item nav-link <?= $status == -1 ? 'active' : null ?>"
                       href="<?= $urlManager->createUrl(array_merge(['mch/settlementbonus/order/index'])) ?>">全部</a>
                </li>
                <li class="nav-item">
                    <a class="status-item nav-link <?= $status == 0 ? 'active' : null ?>"
                       href="<?= $urlManager->createUrl(array_merge(['mch/settlementbonus/order/index'], $condition, ['status' => 0])) ?>">待申请</a>
                </li>
                <li class="nav-item">
                    <a class="status-item nav-link <?= $status == 1 ? 'active' : null ?>"
                       href="<?= $urlManager->createUrl(array_merge(['mch/settlementbonus/order/index'], $condition, ['status' => 1])) ?>">待发放</a>
                </li>
                <li class="nav-item">
                    <a class="status-item nav-link <?= $status == 2 ? 'active' : null ?>"
                       href="<?= $urlManager->createUrl(array_merge(['mch/settlementbonus/order/index'], $condition, ['status' => 2])) ?>">已发放</a>
                </li>
                <li class="nav-item">
                    <a class="status-item nav-link <?= $status == 3 ? 'active' : null ?>"
                       href="<?= $urlManager->createUrl(array_merge(['mch/settlementbonus/order/index'], $condition, ['status' => 3])) ?>">退款</a>
                </li>
                <li class="nav-item">
                    <a class="status-item nav-link <?= $status == 5 ? 'active' : null ?>"
                       href="<?= $urlManager->createUrl(array_merge(['mch/settlementbonus/order/index'], $condition, ['status' => 5])) ?>">已取消</a>
                </li>
            </ul>
        </div>
        <table class="table table-bordered bg-white">
            <tr>
                <th class="order-tab-1">奖金信息</th>
                <th class="order-tab-2">金额</th>
                <th class="order-tab-3">实际支付</th>
                <th class="order-tab-3">活动结束返回用户</th>
                <th class="order-tab-4">订单状态</th>
                <th class="order-tab-5">操作</th>
            </tr>
        </table>
        <?php foreach ($list as $order_item): ?>
            <div class="order-item">
                <table class="table table-bordered bg-white">
                    <tr>
                        <td colspan="5">
                            <span class="mr-5"><?= date('Y-m-d H:i:s', $order_item['addtime']) ?></span>
                            <span class="mr-5">奖金编号：<?= $order_item['order_no'] ?></span>
                            <span>用户：<?= $order_item['nickname'] ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="order-tab-1">
                            <div class="goods-item" flex="dir:left box:first">
                                <div class="fs-0">
                                    <div class="goods-pic"
                                         style="background-image: url('<?= $order_item['cover_pic'] ?>')"></div>
                                </div>
                                <div class="goods-info">
                                    <div class="goods-name"><?= $order_item['goods_name'] ?></div>
                                    <div class="goods-name"><?= $order_item['name'] ?></div>
                                    <div class="fs-sm">小计：
                                        <span class="text-danger"><?= $order_item['total_price'] ?>元</span></div>
                                </div>
                            </div>
                        </td>
                        <td class="order-tab-2">
                            <?php foreach ($order_item['orderFrom'] AS $k => $v): ?>
                                <div><?= $v->key ?>：<?= $v->value ?></div>
                            <?php endforeach; ?>
                        </td>
                        <td class="order-tab-3">
                            <div><?= $order_item['pay_price'] ?>元</div>
                            <div><?= $order_item['coupon'] ?>积分</div>
                            <div><?= $order_item['integral'] ?>券</div>
                        </td>
                        <td class="order-tab-3">
                            <div><span class="text-danger">0元(暂无)</span></div>
                            <div><span class="text-danger"><?= $order_item['return_integral'] ?>积分</span></div>
                            <div><span class="text-danger"><?= $order_item['return_coupon']?>券</span></div>
                        </td>
                        <td class="order-tab-4">
                            <div>
                                申请状态：
                                <?php if ($order_item['is_pay'] == 1): ?>
                                    <span class="badge badge-success">已申请</span>
                                <?php else: ?>
                                    <span class="badge badge-default">未申请</span>
                                    <?php if ($order_item['is_cancel'] == 1): ?>
                                        <span class="badge badge-warning">已取消</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>

                            <?php if ($order_item['is_pay'] == 1): ?>
                                <div>
                                    发货状态：
                                    <?php if ($order_item['is_use'] == 1): ?>
                                        <span class="badge badge-success">已发放</span>
                                    <?php else: ?>
                                        <span class="badge badge-default">未发放</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($order_item['apply_delete'] == 1): ?>
                                <div>
                                    退款状态：
                                    <?php if ($order_item['is_refund'] == 1): ?>
                                        <span class="badge badge-danger">已退款</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">申请退款中</span>
                                    <?php endif; ?>

                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="order-tab-5">
                            <?php if ($order_item['is_pay'] == 1 && $order_item['is_refund'] == 0 && $order_item['apply_delete'] == 1): ?>
                                <a class="btn btn-sm btn-primary send-confirm-btn" href="javascript:"
                                   data-order-id="<?= $order_item['id'] ?>">
                                    退款
                                </a>
                            <?php endif; ?>
                            <?php if ($order_item['is_pay'] == 1 && $order_item['is_confirm'] != 1&& $order_item['is_use'] != 1): ?>
                                <a class="btn btn-sm btn-primary update" href="javascript:" data-toggle="modal"
                                   data-target="#price" data-id="<?= $order_item['id'] ?>">发放奖励</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        <?php endforeach; ?>
        <div class="text-center">
            <?= LinkPager::widget(['pagination' => $pagination,]) ?>
            <div class="text-muted"><?= $row_count ?>条数据</div>
        </div>

    </div>
</div>
<?= $this->render('/layouts/ss'); ?>
<script>
    $(document).on("click", ".apply-status-btn", function () {
        var url = $(this).attr("href");
        $.myConfirm({
            content: "确认“" + $(this).text() + "”？",
            confirm: function () {
                $.myLoading();
                $.ajax({
                    url: url,
                    dataType: "json",
                    success: function (res) {
                        $.myLoadingHide();
                        $.myAlert({
                            content: res.msg,
                            confirm: function () {
                                if (res.code == 0)
                                    location.reload();
                            }
                        });
                    }
                });
            }
        });
        return false;
    });


    //    $(document).on("click", ".send-btn", function () {
    //        var order_id = $(this).attr("data-order-id");
    //        $(".send-modal input[name=order_id]").val(order_id);
    //        $(".send-modal").modal("show");
    //    });
    $(document).on("click", ".send-confirm-btn", function () {

        var order_id = $(this).attr("data-order-id");
        var btn = $(this);
        var error = $(".send-form").find(".form-error");
        btn.btnLoading("正在提交");
        error.hide();
        console.log(error);
        $.ajax({
            url: "<?=$urlManager->createUrl(['mch/settlementbonus/order/refund'])?>",
            type: "get",
            data: {order_id: order_id},
            dataType: "json",
            success: function (res) {
                if (res.code == 0) {
                    btn.text(res.msg);
                    location.reload();
                    $(".send-modal").modal("hide");
                }
                if (res.code == 1) {
                    btn.btnReset();
                    error.html(res.msg).show();
                }
            }
        });
    });


</script>





<!--新加入的-->
<!-- 修改价格 -->
<div class="modal fade" data-backdrop="static" id="price">
    <div class="modal-dialog modal-sm" role="document" style="max-width: 400px">
        <div class="modal-content">
            <div class="modal-header">
                <b class="modal-title">确认发放</b>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input class="order-id" type="hidden">
                商城业绩:<input class="all_son_sum_price" ><br/>
                商城点奖:<input class="all_son_sum_price_level" ><br/>
                预售业绩:<input class="all_son_sum_price_bookmall" ><br/>
                预售点奖:<input class="all_son_sum_price_level_bookmall" ><br/>
                众筹业绩:<input class="all_son_sum_price_crowdc" ><br/>
                众筹点奖:<input class="all_son_sum_price_level_crowdc" ><br/>
                预计发放:<input class="all" ><br/>
                <input class=" form-control money" type="number" placeholder="请填写增加或减少的积分">
                <div class="text-danger form-error mb-3" style="display: none">错误信息</div>
            </div>
            <div class="modal-footer">
<!--                <a href="javascript:" class="btn btn-primary add-price" data-type="1">确认</a>-->
<!--                <a href="javascript:" class="btn btn-primary add-price" data-type="2">奖金</a>-->
                <a href="javascript:" class="btn btn-primary add-price" data-type="1">加价</a>
                <a href="javascript:" class="btn btn-danger add-price" data-type="2">优惠</a>
            </div>
        </div>
    </div>
</div>


<!-- 发货 -->
<div class="modal fade send-modal" data-backdrop="static">
    <div class="modal-dialog modal-sm" role="document" style="max-width: 400px">
        <div class="modal-content">
            <div class="modal-header">
                <b class="modal-title">物流信息</b>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="send-form" method="post">
                    <div class="form-group row">
                        <div class="col-3 text-right">
                            <label class=" col-form-label">物流选择</label>
                        </div>
                        <div class="col-9">
                            <div class="pt-1">
                                <label class="custom-control custom-radio">
                                    <input id="radio1" value="1" checked
                                           name="is_express" type="radio"
                                           class="custom-control-input is-express">
                                    <span class="custom-control-indicator"></span>
                                    <span class="custom-control-description">快递</span>
                                </label>
                                <label class="custom-control custom-radio">
                                    <input id="radio2" value="0" name="is_express" type="radio"
                                           class="custom-control-input is-express">
                                    <span class="custom-control-indicator"></span>
                                    <span class="custom-control-description">无需物流</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="is-true-express">
                        <input class="form-control" type="hidden" autocomplete="off" name="order_id">
                        <label>快递公司</label>
                        <div class="input-group mb-3">
                            <input class="form-control" placeholder="请输入快递公司" type="text" autocomplete="off"
                                   name="express">
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-secondary dropdown-toggle"
                                        data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                </button>
                                <div class="dropdown-menu dropdown-menu-right"
                                     style="max-height: 250px;overflow: auto">
                                    <?php if (count($express_list['private'])): ?>
                                        <?php foreach ($express_list['private'] as $item): ?>
                                            <a class="dropdown-item" href="javascript:"><?= $item ?></a>
                                        <?php endforeach; ?>
                                        <div class="dropdown-divider"></div>
                                    <?php endif; ?>
                                    <?php foreach ($express_list['public'] as $item): ?>
                                        <a class="dropdown-item" href="javascript:"><?= $item ?></a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <label>收件人邮编</label>
                        <input class="form-control" placeholder="请输入收件人邮编" type="text" autocomplete="off"
                               name="post_code">
                        <label><a class="print" href="javascript:">打印面单</a></label>
                        <label><a href='http://www.c-lodop.com/download.html' target='_blank'>下载插件</a></label>
                        <div class="text-danger">需要设置面单打印功能</div>
                        <label>快递单号</label>
                        <input class="form-control" placeholder="请输入快递单号" type="text" autocomplete="off"
                               name="express_no">
                        <div class="text-danger mt-3 form-error" style="display: none"></div>
                    </div>
                    <div class="mt-2">
                        <label>商家留言（选填）</label>
                        <textarea class="form-control" name="words"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary send-confirm-btn">提交</button>
            </div>
        </div>
    </div>
</div>
</div>
</div>
<script>
    $(document).on("click", ".apply-status-btn", function () {
        var url = $(this).attr("href");
        $.myConfirm({
            content: "确认“" + $(this).text() + "”？",
            confirm: function () {
                $.myLoading();
                $.ajax({
                    url: url,
                    dataType: "json",
                    success: function (res) {
                        $.myLoadingHide();
                        $.myAlert({
                            content: res.msg,
                            confirm: function () {
                                if (res.code == 0)
                                    location.reload();
                            }
                        });
                    }
                });
            }
        });
        return false;
    });


    $(document).on("click", ".send-btn", function () {
        var order_id = $(this).attr("data-order-id");
        $(".send-modal input[name=order_id]").val(order_id);
        $(".send-modal").modal("show");
    });
    $(document).on("click", ".send-confirm-btn", function () {
        var btn = $(this);
        var error = $(".send-form").find(".form-error");
        btn.btnLoading("正在提交");
        error.hide();
        console.log(error);
        $.ajax({
            url: "<?=$urlManager->createUrl(['mch/order/send'])?>",
            type: "post",
            data: $(".send-form").serialize(),
            dataType: "json",
            success: function (res) {
                if (res.code == 0) {
                    btn.text(res.msg);
                    location.reload();
                    $(".send-modal").modal("hide");
                }
                if (res.code == 1) {
                    btn.btnReset();
                    error.html(res.msg).show();
                }
            }
        });
    });


</script>
<!--打印函数-->
<script>
    var LODOP; //声明为全局变量
    //检测是否含有插件
    function CheckIsInstall() {
        try {
            var LODOP = getLodop();
            if (LODOP.VERSION) {
                if (LODOP.CVERSION)
                    $.myAlert({
                        content: "当前有C-Lodop云打印可用!\n C-Lodop版本:" + LODOP.CVERSION + "(内含Lodop" + LODOP.VERSION + ")"
                    });
                else
                    $.myAlert({
                        content: "本机已成功安装了Lodop控件！\n 版本号:" + LODOP.VERSION
                    });

            }
        } catch (err) {
        }
    }
    ;
    //打印预览
    function myPreview() {
        LODOP.PRINT_INIT("");
        LODOP.ADD_PRINT_HTM(10, 50, '100%', '100%', $('#print').html());
    }
    $(document).on('click', '.print', function () {
        var id = $(".send-modal input[name=order_id]").val();
        var express = $(".send-modal input[name=express]").val();
        var post_code = $(".send-modal input[name=post_code]").val();
        $.ajax({
            url: "<?=$urlManager->createAbsoluteUrl(['mch/order/print'])?>",
            type: 'get',
            dataType: 'json',
            data: {
                id: id,
                express: express,
                post_code: post_code
            },
            success: function (res) {
                if (res.code == 0) {
                    LODOP.PRINT_INIT("");
                    LODOP.ADD_PRINT_HTM(10, 50, '100%', '100%', res.data.PrintTemplate);
                    LODOP.PREVIEW();
                    $(".send-modal input[name=express_no]").val(res.data.Order.LogisticCode);
                } else {
                    $.myAlert({
                        content: res.msg
                    });
                }
            }
        });
    });
</script>
<script>
    $(document).on('click', '.update', function () {
        var order_id = $(this).data('id');
        $('.order-id').val(order_id);

        $.loading();
        $.ajax({
            url: "<?=$urlManager->createUrl(['mch/settlementbonus/order/getsettlementbonus'])?>",
            type: 'get',
            dataType: 'json',
            data: {
                order_id: order_id,
            },
            success: function (res) {
                if (res.code == 0) {
                    $('.all_son_sum_price').val(res.data.all_son_sum_price);
                    $('.all_son_sum_price_level').val(res.data.all_son_sum_price_level);
                    $('.all_son_sum_price_bookmall').val(res.data.all_son_sum_price_bookmall);
                    $('.all_son_sum_price_level_bookmall').val(res.data.all_son_sum_price_level_bookmall);
                    $('.all_son_sum_price_crowdc').val(res.data.all_son_sum_price_crowdc);
                    $('.all_son_sum_price_level_crowdc').val(res.data.all_son_sum_price_level_crowdc);
                    $('.all').val(res.data.all);

                    $.loadingHide();
                } else {
                    $('.send-price').val(res.msg);
                }
            }
        });
    });
    $(document).on('click', '.add-price', function () {
        var order_id = $('.order-id').val();
        var price = $('.money').val();
        var type = $(this).data('type');
        var error = $('.form-error');
        error.hide();
        $.ajax({
            url: "<?=$urlManager->createUrl(['mch/settlementbonus/order/clerk'])?>",
            type: 'get',
            dataType: 'json',
            data: {
                order_id: order_id,
                price: price,
                type: type
            },
            success: function (res) {
                if (res.code == 0) {
                    window.location.reload();
                } else {
                    error.html(res.msg).show()
                }
            }
        });
    });
    $(document).on('click', '.is-express', function () {
        if ($(this).val() == 0) {
            $('.is-true-express').prop('hidden', true);
        } else {
            $('.is-true-express').prop('hidden', false);
        }
    });
</script>

