<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/11
 * Time: 10:33
 */
/* @var $pagination yii\data\Pagination */
use yii\widgets\LinkPager;
use \app\models\Cash;

$urlManager = Yii::$app->urlManager;
$this->title = '提现列表';
$this->params['active_nav_group'] = 5;
$status = Yii::$app->request->get('status');
if ($status === '' || $status === null || $status == -1)
    $status = -1;
?>
<div class="panel mb-3">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <div class="mb-3 clearfix">
            <div class="p-4 bg-shaixuan">
                <form method="get">

                    <?php $_s = ['keyword'] ?>
                    <?php foreach ($_GET as $_gi => $_gv):if (in_array($_gi, $_s)) continue; ?>
                        <input type="hidden" name="<?= $_gi ?>" value="<?= $_gv ?>">
                    <?php endforeach; ?>
                    <div flex="dir:left">
                        <div>
                            <div class="input-group">
                                <input class="form-control"
                                       placeholder="姓名/微信昵称"
                                       name="keyword"
                                       autocomplete="off"
                                       value="<?= isset($_GET['keyword']) ? trim($_GET['keyword']) : null ?>">
                    <span class="input-group-btn">
                    <button class="btn btn-primary">搜索</button>
                </span>
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
                       href="<?= $urlManager->createUrl(['mch/share/cash']) ?>">全部</a>
                </li>
                <li class="nav-item">
                    <a class="status-item nav-link <?= $status == 0 ? 'active' : null ?>"
                       href="<?= $urlManager->createUrl(['mch/share/cash', 'status' => 0]) ?>">未审核<?= $count['count_1'] ? "(" . $count['count_1'] . ")" : null ?></a>
                </li>
                <li class="nav-item">
                    <a class="status-item nav-link <?= $status == 1 ? 'active' : null ?>"
                       href="<?= $urlManager->createUrl(['mch/share/cash', 'status' => 1]) ?>">待打款<?= $count['count_2'] ? "(" . $count['count_2'] . ")" : null ?></a>
                </li>
                <li class="nav-item">
                    <a class="status-item nav-link <?= $status == 2 ? 'active' : null ?>"
                       href="<?= $urlManager->createUrl(['mch/share/cash', 'status' => 2]) ?>">已打款<?= $count['count_3'] ? "(" . $count['count_3'] . ")" : null ?></a>
                </li>
                <li class="nav-item">
                    <a class="status-item nav-link <?= $status == 3 ? 'active' : null ?>"
                       href="<?= $urlManager->createUrl(['mch/share/cash', 'status' => 3]) ?>">无效<?= $count['count_4'] ? "(" . $count['count_4'] . ")" : null ?></a>
                </li>
            </ul>
        </div>
        <table class="table table-bordered bg-white">
            <tr>
                <td width="50px">ID</td>
                <td width="200px">微信信息</td>
                <td>账号信息</td>
                <td>提现金额（元）</td>
                <td>状态</td>
                <td>申请时间</td>
                <td>操作</td>
            </tr>
            <?php foreach ($list as $index => $value): ?>
                <tr>
                    <td><?= $value['user_id'] ?></td>
                    <td data-toggle="tooltip" data-placement="top" title="<?= $value['nickname'] ?>">
                        <span
                            style="width: 150px;display:block;white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><img
                                src="<?= $value['avatar_url'] ?>"
                                style="width: 30px;height: 30px;margin-right: 10px;"><?= $value['nickname'] ?></span>
                    </td>
                    <td>
                        <?php if ($value['mobile']): ?>
                            <div>姓名：<?= $value['name'] ?></div>
                            <div>
                                <?php if ($value['type'] == 0): ?>
                                    <span>微信号：</span>
                                <?php elseif ($value['type'] == 3): ?>
                                    <span>开户行：</span><?= $value['bank'] ?><br>
                                    <span>银行卡号：</span>
                                <?php else: ?>
                                    <span>支付宝账号：</span>
                                <?php endif; ?>
                                <span><?= $value['mobile'] ?></span>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td><?= $value['price'] ?></td>
                    <td><?= Cash::$status[$value['status']] ?><?= ($value['status'] == 2) ? "(" . Cash::$type[$value['type']] . ")" : "" ?></td>
                    <td><?= date('Y-m-d H:i', $value['addtime']); ?></td>
                    <td>
                        <?php if ($value['status'] == 0): ?>
                            <a class="btn btn-sm btn-primary del" href="javascript:"
                               data-url="<?= $urlManager->createUrl(['mch/share/apply', 'status' => 1, 'id' => $value['id']]) ?>"
                               data-content="是否通过申请？">通过</a>
                            <a class="btn btn-sm btn-danger del" href="javascript:"
                               data-url="<?= $urlManager->createUrl(['mch/share/apply', 'status' => 3, 'id' => $value['id']]) ?>"
                               data-content="是否驳回申请？">驳回</a>
                        <?php elseif ($value['status'] == 1): ?>
                            <div>
                                <a class="btn btn-sm btn-primary del" href="javascript:"
                                   data-url="<?= $urlManager->createUrl(['mch/share/confirm', 'status' => 2, 'id' => $value['id']]) ?>"
                                   data-content="是否确认打款？">确认打款</a>
                                <span>（微信支付自动打款）</span>
                            </div>
                            <div class="mt-2">
                                <a class="btn btn-sm btn-primary del" href="javascript:"
                                   data-url="<?= $urlManager->createUrl(['mch/share/confirm', 'status' => 4, 'id' => $value['id']]) ?>"
                                   data-content="是否确认打款？">手动打款</a>
                                <span>（线下打款）</span>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <div class="text-center">
            <?= LinkPager::widget(['pagination' => $pagination,]) ?>
            <div class="text-muted"><?= $count['total'] ? $count['total'] : 0 ?>条数据</div>
        </div>
    </div>
</div>
<?= $this->render('/layouts/ss'); ?>
<script>
    $(document).on('click', '.del', function () {
        var a = $(this);
        if (confirm(a.data('content'))) {
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
        return false;
    });
</script>
