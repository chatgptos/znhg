<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/25
 * Time: 9:25
 */

namespace app\modules\mch\controllers;


use app\models\Room;
use app\modules\mch\models\RoomForm;
use app\modules\mch\models\RoomListForm;

class RoomController extends Controller
{
    /**
     * 直播间列表
     */
    public function actionIndex()
    {
        $form = new RoomListForm();
        $form->store_id = $this->store->id;
        $form->attributes = \Yii::$app->request->get();
        $arr = $form->search();
        return $this->render('index',[
            'list'=>$arr['list'],
            'pagintion'=>$arr['pagintion']
        ]);
    }

    /**
     * 直播间编辑
     */
    public function actionEdit($id = null)
    {
        $model = Room::findOne(['id'=>$id,'is_delete'=>0]);
        if(!$model){
            $model = new Room();
        }
        if (\Yii::$app->request->isPost) {
            $form = new RoomForm();
            $form->store_id = $this->store->id;
            $form->Room = $model;
            $form->attributes = \Yii::$app->request->post();
            $this->renderJson($form->save());
        } else {
            return $this->render('edit',[
                'model'=>$model
            ]);
        }
    }
    /**
     * 直播间删除
     */
    public function actionDel($id = null)
    {
        $Room = Room::findOne(['id'=>$id,'store_id'=>$this->store->id]);
        if(!$Room){
            $this->renderJson([
                'code'=>1,
                'msg'=>'直播间不存在，请刷新后重试！'
            ]);
        }
        if($Room->is_delete == 1){
            $this->renderJson([
                'code'=>1,
                'msg'=>'直播间已删除，请刷新后重试！'
            ]);
        }
        $Room->is_delete = 1;
        if($Room->save()){
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

    /**
     * 直播间删除
     */
    public function actionTransfer($id = null)
    {
        $form = new RoomForm();
        $form->store_id = $this->store->id;
        $this->renderJson($form->transfer($id));
        $this->renderJson([
            'code'=>0,
            'msg'=>'删除成功'
        ]);
    }
}