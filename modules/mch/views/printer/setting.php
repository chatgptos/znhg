<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/12/1
 * Time: 16:16
 */

use yii\widgets\LinkPager;
use \app\models\Option;

/* @var \app\models\Printer[] $list */
/* @var \app\models\PrinterSetting $model */

$urlManager = Yii::$app->urlManager;
$this->title = '打印设置';
$this->params['active_nav_group'] = 13;
?>
<div class="panel mb-3">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <form class="auto-form" method="post">
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label">选择打印机</label>
                </div>
                <div class="col-sm-6">
                    <select class="form-control" name="printer_id">
                        <?php foreach ($list as $index => $value): ?>
                            <option value="<?= $value->id ?>" <?= $model->printer_id == $value['id'] ? "selected" : "" ?>><?= $value->name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label">订单打印方式</label>
                </div>
                <div class="col-sm-6">
                    <label class="checkbox-label">
                        <input id="radio1"
                               value="1" <?= $model->type['order'] == 1 ? "checked" : "" ?>
                               name="type[order]" type="checkbox" class="custom-control-input">
                        <span class="label-icon"></span>
                        <span class="label-text">下单打印</span>
                    </label>
                    <label class="checkbox-label">
                        <input id="radio2"
                               value="1" <?= $model->type['pay'] == 1 ? "checked" : "" ?>
                               name="type[pay]" type="checkbox" class="custom-control-input">
                        <span class="label-icon"></span>
                        <span class="label-text">付款打印</span>
                    </label>
                    <label class="checkbox-label">
                        <input id="radio2"
                               value="1" <?= $model->type['confirm'] == 1 ? "checked" : "" ?>
                               name="type[confirm]" type="checkbox" class="custom-control-input">
                        <span class="label-icon"></span>
                        <span class="label-text">确认收货打印</span>
                    </label>
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
