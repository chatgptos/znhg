<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/3
 * Time: 13:42
 */
use \app\models\User;
use \app\models\Level;

/* @var \app\models\User $user */
/* @var \app\models\Level[] $level */
$urlManager = Yii::$app->urlManager;
$this->title = '会员编辑';
$this->params['active_nav_group'] = 4;
?>
<div class="panel mb-3">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <div class="">
            <form method="post" class="form auto-form" autocomplete="off"
                  return="<?= $urlManager->createUrl(['mch/user/index']) ?>">
                <div class="form-body">
                    <div class="form-group row">
                        <div class="form-group-label col-2 text-right">
                            <label class="col-form-label">会员</label>
                        </div>
                        <div class="col-5">
                            <div>
                                <img src="<?= $user->avatar_url ?>" style="width: 50px;height:50px;">
                                <span><?= $user->nickname ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="form-group-label col-2 text-right">
                            <label class="col-form-label required">会员等级</label>
                        </div>
                        <div class="col-2">
                            <select class="form-control" name="level">
                                <option value="-1" <?= $user->level == -1 ? "selected" : "" ?>>普通用户</option>
                                <?php foreach ($level as $value): ?>
                                    <option
                                        value="<?= $value->level ?>" <?= ($value->level == $user->level) ? "selected" : "" ?>><?= $value->name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="form-group-label col-2 text-right">
                            <label class="col-form-label">注册时间</label>
                        </div>
                        <div class="col-5">
                            <label class="col-form-label"><?= date('Y-m-d H:i', $user->addtime); ?></label>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="form-group-label col-2 text-right">
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
