<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/2
 * Time: 9:27
 */
use \app\models\Level;

/* @var \app\models\Level $level */
$urlManager = Yii::$app->urlManager;
$this->title = '会员设置';
$this->params['active_nav_group'] = 4;
?>
<div class="panel mb-3">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <div class="form bg-white">
            <div class="form-title" style="border: 0;">
                <nav class="nav nav-tabs" id="myTab" role="tablist">
                    <a class="nav-item nav-link active" id="nav-level-edit" data-toggle="tab" href="#level-edit"
                       role="tab"
                       aria-controls="level-edit" aria-selected="true">会员等级设置</a>
                    <a class="nav-item nav-link" id="nav-content-edit" data-toggle="tab" href="#content-edit" role="tab"
                       aria-controls="content-edit" aria-selected="false">会员等级说明</a>
                </nav>

            </div>
            <div class="tab-content mt-4" id="nav-tabContent">
                <div class="tab-pane fade show active" id="level-edit" role="tabpanel" aria-labelledby="nav-level-edit">

                    <form method="post" class="auto-form" autocomplete="off"
                          return="<?= $urlManager->createUrl(['mch/user/level']) ?>">
                        <div class="form-body">
                            <input type="hidden" name="scene" value="edit">
                            <div class="form-group row">
                                <div class="col-2 text-right">
                                    <label class="col-form-label required">等级</label>
                                </div>
                                <div class="col-2">
                                    <select class="form-control" name="level">
                                        <?php for ($i = 0; $i <= 100; $i++): ?>
                                            <option
                                                value="<?= $i ?>" <?= ($level->level == $i) ? "selected" : "" ?>><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <div class="text-muted fs-sm">数字越大等级越高</div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="offset-2 col-5">
                                    <div class="text-muted fs-sm text-danger">会员满足条件等级从低到高自动升级，高等级不会自动降级</div>
                                    <div class="text-muted fs-sm text-danger">如需个别调整，请前往<a
                                            href="<?= $urlManager->createUrl(['mch/user/index']) ?>">会员列表</a>调整
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-2 text-right">
                                    <label class="col-form-label required">等级名称</label>
                                </div>
                                <div class="col-5">
                                    <input class="form-control" name="name" value="<?= $level->name ?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-2 text-right">
                                    <label class="col-form-label required">升级条件</label>
                                </div>
                                <div class="col-5">
                                    <div class="input-group">
                                        <span class="input-group-addon bg-white">累计完成订单金额满</span>
                                        <input class="form-control" name="money" type="number"
                                               value="<?= $level->money ?>">
                                        <span class="input-group-addon bg-white">元</span>
                                    </div>
                                    <div class="text-muted fs-sm">会员升级条件</div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-2 text-right">
                                    <label class="col-form-label required">折扣</label>
                                </div>
                                <div class="col-5">
                                    <div class="input-group">
                                        <input class="form-control" name="discount" value="<?= $level->discount ?>">
                                        <span class="input-group-addon bg-white">折</span>
                                    </div>
                                    <div class="text-muted fs-sm">请输入0.1~10之间的数字</div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-2 text-right">
                                    <label class="col-form-label required">状态</label>
                                </div>
                                <div class="col-5">
                                    <div class="pt-1">
                                        <label class="custom-control custom-radio">
                                            <input id="radio1"
                                                   value="1" <?= $level->status == 1 ? "checked" : "" ?>
                                                   name="status" type="radio" class="custom-control-input">
                                            <span class="custom-control-indicator"></span>
                                            <span class="custom-control-description">启用</span>
                                        </label>
                                        <label class="custom-control custom-radio">
                                            <input id="radio2"
                                                   value="0" <?= $level->status == 0 ? "checked" : "" ?>
                                                   name="status" type="radio" class="custom-control-input">
                                            <span class="custom-control-indicator"></span>
                                            <span class="custom-control-description">禁用</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-2 text-right">
                                </div>
                                <div class="col-5">
                                    <a class="btn btn-primary auto-form-btn" href="javascript:">保存</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="tab-pane fade show" id="content-edit" role="tabpanel" aria-labelledby="nav-content-edit">
                    <form method="post" class="auto-form" autocomplete="off"
                          return="<?= $urlManager->createUrl(['mch/user/level']) ?>">
                        <div class="form-body">
                            <div class="form-group row">
                                <div class="col-2 text-right required">
                                    <label class="col-form-label required">会员等级说明</label>
                                </div>
                                <div class="col-5">
                                    <textarea class="form-control" name="content"
                                              style="min-height: 200px;"><?= $store->member_content ?></textarea>
                                </div>
                                <input type="hidden" name="scene" value="content">
                            </div>
                            <div class="form-group row">
                                <div class="col-2 text-right">
                                </div>
                                <div class="col-5">
                                    <a class="btn btn-primary auto-form-btn" href="javascript:">保存</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>