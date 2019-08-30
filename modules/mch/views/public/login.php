<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta content="IE=edge" http-equiv="X-UA-Compatible">
    <title>商城系统登录</title>
    <link href="<?= Yii::$app->request->baseUrl ?>/statics/mch/css/login/style.css" rel="stylesheet"/>
    <link href="<?= Yii::$app->request->baseUrl ?>/statics/mch/favicon.ico" rel="shortcut icon" type="image/x-icon"/>
</head>
<body class="page-login-v3">
<div class="container">
    <div id="wrapper" class="login-body">
        <div class="login-content">
            <div class="brand">
                <img alt="" class="brand-img"
                     src="data:image/png;base64,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"
                     width="50"/>
                <h2 class="brand-text">商城系统</h2>
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