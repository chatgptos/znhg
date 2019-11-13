<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/6/19
 * Time: 16:52
 */
$urlManager = Yii::$app->urlManager;
$this->title = '用户中心';
$this->params['active_nav_group'] = 1;
?>
<style>
    .menu-header > div,
    .menu-item > div {
        width: 33.333333%;
    }

    .menu-item {
        background: #fff;
        margin: .5rem 0;
    }

    .menu-item > div {
        padding: .5rem .75rem;
    }

    .menu-item .drop-btn {
        display: inline-block;
        padding: .25rem;
    }

    .menu-item .drop-btn .iconfont {
        font-size: .75rem;
        color: #666;
        font-weight: bold;
        text-decoration: none;
    }

    .menu-item .drop-btn .iconfont:hover {
        font-size: .75rem;
        color: #333;
        font-weight: bold;
        text-decoration: none;
        cursor: move;
    }
</style>
<div class="panel mb-3">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <form class="auto-form" method="post">
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label">顶部背景图设置</label>
                </div>
                <div class="col-sm-6">
                    <div class="upload-group">
                        <div class="input-group">
                            <input class="form-control file-input" name="user_center_bg" value="<?= $user_center_bg ?>">
                            <span class="input-group-btn">
                            <a class="btn btn-secondary upload-file" href="javascript:" data-toggle="tooltip"
                               data-placement="bottom" title="上传文件">
                                <span class="iconfont icon-cloudupload"></span>
                            </a>
                        </span>
                            <span class="input-group-btn">
                            <a class="btn btn-secondary select-file" href="javascript:" data-toggle="tooltip"
                               data-placement="bottom" title="从文件库选择">
                                <span class="iconfont icon-viewmodule"></span>
                            </a>
                        </span>
                            <span class="input-group-btn">
                            <a class="btn btn-secondary delete-file" href="javascript:" data-toggle="tooltip"
                               data-placement="bottom" title="删除文件">
                                <span class="iconfont icon-close"></span>
                            </a>
                        </span>
                        </div>
                        <div class="upload-preview text-center upload-preview">
                            <span class="upload-preview-tip">750&times;268</span>
                            <img class="upload-preview-img" src="<?= $user_center_bg ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label">菜单设置</label>
                </div>
                <div class="col-sm-6">
                    <div style="background: #f6f8f9;padding: 1rem">
                        <div flex="dir:left" class="menu-header mb-2">
                            <div>菜单名称</div>
                            <div class="text-right">是否显示</div>
                            <div class="text-right">拖动排序</div>
                        </div>
                        <div class="menu-list" id="sortList">
                            <?php foreach ($menu_list as $i => $item): ?>
                                <div class="menu-item" flex="dir:left">
                                    <input type="hidden" name="model[<?= is_numeric($i) ? ('item_' . $i) : $i ?>][name]"
                                           value="<?= $item['name'] ?>">
                                    <input type="hidden" name="model[<?= is_numeric($i) ? ('item_' . $i) : $i ?>][icon]"
                                           value="<?= $item['icon'] ?>">
                                    <input type="hidden"
                                           name="model[<?= is_numeric($i) ? ('item_' . $i) : $i ?>][open_type]"
                                           value="<?= $item['open_type'] ?>">
                                    <input type="hidden" name="model[<?= is_numeric($i) ? ('item_' . $i) : $i ?>][url]"
                                           value="<?= $item['url'] ?>">
                                    <input type="hidden" name="model[<?= is_numeric($i) ? ('item_' . $i) : $i ?>][tel]"
                                           value="<?= $item['tel'] ?>">
                                    <div class="menu-name" flex="cross:center"><?= $item['name'] ?></div>
                                    <div class="text-right menu-switch" flex="cross:center main:right">
                                        <label class="custom-control custom-checkbox mb-0 mr-0">
                                            <input <?= $item['is_show'] == 1 ? 'checked' : null ?>
                                                    name="model[<?= is_numeric($i) ? ('item_' . $i) : $i ?>][is_show]"
                                                    type="checkbox"
                                                    value="1"
                                                    class="custom-control-input">
                                            <span class="custom-control-indicator"></span>
                                            <span class="custom-control-description"></span>
                                        </label>
                                    </div>
                                    <div class="text-right menu-drop" flex="cross:center main:right">
                                    <span class="drop-btn">
                                        <i class="iconfont icon-paixu"></i>
                                    </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary auto-form-btn" href="javascript:">保存</a>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="<?= Yii::$app->request->baseUrl ?>/statics/mch/js/Sortable.min.js"></script>
<script>

    Sortable.create(document.getElementById('sortList'), {
        animation: 250,
    }); // That's all.
</script>
