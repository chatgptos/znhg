<?php

namespace app\modules\mch\controllers;

use app\models\StoreUser;
use Yii;
use app\modules\mch\models\StoreUserForm;

/**
 * 商城后台账户
 * Class AccountController
 * @package app\modules\mch\controllers
 */
class AccountController extends Controller
{
    /**
     * 账户设置
     * @return array|bool|string
     * @throws \yii\base\Exception
     */
    public function actionIndex()
    {
        $identity = Yii::$app->store->identity;
        if (Yii::$app->request->isPost) {
            $form = new StoreUserForm;
            $form->user_id = $identity->user_id;
            return $this->renderJson($form->update(Yii::$app->request->post()));
        } else {
            return $this->render('index', ['model' => $identity]);
        }
    }
}