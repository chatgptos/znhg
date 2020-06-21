<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/9/22
 * Time: 16:41
 */
$urlManager = Yii::$app->urlManager;
$this->title = '购值爽服务点设备设置';
$this->params['active_nav_group'] = 1;
?>
<script charset="utf-8" src="https://map.qq.com/api/js?v=2.exp&key=key=OB4BZ-D4W3U-B7VVO-4PJWW-6TKDJ-WPB77"></script>
<div class="panel mb-3">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <table class="table table-bordered bg-white">
            <thead>
            <tr>
                <th>ID</th>
                <th>购值爽服务点名称</th>
<!--                <th>deviceNo</th>-->
                <th>购值爽服务点地址</th>
                <th>购值爽服务点图片</th>
                <th>购值爽服务点运营状态</th>
                <th>购值爽服务点小程序入口</th>
                <th>购值爽服务点商品信息</th>
            </tr>
            </thead>
            <?php foreach ($list as $item): ?>
                <tr>
                    <td><?= $item['id'] ?></td>
                    <td><?= $item['name'] ?></td>
<!--                    <td>--><?//= $item['deviceNo'] ?><!--</td>-->
                    <td><?= $item['address'] ?></td>
                    <td>
                        <div class="upload-preview text-center upload-preview">
                            <span class="upload-preview-tip">150&times;150</span>
                            <img class="upload-preview-img" src="<?= $item['imgUrl'] ?>">
                        </div>
                    </td>

                    <td> <label class="radio-label">
                            <input id="radio2" <?= $item['status'] == 0 ? 'checked' : null ?>
                                   value="0"
                                   name="status" type="radio" class="custom-control-input">
                            <span class="label-icon"></span>
                            <span class="label-text">关闭</span>
                        </label>
                        <label class="radio-label">
                            <input id="radio1" <?= $item['status']== 1 ? 'checked' : null ?>
                                   value="1"
                                   name="status" type="radio" class="custom-control-input">
                            <span class="label-icon"></span>
                            <span class="label-text">开启</span>
                        </label>
                    </td>



                    <td>
                        <div class="upload-preview text-center upload-preview">
                            <span class="upload-preview-tip">150&times;150</span>
                            <img class="upload-preview-img" src="http://airent-hospital.oss-cn-beijing.aliyuncs.com/uploads/image/bb/bb0089aaedf008080cfbe2e2ef3cb7cf.png">
<!--                            <img class="upload-preview-img" src="--><?//= $item['qrcode'] ?><!--">-->


                        </div>
                    </td>



                    <td>
                        <div class="upload-preview-list">
                            <?php if (count($item['good_list']) > 0): ?>
                                <?php foreach ($item['good_list'] as $item1): ?>
                                    <div class="upload-preview text-center">
                                        <input type="hidden" class="file-item-input"
                                               name="shop_pic[]"
                                               value="<?= $item1['imgUrl'] ?>">
                                        <span class="file-item-delete">&times;</span>
                                        <span class="upload-preview-tip">750&times;360</span>
                                        <img class="upload-preview-img" src="<?= $item1['imgUrl']  ?>">
                                        价格<?= $item1['price'] ?>
                                        名称<?= $item1['goodsName'] ?>
                                        数量<?= $item1['count'] ?>
                                    </div>

                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="upload-preview text-center">
                                    <input type="hidden" class="file-item-input" name="shop_pic[]">
                                    <span class="file-item-delete">&times;</span>
                                    <span class="upload-preview-tip">750&times;360</span>
                                    <img class="upload-preview-img" src="">
                                    价格<?= $item1['price'] ?>
                                    名称<?= $item1['goodsName'] ?>
                                    数量<?= $item1['count'] ?>
                                </div>

                            <?php endif; ?>
                        </div>
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
                                title: "提示",
                                content: res.msg
                            });
                        }
                    }
                });
            }
        });
        return false;
    });
</script>
