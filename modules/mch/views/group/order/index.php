<?php

/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/6/29
 * Time: 9:50
 */

use yii\widgets\LinkPager;

/* @var \app\models\User $user */

$urlManager = Yii::$app->urlManager;
$statics = Yii::$app->request->baseUrl . '/statics';
$this->title = '拼团订单列表';
$this->params['active_nav_group'] = 10;
$this->params['is_group'] = 1;
$status = Yii::$app->request->get('status', -1);

$condition = [
    'user_id' => Yii::$app->request->get('user_id'),
    'is_offline' => Yii::$app->request->get('is_offline'),
    'clerk_id' => Yii::$app->request->get('clerk_id'),
    'shop_id' => Yii::$app->request->get('shop_id')
];
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
                                            订单号
                                        </option>
                                        <option value="2" <?= Yii::$app->request->get('keyword_1') == 2 ? "selected" : "" ?>>
                                            用户
                                        </option>
                                        <option value="3" <?= Yii::$app->request->get('keyword_1') == 3 ? "selected" : "" ?>>
                                            商品名
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
                                    <label>下单时间：</label>
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
                                <a class="btn btn-secondary"
                                   href="<?= Yii::$app->request->url . "&flag=EXPORT" ?>">批量导出</a>
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
                       href="<?= $urlManager->createUrl(array_merge(['mch/group/order/index'])) ?>">全部</a>
                </li>
                <li class="nav-item">
                    <a class="status-item nav-link <?= $status == 0 ? 'active' : null ?>"
                       href="<?= $urlManager->createUrl(array_merge(['mch/group/order/index'], $condition, ['status' => 0])) ?>">未付款</a>
                </li>
                <li class="nav-item">
                    <a class="status-item nav-link <?= $status == 1 ? 'active' : null ?>"
                       href="<?= $urlManager->createUrl(array_merge(['mch/group/order/index'], $condition, ['status' => 1])) ?>">待发货</a>
                </li>
                <li class="nav-item">
                    <a class="status-item nav-link <?= $status == 2 ? 'active' : null ?>"
                       href="<?= $urlManager->createUrl(array_merge(['mch/group/order/index'], $condition, ['status' => 2])) ?>">已发货</a>
                </li>
                <li class="nav-item">
                    <a class="status-item nav-link <?= $status == 6 ? 'active' : null ?>"
                       href="<?= $urlManager->createUrl(array_merge(['mch/group/order/index'], $condition, ['status' => 6])) ?>">拼团中</a>
                </li>
                <li class="nav-item">
                    <a class="status-item nav-link <?= $status == 3 ? 'active' : null ?>"
                       href="<?= $urlManager->createUrl(array_merge(['mch/group/order/index'], $condition, ['status' => 3])) ?>">已完成</a>
                </li>
                <li class="nav-item">
                    <a class="status-item nav-link <?= $status == 5 ? 'active' : null ?>"
                       href="<?= $urlManager->createUrl(array_merge(['mch/group/order/index'], $condition, ['status' => 5])) ?>">已取消</a>
                </li>
            </ul>
        </div>
        <table class="table table-bordered bg-white">
            <tr>
                <th class="order-tab-1">商品信息</th>
                <th class="order-tab-2">金额</th>
                <th class="order-tab-3">实际付款</th>
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
                            <span class="mr-5">订单号：<?= $order_item['order_no'] ?></span>
                            <span>用户：<?= $order_item['nickname'] ?></span>
                            <?php if ($order_item['parent_id'] == 0 && $order_item['is_group'] == 1): ?>
                                <span class="mr-5">
                                <span class="badge badge-danger">团长</span>
                                    <!--                                <span class="badge badge-success">团长</span>-->
                            </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="order-tab-1">
                            <div class="goods-item" flex="dir:left box:first">
                                <div class="fs-0">
                                    <div class="goods-pic"
                                         style="background-image: url('<?= $order_item['pic'] ?>')"></div>
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
                                    <div class="fs-sm">小计：
                                        <span class="text-danger"><?= $order_item['total_price'] ?>元</span></div>
                                </div>
                            </div>
                        </td>
                        <td class="order-tab-2">
                            <div>总金额：<?= $order_item['total_price'] ?>元（含运费）</div>
                            <div>运费：<?= $order_item['express_price'] ?>元</div>
                            <?php if ($order_item['colonel'] && $order_item['parent_id'] == 0 && $order_item['is_group'] == 1): ?>
                                <div>团长优惠：<?= $order_item['colonel'] ?>元</div>
                            <?php endif; ?>
                        </td>
                        <td class="order-tab-3">
                            <div><?= $order_item['pay_price'] ?>元</div>
                        </td>
                        <td class="order-tab-4">
                            <div>
                                付款状态：
                                <?php if ($order_item['is_pay'] == 1): ?>
                                    <span class="badge badge-success">已付款</span>
                                <?php else: ?>
                                    <span class="badge badge-default">未付款</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                发货方式：
                                <?php if ($order_item['offline'] == 1): ?>
                                    <span class="badge badge-success">送货上门</span>
                                <?php else: ?>
                                    <span class="badge badge-default">到店自提</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($order_item['is_pay'] == 1): ?>
                                <div>
                                    发货状态：
                                    <?php if ($order_item['is_send'] == 1): ?>
                                        <span class="badge badge-success">已发货</span>
                                    <?php else: ?>
                                        <span class="badge badge-default">未发货</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($order_item['is_send'] == 1): ?>
                                <div>
                                    收货状态：
                                    <?php if ($order_item['is_confirm'] == 1): ?>
                                        <span class="badge badge-success">已收货</span>
                                    <?php else: ?>
                                        <span class="badge badge-default">未收货</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($order_item['is_send'] == 1): ?>
                                <?php if ($order_item['is_offline'] == 0 || $order_item['express']): ?>
                                    <div>快递单号：<a href="https://www.baidu.com/s?wd=<?= $order_item['express_no'] ?>"
                                                 target="_blank"><?= $order_item['express_no'] ?></a></div>
                                    <div>快递公司：<?= $order_item['express'] ?></div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td class="order-tab-5">
                            <?php if ($order_item['is_pay'] == 1 && $order_item['is_confirm'] != 1 && $order_item['is_success'] == 1): ?>
                                <a class="btn btn-sm btn-primary send-btn" href="javascript:"
                                   data-order-id="<?= $order_item['id'] ?>"><?= ($order_item['is_send'] == 1) ? "修改快递单号" : "发货" ?></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5">
                            <div>
                                <span class="mr-3">收货人：<?= $order_item['name'] ?></span>
                                <span class="mr-3">电话：<?= $order_item['mobile'] ?></span>
                                <span>地址：<?= $order_item['address'] ?></span>
                            </div>
                            <div><span>备注：<?= $order_item['content'] ?></span></div>
                        </td>
                    </tr>
                </table>
            </div>
        <?php endforeach; ?>
        <div class="text-center">
            <?= LinkPager::widget(['pagination' => $pagination,]) ?>
            <div class="text-muted"><?= $row_count ?>条数据</div>
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
            url: "<?=$urlManager->createUrl(['mch/group/order/send'])?>",
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
            url: "<?=$urlManager->createAbsoluteUrl(['mch/group/order/print'])?>",
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
    $(document).on('click', '.is-express', function () {
        if ($(this).val() == 0) {
            $('.is-true-express').prop('hidden', true);
        } else {
            $('.is-true-express').prop('hidden', false);
        }
    });
</script>