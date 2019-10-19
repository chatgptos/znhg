<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/6/19
 * Time: 16:52
 */
$urlManager = Yii::$app->urlManager;
$this->title = '集市开放时间';
$this->params['active_nav_group'] = 10;
?>

<div class="alert alert-info rounded-0">
    <div>注：设置了开放时间，小程序端才有相关开放集市时间点出现</div>
    <div>注：还可以在自定义菜单手动关闭一级入口</div>
    <div>注：开放集市入口可以在
        <a target="_blank" href="<?= $urlManager->createUrl(['mch/store/home-nav']) ?>">导航图标</a>、
        <a target="_blank"
           href="<?= $urlManager->createUrl(['mch/store/home-block']) ?>">图片魔方</a>、
        <a target="_blank" href="<?= $urlManager->createUrl(['mch/store/slide']) ?>">轮播图</a>设置。
    </div>
</div>
<div class="panel mb-3">
    <div class="panel-header">
        <span><?= $this->title ?></span>
    </div>
    <div class="panel-body">
        <form class="auto-form" method="post" autocomplete="off">
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label">开放时间</label>
                </div>
                <div class="col-sm-6">
                    <?php $model->open_time = json_decode($model->open_time, true); ?>
                    <?php for ($i = 0; $i < 24; $i++): ?>
                        <label class="custom-control custom-checkbox">
                            <input name="open_time[]"<?= is_array($model->open_time) && in_array($i, $model->open_time) ? 'checked' : null ?>
                                   value="<?= $i ?>"
                                   type="checkbox" class="custom-control-input">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description"><?= $i < 10 ? '0' . $i : $i ?>
                                :00~<?= $i < 10 ? '0' . $i : $i ?>:59</span>
                        </label>
                    <?php endfor; ?>
                </div>
            </div>



            <div class="form-group row">
                <div class="col-3 text-right">
                    <label class=" col-form-label required">积分对欢乐豆是否打开</label>
                </div>
                <div class="col-3">
                    <div class="input-group short-row">
                        <label class="radio-label">
                            <input id="radio1"
                                   value="0" <?=$model->is_jftohld == 0?"checked":""?>
                                   name="model[is_jftohld]" type="radio" class="custom-control-input">
                            <span class="label-icon"></span>
                            <span class="label-text">否</span>
                        </label>
                        <label class="radio-label">
                            <input id="radio2"
                                   value="1" <?=$model->is_jftohld == 1?"checked":""?>
                                   name="model[is_jftohld]" type="radio" class="custom-control-input">
                            <span class="label-icon"></span>
                            <span class="label-text">是</span>
                        </label>
                    </div>
                </div>
            </div>


            <div class="form-group row">
                <div class="col-3 text-right">
                    <label class=" col-form-label required">积分对几个欢乐豆</label>
                </div>
                <div class="col-3">
                    <div class="input-group short-row">
                        <input class="form-control" name="model[jftohld]"
                               value="<?= $model->jftohld ? $model->jftohld : 0 ?>">
                        <span class="input-group-addon">个</span>
                    </div>
                </div>
            </div>



            <div class="form-group row">
                <div class="col-3 text-right">
                    <label class=" col-form-label required">欢乐豆对积分是否打开</label>
                </div>
                <div class="col-3">
                    <div class="input-group short-row">
                        <label class="radio-label">
                            <input id="radio1"
                                   value="0" <?=$model->is_hldtojf == 0?"checked":""?>
                                   name="model[is_hldtojf]" type="radio" class="custom-control-input">
                            <span class="label-icon"></span>
                            <span class="label-text">否</span>
                        </label>
                        <label class="radio-label">
                            <input id="radio2"
                                   value="1" <?=$model->is_hldtojf == 1?"checked":""?>
                                   name="model[is_hldtojf]" type="radio" class="custom-control-input">
                            <span class="label-icon"></span>
                            <span class="label-text">是</span>
                        </label>
                    </div>
                </div>
            </div>


            <div class="form-group row">
                <div class="col-3 text-right">
                    <label class=" col-form-label required">欢乐豆对几个积分</label>
                </div>
                <div class="col-3">
                    <div class="input-group short-row">
                        <input class="form-control" name="model[hldtojf]"
                               value="<?= $model->hldtojf ? $model->hldtojf : 0 ?>">
                        <span class="input-group-addon">个</span>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-3 text-right">
                    <label class=" col-form-label required">欢乐豆对优惠券是否打开(买)</label>
                </div>
                <div class="col-3">
                    <div class="input-group short-row">
                        <label class="radio-label">
                            <input id="radio1"
                                   value="0" <?=$model->is_hldtoyhq == 0?"checked":""?>
                                   name="model[is_hldtoyhq]" type="radio" class="custom-control-input">
                            <span class="label-icon"></span>
                            <span class="label-text">否</span>
                        </label>
                        <label class="radio-label">
                            <input id="radio2"
                                   value="1" <?=$model->is_hldtoyhq == 1?"checked":""?>
                                   name="model[is_hldtoyhq]" type="radio" class="custom-control-input">
                            <span class="label-icon"></span>
                            <span class="label-text">是</span>
                        </label>
                    </div>
                </div>
            </div>



            <div class="form-group row">
                <div class="col-3 text-right">
                    <label class=" col-form-label required">优惠券对欢乐豆是否打开（卖）</label>
                </div>
                <div class="col-3">
                    <div class="input-group short-row">
                        <label class="radio-label">
                            <input id="radio1"
                                   value="0" <?=$model->is_yhqtohld == 0?"checked":""?>
                                   name="model[is_yhqtohld]" type="radio" class="custom-control-input">
                            <span class="label-icon"></span>
                            <span class="label-text">否</span>
                        </label>
                        <label class="radio-label">
                            <input id="radio2"
                                   value="1" <?=$model->is_yhqtohld == 1?"checked":""?>
                                   name="model[is_yhqtohld]" type="radio" class="custom-control-input">
                            <span class="label-icon"></span>
                            <span class="label-text">是</span>
                        </label>
                    </div>
                </div>
            </div>


            <div class="form-group row">
                <div class="col-3 text-right">
                    <label class=" col-form-label required">几个欢乐豆对一张优惠券（买/卖）</label>
                </div>
                <div class="col-3">
                    <div class="input-group short-row">
                        <input class="form-control" name="model[hldtoyhq]"
                               value="<?= $model->hldtoyhq ? $model->hldtoyhq : 0 ?>">
                        <span class="input-group-addon">个</span>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-3 text-right">
                    <label class=" col-form-label required">买方系统赠送张数</label>
                </div>
                <div class="col-3">
                    <div class="input-group short-row">
                        <input class="form-control" name="model[xtjl]"
                               value="<?= $model->xtjl ? $model->xtjl : 0 ?>">
                        <span class="input-group-addon">张</span>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-3 text-right">
                    <label class=" col-form-label required">卖方系统赠送张数</label>
                </div>
                <div class="col-3">
                    <div class="input-group short-row">
                        <input class="form-control" name="model[xtjlsell]"
                               value="<?= $model->xtjlsell ? $model->xtjlsell : 0 ?>">
                        <span class="input-group-addon">张</span>
                    </div>
                </div>
            </div>




            <div class="form-group row">
                <div class="col-3 text-right">
                    <label class=" col-form-label required">手续费（兑换一张券）</label>
                </div>
                <div class="col-3">
                    <div class="input-group short-row">
                        <input class="form-control" name="model[charge]"
                               value="<?= $model->charge ? $model->charge : 0 ?>">
                        <span class="input-group-addon">个欢乐豆</span>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-3 text-right">
                    <label class=" col-form-label required">百分比手续费2级</label>
                </div>
                <div class="col-3">
                    <div class="input-group short-row">
                        <input class="form-control" name="model[charge1]"
                               value="<?= $model->charge1 ? $model->charge1 : 0 ?>">
                        <span class="input-group-addon">%个欢乐豆</span>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-3 text-right">
                    <label class=" col-form-label required">百分比手续费3级</label>
                </div>
                <div class="col-3">
                    <div class="input-group short-row">
                        <input class="form-control" name="model[charge2]"
                               value="<?= $model->charge2 ? $model->charge2 : 0 ?>">
                        <span class="input-group-addon">%个欢乐豆</span>
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
