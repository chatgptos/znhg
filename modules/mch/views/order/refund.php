<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/6/29
 * Time: 9:50
 */
use yii\widgets\LinkPager;

$urlManager = Yii::$app->urlManager;
$this->title = '售后订单';
$this->params['active_nav_group'] = 3;

$Gets = Yii::$app->request->get();

$status = isset($Gets['status']) ? (int)$Gets['status'] : -1;
$url = Yii::$app->request->url;
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
        width: 30%;
    }

    .order-tab-3 {
        width: 20%;
        text-align: center;
    }

    .order-tab-4 {
        width: 10%;
        text-align: center;
    }

    .status-item.active {
        color: inherit;
    }

    .img-view-list {
        margin-left: -.5rem;
        margin-top: -.5rem;
    }

    .img-view {
        width: 4rem;
        height: 4rem;
        display: inline-block;
        background-size: cover;
        background-position: center;
        cursor: pointer;
        opacity: .85;
        margin-top: .5rem;
        margin-left: .5rem;
    }

    .img-view:hover {
        opacity: 1;
    }

    .img-view-box {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, .5);
        z-index: 1030;
        visibility: hidden;
        opacity: 0;
    }

    .img-view-box.active {
        visibility: visible;
        opacity: 1;
    }

    .img-view-close {
        position: absolute;
        right: 2rem;
        top: 2rem;
        display: inline-block;
        font-size: 3rem;
        color: #ddd !important;
        cursor: pointer;
        width: 3rem;
        height: 3rem;
        line-height: 3rem;
        text-align: center;
    }

    .img-view-close:hover {
        color: #fff !important;
        text-decoration: none;
    }
</style>


