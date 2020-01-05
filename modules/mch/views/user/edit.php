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
<style>

    .user-list .user-item {
        text-align: center;
        width: 120px;
        border: 1px solid #e3e3e3;
        padding: 1rem 0;
        cursor: pointer;
        display: inline-block;
        vertical-align: top;
        margin: 0 1rem 1rem 0;
        border-radius: .15rem;
    }

    .user-list .user-item:hover {
        background: rgba(238, 238, 238, 0.54);
    }

    .user-list .user-item img {
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 999px;
        margin-bottom: 1rem;
    }

    .user-list .user-item.active {
        background: rgba(2, 117, 216, 0.69);
        color: #fff;
    }
</style>
<div class="panel mb-3"  id="app">
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
                        <div class="form-group-label col-sm-2 text-right">
                            <label class="col-form-label">上级经销商</label>
                        </div>
                        <div class="col-sm-6">
                            <select class="form-control " >
                                <option value="0">无</option>
                                <?php foreach ($parent_list as $value): ?>
                                    <?php if($user->parent_id == $value['id']):?>
                                        <option  selected><?= $value->nickname ?></option>
                                    <?php endif;?>

                                 <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="form-group-label col-3 text-right">
                            <label class="">发放对象</label>
                        </div>
                        <div class="col-9">
                            <div class="input-group mb-3" style="max-width: 250px">
                                <input class="form-control search-user-keyword" placeholder="昵称"
                                       onkeydown="if(event.keyCode==13) {search_user();return false;}">
                                <span class="input-group-btn">
                            <a class="btn btn-secondary search-user-btn" onclick="search_user()"
                               href="javascript:">查找用户</a>
                        </span>
                            </div>
                            <div class="user-list">
                                <div v-if="user_list">
                                    <label class="user-item" v-for="(user,index) in user_list">
                                        <img v-bind:src="user.avatar_url">
                                        <input v-bind:value="user.id" type="checkbox" name="parent_id"
                                               style="display: none">
                                        <div style="white-space: nowrap;text-overflow: ellipsis;overflow: hidden">
                                            {{user.nickname}}
                                        </div>
                                    </label>
                                </div>
                                <div v-else style="color: #ddd;">请输入昵称查找用户</div>
                            </div>
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

<script>

    var app = new Vue({
        el: "#app",
        data: {
            user_list: false,
        }
    });

    $(document).on("change", "input[name=expire_type]", function () {
        $(".expire-type").hide();
        $(".expire-type-" + this.value).show();
    });
    $(document).on("change", "input[name='parent_id']", function () {
        console.log($(this).parents("label"));
        if ($(this).prop("checked")) {
            $(this).parents("label").addClass("active");
        } else {
            $(this).parents("label").removeClass("active");
        }
    });



    function search_user() {
        var btn = $(".search-user-btn");
        var keyword = $(".search-user-keyword").val();
        btn.btnLoading("正在查找");
        $.ajax({
            url: "<?=$urlManager->createUrl(['mch/coupon/search-user'])?>",
            dataType: "json",
            data: {
                keyword: keyword,
            },
            success: function (res) {
                btn.btnReset();
                if (res.code == 0) {
                    app.user_list = res.data.list;
                }
            }
        });
    }


</script>