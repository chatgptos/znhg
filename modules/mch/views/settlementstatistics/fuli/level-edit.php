<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/2
 * Time: 9:27
 */
use \app\models\Level;

/* @var \app\models\Award $level */
$urlManager = Yii::$app->urlManager;
$this->title = '福利';
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
                       aria-controls="level-edit" aria-selected="true">福利期数设置</a>
                    <a class="nav-item nav-link" id="nav-content-edit" data-toggle="tab" href="#content-edit" role="tab"
                       aria-controls="content-edit" aria-selected="false">福利期数说明</a>
                </nav>

            </div>
            <div class="tab-content mt-4" id="nav-tabContent">
                <div class="tab-pane fade show active" id="level-edit" role="tabpanel" aria-labelledby="nav-level-edit">

                    <form method="post" class="auto-form" autocomplete="off"
                          return="<?= $urlManager->createUrl(['mch/settlementstatistics/fuli/level']) ?>">
                        <div class="form-body">
                            <input type="hidden" name="scene" value="edit">
                            <div class="form-group row">
                                <div class="col-2 text-right">
                                    <label class="col-form-label required">期数</label>
                                </div>
                                <div class="col-2">
                                    <select class="form-control" name="level">
                                        <?php for ($i = 0; $i <= 100; $i++): ?>
                                            <option
                                                value="<?= $i ?>" <?= ($level->level == $i) ? "selected" : "" ?>><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <div class="text-muted fs-sm">数字越大期数越小</div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="offset-2 col-5">
                                    <div class="text-muted fs-sm text-danger">福利满足条件期数</div>
                                    <div class="text-muted fs-sm text-danger">如需个别调整，请前往<a
                                            href="<?= $urlManager->createUrl(['mch/settlementstatistics/fuli/level']) ?>">福利列表</a>调整
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-2 text-right">
                                    <label class="col-form-label required">福利期数名称</label>
                                </div>
                                <div class="col-5">
                                    <input class="form-control" name="name" value="<?= $level->name ?>">
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-2 text-right">
                                    <label class="col-form-label required">每份奖励</label>
                                </div>
                                <div class="col-5">
                                    <div class="input-group">
                                        <span class="input-group-addon bg-white"></span>
                                        <input class="form-control" name="money" type="number"
                                               value="<?= $level->money ?>">
                                    </div>
                                    <div class="text-muted fs-sm">奖励积分（个）</div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-2 text-right">
                                    <label class="col-form-label required">总奖励</label>
                                </div>
                                <div class="col-5">
                                    <div class="input-group">
                                        <input class="form-control" name="all_money" value="<?= $level->all_money ?>">
                                        <span class="input-group-addon bg-white">元</span>
                                    </div>
                                    <div class="text-muted fs-sm">请输入1~10000000之间的数字</div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-2 text-right">
                                    <label class="col-form-label required">每份需要优惠券数量</label>
                                    <div class="text-muted fs-sm text-danger">张</div>
                                </div>
                                <div class="col-5">
                                    <div class="input-group">
                                        <input class="form-control" name="coupon_require" value="<?= $level->coupon_require ?>">
                                        <span class="input-group-addon bg-white">张</span>
                                    </div>
                                    <div class="text-muted fs-sm">请输入1~100000之间的数字</div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-2 text-right">
                                    <label class="col-form-label required">需要的等级</label>
                                    <div class="text-muted fs-sm text-danger">张</div>
                                </div>
                                <div class="col-5">
                                    <select class="form-control parent" name="require_level">
                                        <option value="">请选择等级</option>
                                        <?php foreach ($level_list as $value): ?>
                                            <option
                                                    value="<?= $value['id'] ?>" <?= $value['id'] == $level['require_level'] ? 'selected' : '' ?>><?= $value['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>


                            <div class="form-group row">
                                <div class="col-2 text-right">
                                    <label class="col-form-label required">总份数</label>
                                    <div class="text-muted fs-sm text-danger">总份数</div>
                                </div>
                                <div class="col-5">
                                    <div class="input-group">
                                        <input class="form-control" name="num" value="<?= $level->num ?>">
                                        <span class="input-group-addon bg-white">份数</span>
                                    </div>
                                    <div class="text-muted fs-sm">请输入1~100000之间的数字</div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-2 text-right">
                                    <label class="col-form-label required">福利分红日期</label>
                                </div>
                                <div class="col-5">
                                    <div class="input-group">
                                        <input class="form-control" id="date_end" value="<?= date('Y-m-d', $level->end_fulichi_time)?>" name="end_fulichi_time">
                                        <span class="input-group-addon bg-white"></span>
                                    </div>
                                    <div class="text-muted fs-sm">请输入日期</div>
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
                          return="<?= $urlManager->createUrl(['mch/settlementstatistics/fuli/level']) ?>">
                        <div class="form-body">
                            <div class="form-group row">
                                <div class="col-2 text-right required">
                                    <label class="col-form-label required">福利期数说明</label>
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



<script>

    $.datetimepicker.setLocale('zh');

    $(function () {
        $('#date_begin').datetimepicker({
            format: 'Y-m-d',
            onShow: function (ct) {
                this.setOptions({
                    maxDate: $('#date_end').val() ? $('#date_end').val() : false
                })
            },
            timepicker: false
        });
        $('#date_end').datetimepicker({
            format: 'Y-m-d',
            onShow: function (ct) {
                this.setOptions({
                    minDate: $('#date_begin').val() ? $('#date_begin').val() : false
                })
            },
            timepicker: false
        });
    });

</script>