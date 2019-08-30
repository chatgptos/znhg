<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2018/03/15
 * Time: 13:02
 */
$this->title = '账户设置';
?>
<div class="panel mb-3">
    <div class="panel-header">账户设置</div>
    <div class="panel-body">
        <form class="auto-form" method="post">
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label required">用户名</label>
                </div>
                <div class="col-sm-6">
                    <input class="form-control" value="<?= $model->user_name ?>" name="StoreUserForm[user_name]"/>
                </div>
            </div>

            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label">登录密码</label>
                </div>
                <div class="col-sm-6">
                    <input class="form-control" type="password" name="StoreUserForm[password]"/>
                </div>
            </div>

            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label">确认密码</label>
                </div>
                <div class="col-sm-6">
                    <input class="form-control" type="password" name="StoreUserForm[repassword]"/>
                </div>
            </div>

            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                </div>
                <div class="col-sm-6">
                    <input type="submit" class="btn btn-primary auto-form-btn" value="保存"/>
                </div>
            </div>
        </form>
    </div>
</div>