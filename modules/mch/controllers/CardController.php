<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/25
 * Time: 9:25
 */

namespace app\modules\mch\controllers;


use app\models\Card;
use app\modules\mch\models\CardForm;
use app\modules\mch\models\CardListForm;

class CardController extends Controller
{
    /**
     * 卡券列表
     */
    public function actionIndex()
    {
        $form = new CardListForm();
        $form->store_id = $this->store->id;
        $form->attributes = \Yii::$app->request->get();
        $arr = $form->search();
        return $this->render('index',[
            'list'=>$arr['list'],
            'pagintion'=>$arr['pagintion']
        ]);
    }

    /**
     * 卡券编辑
     */
    public function actionEdit($id = null)
    {
        $model = Card::findOne(['id'=>$id,'is_delete'=>0]);
        if(!$model){
            $model = new Card();
        }
        if (\Yii::$app->request->isPost) {
            $form = new CardForm();
            $form->store_id = $this->store->id;
            $form->card = $model;
            $form->attributes = \Yii::$app->request->post();
            $this->renderJson($form->save());
        } else {
            return $this->render('edit',[
                'model'=>$model
            ]);
        }
    }
    /**
     * 卡券删除
     */
    public function actionDel($id = null)
    {
        $card = Card::findOne(['id'=>$id,'store_id'=>$this->store->id]);
        if(!$card){
            $this->renderJson([
                'code'=>1,
                'msg'=>'卡券不存在，请刷新后重试！'
            ]);
        }
        if($card->is_delete == 1){
            $this->renderJson([
                'code'=>1,
                'msg'=>'卡券已删除，请刷新后重试！'
            ]);
        }
        $card->is_delete = 1;
        if($card->save()){
            $this->renderJson([
                'code'=>0,
                'msg'=>'删除成功'
            ]);
        }else{
            $this->renderJson([
                'code'=>1,
                'msg'=>'请刷新后重试！'
            ]);
        }
    }
}