<div class="panel mb-3" id="app">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <div class="mb-3 clearfix">
            <div class="p-4 bg-shaixuan">
                <form method="get">
                    <?php $_s = ['keyword', 'keyword_1', 'date_start', 'date_end'] ?>
                    <?php foreach ($Gets as $_gi => $_gv):if (in_array($_gi, $_s)) continue; ?>
                        <input type="hidden" name="<?= $_gi ?>" value="<?= $_gv ?>">
                    <?php endforeach; ?>

                    <div flex="dir:left">
                        <div class="mr-4">
                            <div class="form-group row w-20">
                                <div class="col-5">
                                    <select class="form-control" name="keyword_1">
                                        <option value="1" <?= isset($Gets['keyword_1']) && $Gets['keyword_1'] == 1 ? "selected" : "" ?>>
                                            订单号
                                        </option>
                                        <option value="2" <?= isset($Gets['keyword_2']) && $Gets['keyword_1'] == 2 ? "selected" : "" ?>>
                                            用户
                                        </option>
                                        <option value="3" <?= isset($Gets['keyword_3']) && $Gets['keyword_1'] == 3 ? "selected" : "" ?>>
                                            收货人
                                        </option>
                                    </select>
                                </div>
                                <div class="col-7">
                                    <input class="form-control"
                                           name="keyword"
                                           autocomplete="off"
                                           value="<?= isset($Gets['keyword']) ? trim($Gets['keyword']) : null ?>">
                                </div>
                            </div>
                        </div>
                        <div class="mr-4">
                            <div class="form-group row">
                                <div>
                                    <label>下单时间：</label>
                                </div>
                                <div>
                                    <div class="input-group">
                                        <input class="form-control" id="date_start" name="date_start"
                                               autocomplete="off"
                                               value="<?= isset($Gets['date_start']) ? trim($Gets['date_start']) : '' ?>">
                                        <span class="input-group-btn">
                                            <a class="btn btn-secondary" id="show_date_start" href="javascript:">
                                                <span class="iconfont icon-daterange"></span>
                                            </a>
                                        </span>
                                        <span class="middle-center" style="padding:0 4px">至</span>
                                        <input class="form-control" id="date_end" name="date_end"
                                               autocomplete="off"
                                               value="<?= isset($Gets['date_end']) ? trim($Gets['date_end']) : '' ?>">
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
                                <a class="btn btn-secondary"
                                   href="<?= $url . "&flag=EXPORT" ?>">批量导出</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="mb-4">
            <ul class="nav nav-tabs status">
                <li class="nav-item">
                    <a class="status-item nav-link <?= $status == -1 ? 'active' : null ?>"
                       href="<?= $urlManager->createUrl(['mch/order/refund']) ?>">全部</a>
                </li>
                <li class="nav-item">
                    <a class="status-item nav-link <?= $status == 0 ? 'active' : null ?>"
                       href="<?= $urlManager->createUrl(['mch/order/refund', 'status' => 0]) ?>">待处理</a>
                </li>
                <li class="nav-item">
                    <a class="status-item nav-link <?= $status == 1 ? 'active' : null ?>"
                       href="<?= $urlManager->createUrl(['mch/order/refund', 'status' => 1]) ?>">已处理</a>
                </li>
            </ul>
        </div>
        <table class="table table-bordered bg-white">
            <tr>
                <th class="order-tab-1">商品信息</th>
                <th class="order-tab-2">退款退货|换货</th>
                <th class="order-tab-3">状态</th>
                <th class="order-tab-4">操作</th>
            </tr>
        </table>
        <?php foreach ($list as $order_item): ?>
            <div class="order-item">
                <table class="table table-bordered bg-white">
                    <tr>
                        <td colspan="5">
                            <span class="mr-5">售后申请时间：<?= date('Y-m-d H:i:s', $order_item['addtime']) ?></span>
                            <span class="mr-5">订单号：<?= $order_item['order_no'] ?></span>
                            <span>用户：<?= $order_item['nickname'] ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="order-tab-1">

                            <div class="goods-item" flex="dir:left box:first">
                                <div class="fs-0">
                                    <div class="goods-pic"
                                         style="background-image: url('<?= $order_item['goods_pic'] ?>')"></div>
                                </div>
                                <div class="goods-info">
                                    <div class="goods-name"><?= $order_item['goods_name'] ?></div>
                                    <div class="fs-sm">
                                        规格：
                                        <span class="text-danger">
                                            <?php $attr_list = json_decode($order_item['attr']); ?>
                                            <?php if (is_array($attr_list)):foreach ($attr_list as $attr): ?>
                                                <span class="mr-3"><?= $attr->attr_group_name ?>
                                                    :<?= $attr->attr_name ?></span>
                                            <?php endforeach;;endif; ?>
                                        </span>
                                    </div>
                                    <div class="fs-sm">数量：
                                        <span class="text-danger"><?= $order_item['num'] ?>件</span>
                                    </div>
                                    <div class="fs-sm">金额：
                                        <span class="text-danger"><?= $order_item['total_price'] ?>元</span></div>
                                </div>
                            </div>
                        </td>
                        <td class="order-tab-2">
                            <?php if ($order_item['refund_type'] == 1): ?>
                                <div>售后类型：退货退款</div>
                                <div>退款金额：<span class="text-danger"><?= $order_item['refund_price'] ?>元</span></div>
                                <div>申请理由：<?= $order_item['refund_desc'] ?></div>
                                <div class="img-view-list">
                                    <?php $pic_list = json_decode($order_item['refund_pic_list']) ?>
                                    <?php if (is_array($pic_list)):foreach ($pic_list as $pic): ?>
                                        <div class="img-view" data-src="<?= $pic ?>"
                                             style="background-image: url('<?= $pic ?>')"></div>
                                    <?php endforeach;;endif; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($order_item['refund_type'] == 2): ?>
                                <div>售后类型：换货</div>
                                <div>退款金额：<span class="text-danger"><?= $order_item['refund_price'] ?>元</span></div>
                                <div>申请理由：<?= $order_item['refund_desc'] ?></div>
                                <div class="img-view-list">
                                    <?php $pic_list = json_decode($order_item['refund_pic_list']) ?>
                                    <?php if (is_array($pic_list)):foreach ($pic_list as $pic): ?>
                                        <div class="img-view" data-src="<?= $pic ?>"
                                             style="background-image: url('<?= $pic ?>')"></div>
                                    <?php endforeach;;endif; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="order-tab-3">
                            <?php if ($order_item['refund_status'] == 0): ?>
                                <span class="badge badge-default">待处理</span>
                            <?php elseif ($order_item['refund_status'] == 1): ?>
                                <span class="badge badge-success">已同意退款退货</span>
                            <?php elseif ($order_item['refund_status'] == 2): ?>
                                <span class="badge badge-success">已同意换</span>
                            <?php elseif ($order_item['refund_status'] == 3): ?>
                                <?php if ($order_item['refund_type'] == 1): ?>
                                    <span class="badge badge-danger">已拒绝退货退款</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">已拒换货</span>
                                <?php endif; ?>
                                <div><?= $order_item['refund_refuse_desc'] ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="order-tab-4">
                            <?php if ($order_item['refund_status'] == 0): ?>
                                <?php if ($order_item['refund_type'] == 1): ?>
                                    <div class="mb-2">
                                        <a href="javascript:" class="btn btn-sm btn-success agree-btn-1"
                                           data-id="<?= $order_item['order_refund_id'] ?>"
                                           data-price="<?= $order_item['refund_price'] ?>">同意退款退货</a>
                                    </div>
                                    <div class="mb-2">
                                        <a href="javascript:" class="btn btn-sm btn-danger disagree-btn-1"
                                           data-id="<?= $order_item['order_refund_id'] ?>">拒绝退款退货</a>
                                    </div>
                                <?php else: ?>
                                    <div class="mb-2">
                                        <a href="javascript:" class="btn btn-sm btn-success agree-btn-2"
                                           data-id="<?= $order_item['order_refund_id'] ?>">同意换货</a>
                                    </div>
                                    <div class="mb-2">
                                        <a href="javascript:" class="btn btn-sm btn-danger disagree-btn-2"
                                           data-id="<?= $order_item['order_refund_id'] ?>">拒绝换货</a>
                                    </div>
                                <?php endif; ?>
                            <?php elseif ($order_item['refund_status'] == 1): ?>
                            <?php elseif ($order_item['refund_status'] == 2): ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5">
                            <span class="mr-3">收货人：<?= $order_item['name'] ?></span>
                            <span class="mr-3">电话：<?= $order_item['mobile'] ?></span>
                            <span>地址：<?= $order_item['address'] ?></span>
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


