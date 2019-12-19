<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2018/1/6
 * Time: 11:50
 */

namespace app\modules\mch\controllers\settlementstatistics;


use app\models\Cat;
use app\models\User;
use app\modules\mch\models\settlementstatistics\DataGoodsForm;

class DataController extends Controller
{
    public function actionGoods()
    {
        $form = new DataGoodsForm();
        $form->store_id = $this->store->id;
        $form->attributes = \Yii::$app->request->get();
        $arr = $form->search();
        return $this->render('goods',[
            'list'=>$arr['list'],
            'pagination'=>$arr['pagination'],
            'row_count'=>$arr['row_count']
        ]);
    }
    public function actionUser()
    {
        $form = new DataGoodsForm();
        $form->store_id = $this->store->id;
        $form->attributes = \Yii::$app ->request->get();
        $arr = $form->user_search();

        $cat_list = User::find()->where(['store_id' => $this->store->id, 'is_delete' => 0, 'parent_id' => 0])
            ->orderBy('addtime DESC')
            ->all();

        return $this->render('user',[
            'list'=>$arr['list'],
            'pagination'=>$arr['pagination'],
            'row_count'=>$arr['row_count'],
            'cat_list' => $cat_list,
        ]);
    }
}