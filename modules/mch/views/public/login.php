<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta content="IE=edge" http-equiv="X-UA-Compatible">
    <title>购值爽商城系统登录</title>
    <link href="<?= Yii::$app->request->baseUrl ?>/statics/mch/css/login/style.css" rel="stylesheet"/>
    <link href="<?= Yii::$app->request->baseUrl ?>/statics/mch/favicon.ico" rel="shortcut icon" type="image/x-icon"/>
</head>
<body class="page-login-v3">
<div class="container">
    <div id="wrapper" class="login-body">
        <div class="login-content">
            <div class="brand">
                <img alt="" class="brand-img"
               src="http://airent-hospital.oss-cn-beijing.aliyuncs.com/uploads/image/6e/6ebe3b328ed07f6ce3ef9117c4fa26a9.jpeg"
                width="50"/>
                <h2 class="brand-text">购值爽</h2>
            </div>
            <form id="login-form" class="login-form">
                <div class="form-group">
                    <input class="" name="LoginForm[user_name]" placeholder="请输入用户名" type="text" required/>
                </div>
                <div class="form-group">
                    <input class="" name="LoginForm[password]" placeholder="请输入密码" type="password" required/>
                </div>
                <div class="form-group">
                    <button id="btn-submit" type="submit">
                        登录
                    </button>
                </div>
                <input type="hidden" name="_csrf" id='csrf' value="<?= Yii::$app->request->csrfToken ?>">
            </form>
        </div>
    </div>
</div>
</body>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/mch/js/jquery.min.js"></script>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/mch/js/jquery.form.js"></script>
<script src="<?= Yii::$app->request->baseUrl ?>/statics/mch/vendor/layer/layer.js"></script>
<script>
    $(function () {
        // 表单提交
        var _form = $('#login-form');
        _form.submit(function () {
            var btn_submit = $('#btn-submit');
            btn_submit.attr("disabled", true);
            $(_form).ajaxSubmit({
                type: "post",
                dataType: "json",
                url: "<?= yii\helpers\Url::to(['public/login'])?>",
                success: function (result) {
                    btn_submit.attr("disabled", false);
                    if (result.code === 1) {
                        layer.msg(result.msg, {time: 1500, anim: 1}, function () {
                            window.location = result.data.url;
                        });
                        return true;
                    }
                    layer.msg(result.msg, {time: 1500, anim: 6});
                }
            });
            return false;
        });
    });
</script>
</html>