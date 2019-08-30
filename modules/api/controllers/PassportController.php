<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/6/24
 * Time: 22:31
 */

namespace app\modules\api\controllers;


use app\models\User;
use app\modules\api\models\LoginForm;

class PassportController extends Controller
{
    public function actionLogin()
    {
        $form = new LoginForm();
        $form->attributes = \Yii::$app->request->post();
        $form->wechat_app = $this->wechat_app;
        $form->store_id = $this->store->id;
        return $this->renderJson($form->login());
    }
}