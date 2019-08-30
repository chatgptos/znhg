<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/6/27
 * Time: 1:06
 */

namespace app\modules\api\controllers;


use app\modules\api\behaviors\LoginBehavior;
use app\modules\api\models\AddCartForm;
use app\modules\api\models\CartDeleteForm;
use app\modules\api\models\CartListForm;
use app\modules\api\models\OrderSubmitPreviewForm;

class CartController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginBehavior::className(),
            ],
        ]);
    }

    public function actionList()
    {
        $form = new CartListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;
        $this->renderJson($form->search());
    }

    public function actionAddCart()
    {
        if (\Yii::$app->request->isPost) {
            $form = new AddCartForm();
            $form->attributes = \Yii::$app->request->post();
            $form->store_id = $this->store->id;
            $form->user_id = \Yii::$app->user->id;
            $this->renderJson($form->save());
        }
    }

    public function actionDelete()
    {
        $form = new CartDeleteForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;
        $this->renderJson($form->save());
    }
}