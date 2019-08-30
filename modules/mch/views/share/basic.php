<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/8
 * Time: 18:01
 */


/* @var $list \app\models\Setting */

/* @var $qrcode \app\models\Qrcode */

use yii\widgets\LinkPager;

$static = Yii::$app->request->baseUrl . '/statics';
$urlManager = Yii::$app->urlManager;
$this->title = '基础设置';
$this->params['active_nav_group'] = 5;
?>
<style>
    .help-block {
        display: block;
        margin-top: 5px;
        margin-bottom: 10px;
        color: #737373;
    }

    .short-row {
        max-width: 450px;
    }
</style>
<div class="panel mb-3">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <form class="form auto-form" method="post" autocomplete="off">
            <div class="form-body">
                <div class="form-group row">
                    <div class="col-3 text-right">
                        <label class=" col-form-label required">分销层级</label>
                    </div>
                    <div class="col-9">
                        <label class="radio-label">
                            <input type="radio" name="model[level]"
                                   value="0" <?= ($list->level == 0) ? "checked" : "" ?>>
                            <span class="label-icon"></span>
                            <span class="label-text">不开启</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="model[level]"
                                   value="1" <?= ($list->level == 1) ? "checked" : "" ?>>
                            <span class="label-icon"></span>
                            <span class="label-text">一级分销</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="model[level]"
                                   value="2" <?= ($list->level == 2) ? "checked" : "" ?>>
                            <span class="label-icon"></span>
                            <span class="label-text">二级分销</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="model[level]"
                                   value="3" <?= ($list->level == 3) ? "checked" : "" ?>>
                            <span class="label-icon"></span>
                            <span class="label-text">三级分销</span>
                        </label>
                    </div>
                </div>

                <div class="form-group row" style="border-bottom: 1px #ccc dashed">
                    <div class="col-3 text-right">
                        <label class=" col-form-label">上下线关系设置</label>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-3 text-right">
                        <label class=" col-form-label required">成为下线条件</label>
                    </div>
                    <div class="col-9">
                        <div>
                            <label class="radio-label">
                                <input type="radio" name="model[condition]"
                                       value="0" <?= ($list->condition == 0) ? "checked" : "" ?>>
                                <span class="label-icon"></span>
                                <span class="label-text">首次点击链接</span>
                            </label>
                            <!--
                            <label class="radio-label">
                                <input type="radio" name="model[condition]"
                                                              value="1" <?= ($list->condition == 1) ? "checked" : "" ?>>
                                <span class="label-icon"></span>
                                <span class="label-text">首次下单</span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="model[condition]"
                                                              value="2" <?= ($list->condition == 2) ? "checked" : "" ?>>
                                <span class="label-icon"></span>
                                <span class="label-text">首次付款</span>
                            </label>
                            -->
                        </div>
                        <div class="help-block" hidden>首次点击分享链接： 可以自由设置分销商条件</div>
                    </div>
                </div>
                <div class="form-group row" style="border-bottom: 1px #ccc dashed">
                    <div class="col-3 text-right">
                        <label class=" col-form-label">分销资格设置</label>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-3 text-right">
                        <label class=" col-form-label required">成为分销商条件</label>
                    </div>
                    <div class="col-9">
                        <div>
                            <label class="radio-label">
                                <input type="radio" name="model[share_condition]"
                                       value="0" <?= ($list->share_condition == 0) ? "checked" : "" ?>>
                                <span class="label-icon"></span>
                                <span class="label-text">无条件（需要审核）</span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="model[share_condition]"
                                       value="1" <?= ($list->share_condition == 1) ? "checked" : "" ?>>
                                <span class="label-icon"></span>
                                <span class="label-text">申请（需要审核）</span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="model[share_condition]"
                                       value="2" <?= ($list->share_condition == 2) ? "checked" : "" ?>>
                                <span class="label-icon"></span>
                                <span class="label-text">无需审核</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-3 text-right">
                        <label class=" col-form-label">推广海报图</label>
                    </div>
                    <div class="col-9">
                        <a href="<?= $urlManager->createUrl(['mch/share/qrcode']) ?>"
                           class="btn btn-sm btn-primary">设置</a>

                    </div>
                </div>
                <div class="form-group row" style="border-bottom: 1px #ccc dashed">
                    <div class="col-3 text-right">
                        <label class=" col-form-label">分销佣金</label>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-3 text-right">
                        <label class="col-form-label required">提现方式</label>
                    </div>
                    <div class="col-9">
                        <div>
                            <label class="checkbox-label">
                                <input type="checkbox" name="model[pay_type][wechat]" value="0"
                                    <?= ($list->pay_type == 2 || $list->pay_type == 0) ? "checked" : "" ?>>
                                <span class="label-icon"></span>
                                <span class="label-text">微信支付</span>
                            </label>

                            <label class="checkbox-label">
                                <input type="checkbox" name="model[pay_type][alipay]" value="1"
                                    <?= ($list->pay_type == 2 || $list->pay_type == 1) ? "checked" : "" ?>>
                                <span class="label-icon"></span>
                                <span class="label-text">支付宝支付</span>
                            </label>

                            <label class="checkbox-label">
                                <input type="checkbox" name="model[pay_type][bank]" value="2"
                                    <? // $list->bank == 1) ? "checked" : "" ?>>
                                <span class="label-icon"></span>
                                <span class="label-text">银行卡支付</span>
                            </label>

                        </div>
                        <div>
                            <label class="col-form-label">微信自动支付，需要申请微信支付的企业付款到零钱功能</label>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-3 text-right">
                        <label class=" col-form-label required">最少提现额度</label>
                    </div>
                    <div class="col-9">
                        <div class="input-group short-row">
                            <input class="form-control" name="model[min_money]"
                                   value="<?= $list->min_money ? $list->min_money : 1 ?>">
                            <span class="input-group-addon">元</span>
                        </div>
                    </div>
                </div>


                <div class="form-group row">
                    <div class="col-3 text-right">
                        <label class=" col-form-label required">每日提现上限</label>
                    </div>
                    <div class="col-9">
                        <div class="input-group short-row">
                            <input type="number" min="0" step="0.01" class="form-control" name="model[cash_max_day]"
                                   value="<?= $option['cash_max_day'] ? $option['cash_max_day'] : 0 ?>">
                            <span class="input-group-addon">元</span>
                        </div>
                        <div class="text-muted fs-sm">0元表示不限制每日提现金额</div>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-3 text-right">
                        <label class="col-form-label required">消费自动成为分销商</label>
                    </div>
                    <div class="col-9">
                        <div class="input-group short-row">
                            <input type="number" min="0" step="0.01" class="form-control" name="model[auto_share_val]"
                                   value="<?= $option['auto_share_val'] ? $option['auto_share_val'] : 0 ?>">
                            <span class="input-group-addon">元</span>
                        </div>
                        <div class="text-muted fs-sm">消费满指定金额自动成为分销商，0元表示不自动</div>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-3 text-right">
                        <label class=" col-form-label">用户须知</label>
                    </div>
                    <div class="col-9">
                    <textarea class="form-control short-row" name="model[content]"
                              style="min-height: 150px;"><?= $list->content ?></textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-3 text-right">
                        <label class=" col-form-label">提现到账通知模板消息id</label>
                    </div>
                    <div class="col-9">
                        <input class="form-control short-row" value="<?= $tpl_msg['cash_success_tpl'] ?>"
                               name="model[cash_success_tpl]">
                        <div class="fs-sm text-muted">
                            <span>提现转账处理完成后向用户发送消息，</span>
                            <a href="javascript:" class="fs-sm" data-toggle="modal"
                               data-target="#cash_success_tpl">如何获取提现到账通知模板消息id</a>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-3 text-right">
                        <label class=" col-form-label">提现失败通知模板消息id</label>
                    </div>
                    <div class="col-9">
                        <input class="form-control short-row" value="<?= $tpl_msg['cash_fail_tpl'] ?>" name="model[cash_fail_tpl]">
                        <div class="fs-sm text-muted">
                            <span>提现失败向用户发送消息，</span>
                            <a href="javascript:" class="fs-sm" data-toggle="modal"
                               data-target="#cash_fail_tpl">如何获取提现失败通知模板消息id</a>
                        </div>
                    </div>
                </div>

                <div class="form-group row" style="border-bottom: 1px #ccc dashed">
                    <div class="col-3 text-right">
                        <label class=" col-form-label">分销协议</label>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-3 text-right">
                        <label class=" col-form-label">申请协议</label>
                    </div>
                    <div class="col-9">
                    <textarea class="form-control short-row" name="model[agree]"
                              style="min-height: 150px;"><?= $list->agree ?></textarea>
                    </div>
                </div>
                <div class="form-group row" style="border-bottom: 1px #ccc dashed">
                    <div class="col-3 text-right">
                        <label class=" col-form-label">背景图片</label>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-3 text-right">
                        <label class="col-form-label">申请页面</label>
                    </div>
                    <div class="col-9">
                        <div class="upload-group short-row">
                            <div class="input-group">
                                <input class="form-control file-input" name="model[pic_url_1]"
                                       value="<?= $list['pic_url_1'] ?>">
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
                                <span class="upload-preview-tip">750&times;300</span>
                                <img class="upload-preview-img" src="<?= $list['pic_url_1'] ?>">
                            </div>
                        </div>

                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-3 text-right">
                        <label class="col-form-label">待审核页面</label>
                    </div>
                    <div class="col-9">
                        <div class="upload-group short-row">
                            <div class="input-group">
                                <input class="form-control file-input" name="model[pic_url_2]"
                                       value="<?= $list['pic_url_2'] ?>">
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
                                <span class="upload-preview-tip">750&times;300</span>
                                <img class="upload-preview-img" src="<?= $list['pic_url_2'] ?>">
                            </div>
                        </div>

                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-3 text-right">
                    </div>
                    <div class="col-9">
                        <a class="btn btn-primary auto-form-btn" href="javascript:">保存</a>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="cash_success_tpl" data-backdrop="static" tabindex="-1" role="dialog"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">如何获取提现到账通知模板消息id</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ol class="pl-3">
                    <li>
                        <div>进入微信小程序官方后台，找到模板库</div>
                        <div style="text-align: center">
                            <img style="max-width: 100%"
                                 src="<?= Yii::$app->request->baseUrl ?>/statics/images/tplmsg/0.png">
                        </div>
                    </li>
                    <li>
                        <div>查找指定模板，点击选用</div>
                        <div style="text-align: center">
                            <img style="max-width: 100%"
                                 src="<?= Yii::$app->request->baseUrl ?>/statics/images/tplmsg/cash_success_tpl/1.png">
                        </div>
                    </li>
                    <li>
                        <div>选择下图关键词，并按下图调好顺序；点击提交</div>
                        <div style="text-align: center">
                            <img style="max-width: 100%"
                                 src="<?= Yii::$app->request->baseUrl ?>/statics/images/tplmsg/cash_success_tpl/2.png">
                        </div>
                    </li>
                    <li>
                        <div>复制模板ID</div>
                        <div style="text-align: center">
                            <img style="max-width: 100%"
                                 src="<?= Yii::$app->request->baseUrl ?>/statics/images/tplmsg/cash_success_tpl/3.png">
                        </div>
                    </li>
                </ol>
            </div>
            <div class="modal-footer">
                <a href="javascript:" class="btn btn-secondary" data-dismiss="modal">关闭</a>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="cash_fail_tpl" data-backdrop="static" tabindex="-1" role="dialog"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">如何获取提现到账通知模板消息id</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ol class="pl-3">
                    <li>
                        <div>进入微信小程序官方后台，找到模板库</div>
                        <div style="text-align: center">
                            <img style="max-width: 100%"
                                 src="<?= Yii::$app->request->baseUrl ?>/statics/images/tplmsg/0.png">
                        </div>
                    </li>
                    <li>
                        <div>查找指定模板，点击选用</div>
                        <div style="text-align: center">
                            <img style="max-width: 100%"
                                 src="<?= Yii::$app->request->baseUrl ?>/statics/images/tplmsg/cash_fail_tpl/1.png">
                        </div>
                    </li>
                    <li>
                        <div>选择下图关键词，并按下图调好顺序；点击提交</div>
                        <div style="text-align: center">
                            <img style="max-width: 100%"
                                 src="<?= Yii::$app->request->baseUrl ?>/statics/images/tplmsg/cash_fail_tpl/2.png">
                        </div>
                    </li>
                    <li>
                        <div>复制模板ID</div>
                        <div style="text-align: center">
                            <img style="max-width: 100%"
                                 src="<?= Yii::$app->request->baseUrl ?>/statics/images/tplmsg/cash_fail_tpl/3.png">
                        </div>
                    </li>
                </ol>
            </div>
            <div class="modal-footer">
                <a href="javascript:" class="btn btn-secondary" data-dismiss="modal">关闭</a>
            </div>
        </div>
    </div>
</div>