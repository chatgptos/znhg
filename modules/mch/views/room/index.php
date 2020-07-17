<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/25
 * Time: 9:24
 */
use yii\widgets\LinkPager;


$urlManager = Yii::$app->urlManager;
$this->title = '直播间管理';
$this->params['active_nav_group'] = 12;
?>

<div class="panel mb-3">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <a class="btn btn-primary mb-3" href="<?= $urlManager->createUrl(['mch/room/transfer']) ?>">同步直播间</a>
        <div class=" bg-white" style="max-width: 70rem">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <td>ID</td>
                    <td>腾讯直播id</td>
                    <td>直播间名称</td>
                    <td>直播间信息</td>
                    <td>创建时间</td>
                    <td>直播商品</td>
                    <td>操作</td>
                </tr>
                </thead>
                <col style="width: 10%;">
                <col style="width: 20%;">
                <col style="width: 35%;">
                <col style="width: 20%;">
                <col style="width: 15%;">
                <tbody>
                <?php foreach ($list as $index => $value): ?>
                    <tr>
                        <td><?= $value['id']; ?></td>
                        <td><?= $value['room_id']; ?></td>
                        <td><?= $value['name']; ?></td>
                        <td>
                            <div class="info p-2" style="border: 1px solid #ddd;">
                                <div flex="dir:left box:first">
                                    <div class="mr-4" data-responsive="88:88" style="width:44px;
                                        background-image: url(<?= $value['pic_url'] ?>);background-size: cover;
                                        background-position: center;border-radius: 88px;"></div>
                                    <div flex="dir:left cross:center"><?= $value['content'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?= date('Y-m-d H:i:s', $value['addtime']); ?></td>


                        <td>
                            <div class="upload-preview-list">
                                <?php if (count($value['good_list']) > 0): ?>
                                    <?php foreach ($value['good_list'] as $item1): ?>
                                        <div class="upload-preview text-center">
                                            <input type="hidden" class="file-item-input"
                                                   name="shop_pic[]"
                                                   value="<?= $item1['cover_img'] ?>">
                                            <span class="file-item-delete">&times;</span>
                                            <span class="upload-preview-tip">750&times;360</span>

                                            <span class="upload-preview-tip">价格<?= $item1['price']/100  ?>元
                                            优惠价格<?= $item1['price2']/100 ?>元
                                            名称<?= $item1['name'] ?>
                                                促销方式<?= $item1['price_type'] ?>
                                                小程序链接<?= $item1['url'] ?></span>
                                            <img class="upload-preview-img" src="<?= $item1['cover_img']  ?>">
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="upload-preview text-center">
                                        <input type="hidden" class="file-item-input"
                                               name="shop_pic[]"
                                               value="<?= $item1['cover_img'] ?>">
                                        <span class="file-item-delete">&times;</span>
                                        <span class="upload-preview-tip">750&times;360</span>
                                        <img class="upload-preview-img" src="<?= $item1['cover_img']  ?>">
                                        价格<?= $item1['price']/100 ?>元
                                        优惠价格<?= $item1['price2']/100 ?>元
                                        名称<?= $item1['name'] ?>
                                        促销方式<?= $item1['price_type'] ?>
                                        小程序链接<?= $item1['url'] ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>




                        <td>
                            <a class="btn btn-sm btn-primary"
                               href="<?= $urlManager->createUrl(['mch/room/edit', 'id' => $value['id']]) ?>">编辑</a>
                            <a class="btn btn-sm btn-danger del" href="javascript:"
                               data-content="是否删除？"
                               data-url="<?= $urlManager->createUrl(['mch/room/del', 'id' => $value['id']]) ?>">删除</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <nav aria-label="Page navigation example">
                <?php echo LinkPager::widget([
                    'pagination' => $pagination,
                    'prevPageLabel' => '上一页',
                    'nextPageLabel' => '下一页',
                    'firstPageLabel' => '首页',
                    'lastPageLabel' => '尾页',
                    'maxButtonCount' => 5,
                    'options' => [
                        'class' => 'pagination',
                    ],
                    'prevPageCssClass' => 'page-item',
                    'pageCssClass' => "page-item",
                    'nextPageCssClass' => 'page-item',
                    'firstPageCssClass' => 'page-item',
                    'lastPageCssClass' => 'page-item',
                    'linkOptions' => [
                        'class' => 'page-link',
                    ],
                    'disabledListItemSubTagOptions' => ['tag' => 'a', 'class' => 'page-link'],
                ])
                ?>
            </nav>
        </div>
    </div>
</div>
<script>
    $(document).on("click", ".del", function () {
        var a = $(this);
        $.myConfirm({
            content: a.data('content'),
            confirm: function () {
                $.myLoading();
                $.ajax({
                    url: a.data('url'),
                    dataType: "json",
                    success: function (res) {
                        if (res.code == 0) {
                            location.reload();
                        } else {
                            $.myLoadingHide();
                            $.myAlert({
                                content: res.msg,
                            });
                        }
                    }
                });
            },
        });
        return false;
    });
</script>
