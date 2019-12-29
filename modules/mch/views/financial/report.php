<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/6/19
 * Time: 16:52
 */
use \app\models\User;

$urlManager = Yii::$app->urlManager;
$this->title = '用户管理';
$this->params['active_nav_group'] = 4;
?>

<div class="panel mb-3">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <div class="dropdown float-left">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <?php if (isset($_GET['level'])): ?>
                    <?php foreach ($level_list as $index => $value): ?>
                        <?php if ($value['level'] == $_GET['level']): ?>
                            <?= $value['name']; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    全部类型
                <?php endif; ?>
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton"
                 style="max-height: 200px;overflow-y: auto">
                <a class="dropdown-item" href="<?= $urlManager->createUrl(['mch/financial/index']) ?>">全部会员</a>
                <?php foreach ($level_list as $index => $value): ?>
                    <a class="dropdown-item"
                       href="<?= $urlManager->createUrl(array_merge(['mch/financial/index'], $_GET, ['level' => $value['level'], 'page' => 1])) ?>"><?= $value['name'] ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="float-right mb-4">
            <form method="get">

                <?php $_s = ['keyword'] ?>
                <?php foreach ($_GET as $_gi => $_gv):if (in_array($_gi, $_s)) continue; ?>
                    <input type="hidden" name="<?= $_gi ?>" value="<?= $_gv ?>">
                <?php endforeach; ?>

                <div class="input-group">
                    <input class="form-control"
                           placeholder="微信昵称"
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
            <thead>
            <tr>
                <th>ID</th>
                <th>统计日期</th>
                <th>优惠券总数</th>
                <th>用户总数</th>
                <th>积分总数</th>
                <th>欢乐豆总数</th>
                <th>当日积分总数</th>
                <th>当日欢乐豆总数</th>
                <th>当日优惠券总数</th>
                <th>所有交易欢乐豆总数量（已经交易+等待交易）</th>
                <th>所有系统收取欢乐豆手续费（已经交易+等待交易）</th>
                <th>所有系统奖励优惠券数量（已经交易+等待交易）</th>
                <th>所有售卖优惠券数量（已经交易+等待交易）</th>
                <th>所有活跃卖家数量（已经交易+等待交易）</th>
                <th>所有活跃买家数量（已经交易+等待交易）</th>
                <th>系统收欢乐豆总数（已经交易）</th>
                <th>系统收取手续费（已经交易）</th>
                <th>系统奖励优惠券数量（已经交易）</th>
                <th>交易优惠券数量（已经交易）</th>
                <th>所有活跃卖家数量（已经交易）</th>
                <th>所有活跃买家数量（已经交易）</th>
                <th>系统收欢乐豆总数（等待交易）</th>
                <th>系统收取手续费（等待交易）</th>
                <th>系统奖励优惠券数量（等待交易）</th>
                <th>交易优惠券数量（等待交易）</th>
                <th>所有活跃卖家数量（等待交易）</th>
                <th>所有活跃买家数量（等待交易）</th>
                <th>发起统计时间</th>
            </tr>
            </thead>

            <?php foreach ($list as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= $u['statistics_date'] ?></td>
                    <td><?= $u['coupon_count'] ?></td>
                    <td><?= $u['user_count'] ?></td>
                    <td><?= $u['integral_count'] ?></td>
                    <td><?= $u['hld_count'] ?></td>
                    <td><?= $u['jrintegral_count'] ?></td>
                    <td><?= $u['jrhld_count'] ?></td>
                    <td><?= $u['jrcoupon_count'] ?></td>
                    <td><?= $u['peoplesellcount_huanledou1'] ?></td>
                    <td><?= $u['peoplesellcount_huanledou_charge1'] ?></td>
                    <td><?= $u['peoplesellcount_xtjl1'] ?></td>
                    <td><?= $u['peoplesellcount_num1'] ?></td>
                    <td><?= $u['peoplesellcount1'] ?></td>
                    <td><?= $u['peoplebuyercount1'] ?></td>
                    <td><?= $u['peoplesellcount_huanledou2'] ?></td>
                    <td><?= $u['peoplesellcount_huanledou_charge2'] ?></td>
                    <td><?= $u['peoplesellcount_xtjl2'] ?></td>
                    <td><?= $u['peoplesellcount_num2'] ?></td>
                    <td><?= $u['peoplesellcount2'] ?></td>
                    <td><?= $u['peoplebuyercount2'] ?></td>
                    <td><?= $u['peoplesellcount_huanledou3'] ?></td>
                    <td><?= $u['peoplesellcount_huanledou_charge3'] ?></td>
                    <td><?= $u['peoplesellcount_xtjl3'] ?></td>
                    <td><?= $u['peoplesellcount_num3'] ?></td>
                    <td><?= $u['peoplesellcount3'] ?></td>
                    <td><?= $u['peoplebuyercount3'] ?></td>
                    <td><?= date('Y-m-d H:i:s', $u['addtime'])?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <div class="text-center">
            <?= \yii\widgets\LinkPager::widget(['pagination' => $pagination,]) ?>
            <div class="text-muted"><?= $row_count ?>条数据</div>
        </div>
    </div>
</div>
<!-- 充值积分 -->
<div class="modal fade" id="attrAddModal" data-backdrop="static">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">充值积分</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="input-group short-row">
                    <label class="custom-control custom-radio">
                        <input value="1" checked name="rechangeType" type="radio" class="custom-control-input">
                        <span class="custom-control-indicator"></span>
                        <span class="custom-control-description">充值</span>
                    </label>
                    <label class="custom-control custom-radio">
                        <input value="2" name="rechangeType" type="radio" class="custom-control-input integral-reduce">
                        <span class="custom-control-indicator"></span>
                        <span class="custom-control-description">扣除</span>
                    </label>
                </div>

                <input class="form-control" id="integral" placeholder="请填写充值积分" value="0">
                <input type="hidden" id="user_id" value="">
                <div class="form-error text-danger mt-3 rechange-error" style="display: none">ddd</div>
                <div class="form-success text-success mt-3" style="display: none">sss</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary save-rechange">提交</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).on('click', '.del', function () {
        var a = $(this);
        $.myConfirm({
            content: a.data('content'),
            confirm: function () {
                $.ajax({
                    url: a.data('url'),
                    type: 'get',
                    dataType: 'json',
                    success: function (res) {
                        if (res.code == 0) {
                            window.location.reload();
                        } else {
                            $.myAlert({
                                title: res.msg
                            });
                        }
                    }
                });
            }
        });
        return false;
    });
    $(document).on('click', '.rechangeBtn', function () {
        var a = $(this);
        var id = a.data('id');
        var integral = a.data('integral');
        $('#user_id').val(id);
        $('.integral-reduce').attr('data-integral', integral);
    });
    $(document).on('change', '.integral-reduce', function () {
        $('#integral').val($(this).data('integral'));
    });
    $(document).on('click', '.save-rechange', function () {
        var user_id = $('#user_id').val();
        var integral = $('#integral').val();
        var oldIntegral = $('.integral-reduce').data('integral');
        var rechangeType = $("input[type='radio']:checked").val();
        if (rechangeType == '2') {
            if (integral > oldIntegral) {
                $('.rechange-error').css('display', 'block');
                $('.rechange-error').text('当前用户积分不足');
                return;
            }
        }
        if (!integral || integral <= 0) {
            $('.rechange-error').css('display', 'block');
            $('.rechange-error').text('请填写积分');
            return;
        }
        $.ajax({
            url: "<?= Yii::$app->urlManager->createUrl(['mch/financial/rechange']) ?>",
            type: 'post',
            dataType: 'json',
            data: {user_id: user_id, integral: integral, _csrf: _csrf, rechangeType: rechangeType},
            success: function (res) {
                if (res.code == 0) {
                    window.location.reload();
                } else {
                    $('.rechange-error').css('display', 'block');
                    $('.rechange-error').text(res.msg);
                }
            }
        });
    });


</script>
