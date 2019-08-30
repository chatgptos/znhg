<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/10/16
 * Time: 15:43
 */

namespace app\modules\api\controllers;


use app\modules\api\models\SeckillGoodsListForm;
use app\modules\api\models\SeckillListForm;

class SeckillController extends Controller
{
    //今日秒杀安排列表
    public function actionList()
    {
        $form = new SeckillListForm();
        $form->store_id = $this->store->id;
        $form->time = intval(date('H'));
        $form->date = date('Y-m-d');
        $this->renderJson($form->search());
    }

    //秒杀商品列表
    public function actionGoodsList()
    {
        $form = new SeckillGoodsListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $form->date = date('Y-m-d');
        $this->renderJson($form->search());
    }
}