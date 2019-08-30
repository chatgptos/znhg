<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/6/27
 * Time: 11:36
 */

$urlManager = Yii::$app->urlManager;
$this->title = '预约设置';
$this->params['active_nav_group'] = 10;
$this->params['is_book'] = 1;
?>
<div class="panel mb-3">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <form class="form auto-form" method="post" autocomplete="off"
              return="<?= $urlManager->createUrl(['mch/book/index/setting']) ?>">
            <div class="form-body">
                <div class="form-group row">
                    <div class="form-group-label col-3 text-right">
                        <label class=" col-form-label">是否显示分类</label>
                    </div>
                    <div class="col-9 col-form-label">
                        <label class="radio-label">
                            <input <?= $setting['cat'] == 1 ? 'checked' : 'checked' ?>
                                value="1" name="model[cat]" type="radio" class="custom-control-input">
                            <span class="label-icon"></span>
                            <span class="label-text">显示</span>
                        </label>
                        <label class="radio-label">
                            <input <?= $setting['cat'] == 0 ? 'checked' : null ?>
                                value="0" name="model[cat]" type="radio" class="custom-control-input">
                            <span class="label-icon"></span>
                            <span class="label-text">不显示</span>
                        </label>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="form-group-label col-3 text-right">
                    </div>
                    <div class="col-9">
                        <a class="btn btn-primary auto-form-btn" href="javascript:">保存</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
