<?php

namespace app\modules\mch\controllers;

use Yii;
use yii\helpers\Url;
use app\modules\mch\models\LoginForm;

/**
 * 公共模块
 * Public controller for the `mch` module
 */
class PublicController extends Controller
{
    /**
     * 首页
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->actionLogin();
    }

    /**
     * 后台登录
     */
    public function actionLogin()
    {
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->login()) {
                $data = ['code' => 1, 'msg' => '登录成功', 'data' => ['url' => Url::to(['store/index'])]];
            } else {
                $data = ['code' => -1, 'msg' => '登录失败，请检查账号密码是否正确'];
            }
            return $this->renderJson($data);
        } else {
            return $this->renderPartial('login');
        }
    }

    /**
     * 注销登录
     */
    public function actionLogout()
    {
        Yii::$app->store->logout();
        $this->redirect(['public/login']);
    }

}
