<?php

/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/6/29
 * Time: 9:50
 */

use yii\widgets\LinkPager;

$urlManager = Yii::$app->urlManager;
$this->title = '拼团商品列表';
$this->params['active_nav_group'] = 10;
$this->params['is_group'] = 1;
?>
<style>
    table {
        table-layout: fixed;
    }

    th {
        text-align: center;
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }

    td {
        text-align: center;
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

    .goods-pic {
        width: 3rem;
        height: 3rem;
        display: inline-block;
        background-color: #ddd;
        background-size: cover;
        background-position: center;
    }
</style>
<div class="panel mb-3">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <?php
        $status = ['已下架', '已上架'];
        ?>
        <div class="mb-3 clearfix">
            <div class="float-left">
                <a href="<?= $urlManager->createUrl(['mch/group/goods/goods-edit']) ?>" class="btn btn-primary"><i
                            class="iconfont icon-playlistadd"></i>添加商品</a>
                <a href="javascript:void(0)" class="btn btn-secondary batch"
                   data-url="<?= $urlManager->createUrl(['mch/group/goods/batch']) ?>" data-content="是否批量上架"
                   data-type="0">批量上架</a>
                <a href="javascript:void(0)" class="btn btn-warning batch"
                   data-url="<?= $urlManager->createUrl(['mch/group/goods/batch']) ?>" data-content="是否批量下架"
                   data-type="1">批量下架</a>
                <a href="javascript:void(0)" class="btn btn-danger batch"
                   data-url="<?= $urlManager->createUrl(['mch/group/goods/batch']) ?>" data-content="是否批量删除"
                   data-type="2">批量删除</a>
                <a href="javascript:void(0)" class="btn btn-danger batch"
                   data-url="<?= $urlManager->createUrl(['mch/group/goods/batch']) ?>" data-content="是否批量设置热销"
                   data-type="3">批量设置热销</a>
                <a href="javascript:void(0)" class="btn btn-danger batch"
                   data-url="<?= $urlManager->createUrl(['mch/group/goods/batch']) ?>" data-content="是否批量取消热销"
                   data-type="4">批量取消热销</a>
                <div class="dropdown float-right ml-2">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?php
                        $cat_name = null;
                        $cat_id = Yii::$app->request->get('cat');
                        foreach ($cat_list as $index => $value) {
                            if ($cat_id && $cat_id == $value['id']) {
                                $cat_name = $value['name'];
                            }
                        } ?>

                        <?= $cat_name ? $cat_name : '全部类型' ?>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton"
                         style="max-height: 200px;overflow-y: auto">
                        <a class="dropdown-item"
                           href="<?= $urlManager->createUrl(['mch/group/goods/index']) ?>">全部类型</a>
                        <?php foreach ($cat_list as $index => $value): ?>
                            <a class="dropdown-item"
                               href="<?= $urlManager->createUrl(array_merge(['mch/group/goods/index'], $_GET, ['cat' => $value['id']])) ?>"><?= $value['name'] ?></a>
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
                        <input class="form-control" placeholder="商品名/商品类型" name="keyword"
                               value="<?= isset($_GET['keyword']) ? trim($_GET['keyword']) : null ?>">
                        <span class="input-group-btn">
                    <button class="btn btn-primary">搜索</button>
                </span>
                    </div>
                </form>
            </div>
        </div>
        <table class="table table-bordered bg-white">
            <thead>
            <tr>
                <th style="text-align: left"><span><input type="checkbox" class="goods-all"></span>&nbsp;&nbsp;ID</th>
                <th>商品类型</th>
                <th class="text-left">商品名称</th>
                <th>商品图片</th>
                <th>团购价</th>
                <th>状态</th>
                <th>热销</th>
                <th>虚拟销量</th>
                <th>排序</th>
                <th>操作</th>
            </tr>
            </thead>
            <col style="width: 5%">
            <col style="width: 10%">
            <col style="width: 22%">
            <col style="width: 8%">
            <col style="width: 8%">
            <col style="width: 9%">
            <col style="width: 9%">
            <col style="width: 6%">
            <col style="width: 5%">
            <col style="width: 18%">
            <tbody>
            <?php foreach ($list as $index => $goods): ?>
                <tr>
                    <td class="nowrap" style="text-align: left">
                    <span>
                        <input data-num="<?= $goods['num'] ?>" type="checkbox"
                               class="goods-one"
                               value="<?= $goods['id'] ?>">
                    </span>&nbsp;&nbsp;<?= $goods['id'] ?>
                    </td>
                    <td class="nowrap"><?= $goods['cname'] ?></td>
                    <td class="text-left ellipsis"><?= $goods['name'] ?></td>
                    <td class="p-0" style="vertical-align: middle">
                        <div class="goods-pic" style="background-image: url(<?= $goods['cover_pic'] ?>)"></div>
                    </td>
                    <td class="nowrap text-danger"><?= $goods['price'] ?></td>
                    <td class="nowrap">
                        <?php if ($goods['status'] == 1): ?>
                            <!--                        <span class="badge badge-info">上架</span>-->
                            <!--                        |-->
                            <a href="javascript:" onclick="upDown(<?= $goods['id'] ?>,'down');"
                               class="badge badge-primary"
                               data-toggle="tooltip" data-placement="top" title="点击设置下架">上架
                            </a>
                        <?php else: ?>
                            <a href="javascript:" onclick="upDown(<?= $goods['id'] ?>,'up');"
                               class="badge badge-info"
                               data-toggle="tooltip" data-placement="top" title="点击设置上架">下架</a>

                        <?php endif ?>
                    </td>
                    <td class="nowrap">
                        <?php if ($goods['is_hot'] == 1): ?>
                            <a onclick="setHot(<?= $goods['id'] ?>,'nohot');"
                               class="badge badge-danger"
                               href="javascript:;"
                               data-toggle="tooltip" data-placement="top" title="点击取消商品热销">热销</a>
                        <?php else: ?>
                            <a onclick="setHot(<?= $goods['id'] ?>,'hot');" href="javascript:;"
                               class="badge badge-primary"
                               data-toggle="tooltip" data-placement="top" title="点击设置为热销">普通</a>
                        <?php endif ?>
                    </td>
                    <td class="nowrap">
                        <?= $goods['virtual_sales'] ?>
                    </td>
                    <td class="nowrap">
                        <?= $goods['sort'] ?>
                    </td>
                    <td class="nowrap">
                        <a class="btn btn-sm btn-primary"
                           href="<?= $urlManager->createUrl(['mch/group/goods/goods-edit', 'id' => $goods['id']]) ?>">修改</a>
                        <a class="btn btn-sm btn-danger del"
                           href="<?= $urlManager->createUrl(['mch/group/goods/goods-del', 'id' => $goods['id']]) ?>">删除</a>
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

<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
    $(document).on('click', '.del', function () {
        if (confirm("是否删除？")) {
            $.ajax({
                url: $(this).attr('href'),
                type: 'get',
                dataType: 'json',
                success: function (res) {
                    alert(res.msg);
                    if (res.code == 0) {
                        window.location.reload();
                    }
                }
            });
        }
        return false;
    });

    function upDown(id, type) {
        var text = '';
        if (type == 'up') {
            text = "上架";
        } else {
            text = '下架';
        }

        var url = "<?= $urlManager->createUrl(['mch/group/goods/goods-up-down']) ?>";
//        if (confirm("是否" + text + "？")) {
//            $.ajax({
//                url: url,
//                type: 'get',
//                dataType: 'json',
//                data: {id: id, type: type},
//                success: function (res) {
//                    if (res.code == 0) {
//                        window.location.reload();
//                    }
//                    if (res.code == 1) {
//                        alert(res.msg);
//                        if (res.return_url) {
//                            location.href = res.return_url;
//                        }
//                    }
//                }
//            });
//        }
        layer.confirm("是否" + text + "？", {
            btn: [text, '取消'] //按钮
        }, function () {
            layer.msg('加载中', {
                icon: 16
                , shade: 0.01
            });
            $.ajax({
                url: url,
                type: 'get',
                dataType: 'json',
                data: {id: id, type: type},
                success: function (res) {
                    if (res.code == 0) {
                        window.location.reload();
                    }
                    if (res.code == 1) {
                        layer.alert(res.msg, {
                            skin: 'layui-layer-molv'
                            , closeBtn: 0
                            , anim: 4 //动画类型
                        });
                        if (res.return_url) {
                            location.href = res.return_url;
                        }
                    }
                }
            });
        });
        return false;
    }

    /**
     * 设置热销
     * @param id
     * @param type
     */
    function setHot(id, type) {
        var text = '';
        if (type == 'hot') {
            text = "设置热销";
        } else {
            text = '取消热销';
        }
        var url = "<?= $urlManager->createUrl(['mch/group/goods/goods-up-down']) ?>";
        layer.confirm("是否" + text + "？", {
            btn: [text, '取消'] //按钮
        }, function () {
            layer.msg('加载中', {
                icon: 16
                , shade: 0.01
            });
            $.ajax({
                url: url,
                type: 'get',
                dataType: 'json',
                data: {id: id, type: type},
                success: function (res) {
                    if (res.code == 0) {
                        window.location.reload();
                    }
                    if (res.code == 1) {
                        layer.alert(res.msg, {
                            skin: 'layui-layer-molv'
                            , closeBtn: 0
                            , anim: 4 //动画类型
                        });
                        if (res.return_url) {
                            location.href = res.return_url;
                        }
                    }
                }
            });
        });
    }

    $(document).on('click', '.goods-all', function () {
        var checked = $(this).prop('checked');
        $('.goods-one').prop('checked', checked);
        if (checked) {
            $('.batch').addClass('is_use');
        } else {
            $('.batch').removeClass('is_use');
        }
    });
    $(document).on('click', '.goods-one', function () {
        var checked = $(this).prop('checked');
        var all = $('.goods-one');
        var is_all = true;//只要有一个没选中，全选按钮就不选中
        var is_use = false;//只要有一个选中，批量按妞就可以使用
        all.each(function (i) {
            if ($(all[i]).prop('checked')) {
                is_use = true;
            } else {
                is_all = false;
            }
        });
        if (is_all) {
            $('.goods-all').prop('checked', true);
        } else {
            $('.goods-all').prop('checked', false);
        }
        if (is_use) {
            $('.batch').addClass('is_use');
        } else {
            $('.batch').removeClass('is_use');
        }
    });
    $(document).on('click', '.batch', function () {
        var all = $('.goods-one');
        var is_all = true;//只要有一个没选中，全选按钮就不选中
        all.each(function (i) {
            if ($(all[i]).prop('checked')) {
                is_all = false;
            }
        });
        if (is_all) {
            $.myAlert({
                content: "请先勾选商品"
            });
        }
    });
    $(document).on('click', '.is_use', function () {
        var a = $(this);
        var goods_group = [];
        var all = $('.goods-one');
        all.each(function (i) {
            if ($(all[i]).prop('checked')) {
                var goods = {};
                goods.id = $(all[i]).val();
                goods.num = $(all[i]).data('num');
                goods_group.push(goods);
            }
        });
        $.myConfirm({
            content: a.data('content'),
            confirm: function () {
                $.myLoading();
                $.ajax({
                    url: a.data('url'),
                    type: 'get',
                    dataType: 'json',
                    data: {
                        goods_group: goods_group,
                        type: a.data('type'),
                    },
                    success: function (res) {
                        if (res.code == 0) {
                            window.location.reload();
                        } else {

                        }
                    },
                    complete: function () {
                        $.myLoadingHide();
                    }
                });
            }
        })
    });
</script>
<script>
    $(document).ready(function () {
        var clipboard = new Clipboard('.copy');
        clipboard.on('success', function (e) {
            $.myAlert({
                title: '提示',
                content: '复制成功'
            });
        });
        clipboard.on('error', function (e) {
            $.myAlert({
                title: '提示',
                content: '复制失败，请手动复制。链接为：' + e.text
            });
        });
    })
</script>