<div class="img-view-box" flex="cross:center main:center">
    <a class="img-view-close" href="javascript:">×</a>
    <img src="">
</div>


<?= $this->render('/layouts/ss') ?>
<script>

    $(document).on("click", ".img-view", function () {
        var src = $(this).attr("data-src");
        $(".img-view-box").addClass("active").find("img").attr("src", src);
    });

    $(document).on("click", ".img-view-close", function () {
        $(".img-view-box").removeClass("active");
    });


    //同意退货退款
    $(document).on("click", ".agree-btn-1", function () {
        var id = $(this).attr("data-id");
        var price = $(this).attr("data-price");
        $.myConfirm({
            content: "确认同意退货退款？<br>确认通过后退款金额<b class=text-danger>" + price + "元</b>将直接返还给用户！",
            confirm: function () {
                $.myLoading({
                    title: "正在提交"
                });
                $.ajax({
                    type: "post",
                    data: {
                        _csrf: _csrf,
                        order_refund_id: id,
                        type: 1,
                        action: 1,
                    },
                    dataType: "json",
                    success: function (res) {
                        if (res.code == 0) {
                            $.myAlert({
                                content: res.msg,
                                confirm: function () {
                                    location.reload();
                                }
                            });
                        }
                        if (res.code == 1) {
                            $.myAlert({
                                content: res.msg,
                                confirm: function () {
                                    $.myLoadingHide();
                                }
                            });
                        }
                    }
                });

            }
        });
    });

    //拒绝退货退款
    $(document).on("click", ".disagree-btn-1", function () {
        var id = $(this).attr("data-id");
        var price = $(this).attr("data-price");
        $.myConfirm({
            content: "确认拒绝该退货退款申请？",
            confirm: function () {
                $.myLoading({
                    title: "正在提交"
                });
                $.ajax({
                    type: "post",
                    data: {
                        _csrf: _csrf,
                        order_refund_id: id,
                        type: 1,
                        action: 2,
                    },
                    dataType: "json",
                    success: function (res) {
                        if (res.code == 0) {
                            $.myAlert({
                                content: res.msg,
                                confirm: function () {
                                    location.reload();
                                }
                            });
                        }
                        if (res.code == 1) {
                            $.myAlert({
                                content: res.msg,
                                confirm: function () {
                                    $.myLoadingHide();
                                }
                            });
                        }
                    }
                });
            }
        });
    });


    //同意换货
    $(document).on("click", ".agree-btn-2", function () {
        var id = $(this).attr("data-id");
        var price = $(this).attr("data-price");
        $.myConfirm({
            content: "确认同意换货？",
            confirm: function () {
                $.myLoading({
                    title: "正在提交"
                });
                $.ajax({
                    type: "post",
                    data: {
                        _csrf: _csrf,
                        order_refund_id: id,
                        type: 2,
                        action: 1,
                    },
                    dataType: "json",
                    success: function (res) {
                        if (res.code == 0) {
                            $.myAlert({
                                content: res.msg,
                                confirm: function () {
                                    location.reload();
                                }
                            });
                        }
                        if (res.code == 1) {
                            $.myAlert({
                                content: res.msg,
                                confirm: function () {
                                    $.myLoadingHide();
                                }
                            });
                        }
                    }
                });

            }
        });
    });


    //拒绝换货
    $(document).on("click", ".disagree-btn-2", function () {
        var id = $(this).attr("data-id");
        var price = $(this).attr("data-price");
        $.myConfirm({
            content: "确认拒绝该换货申请？",
            confirm: function () {
                $.myLoading({
                    title: "正在提交"
                });
                $.ajax({
                    type: "post",
                    data: {
                        _csrf: _csrf,
                        order_refund_id: id,
                        type: 2,
                        action: 2,
                    },
                    dataType: "json",
                    success: function (res) {
                        if (res.code == 0) {
                            $.myAlert({
                                content: res.msg,
                                confirm: function () {
                                    location.reload();
                                }
                            });
                        }
                        if (res.code == 1) {
                            $.myAlert({
                                content: res.msg,
                                confirm: function () {
                                    $.myLoadingHide();
                                }
                            });
                        }
                    }
                });
            }
        });
    });

</script>