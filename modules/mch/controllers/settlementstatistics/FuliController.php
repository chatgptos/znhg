<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/3
 * Time: 13:51
 */

namespace app\modules\mch\controllers\settlementstatistics;


use app\models\Level;
use app\models\Store;
use app\models\User;
use app\models\UserShareMoney;
use app\modules\mch\models\settlementstatistics\Award;
use app\modules\mch\models\settlementstatistics\AwardForm;
use app\modules\mch\models\settlementstatistics\AwardFuli;
use app\modules\mch\models\settlementstatistics\AwardFuliForm;
use app\modules\mch\models\settlementstatistics\AwardListForm;
use app\modules\mch\models\settlementstatistics\AwardListFuliForm;
use app\modules\mch\models\settlementstatistics\ShareMoneyListForm;
use app\modules\mch\models\UserForm;

class FuliController extends Controller
{


    /**
     * 奖品等级
     */
    public function actionLevel()
    {
//        $fulichi = '303839.00';//总价值 all_money
//        $fulichiTime = '2019/11/11';//截止时间 end_fulichi_tiem
//        $fulichiNum = '10000';//份数   num
//        $perOneCoupon = $card_count_require;//每份优惠券兑换  coupon_require
//        $name = '第一期';                     name
//        $buttonName = '立刻申请';
//        $youHas = '你有'. $user->fuliquan.'份'; /youhas/
        $form = new AwardListFuliForm();
        $form->store_id = $this->store->id;
        $form->attributes = \Yii::$app->request->get();
        $arr = $form->search();
        return $this->render('level', [
            'list' => $arr['list'],
            'pagination' => $arr['p'],
            'row_count' => $arr['row_count']
        ]);
    }
    /**
     * 奖品等级编辑
     */
    public function actionLevelEdit($id = null)
    {
        $level = AwardFuli::findOne(['id' => $id, 'is_delete' => 0, 'store_id' => $this->store->id]);
        if (!$level) {
            $level = new AwardFuli();
        }
        $store = Store::findOne(['id' => $this->store->id]);


        $level_list = Level::find()->where(['store_id' => $this->store->id, 'is_delete' => 0, 'status' => 1])
            ->orderBy(['level' => SORT_ASC])->asArray()->all();


        if (\Yii::$app->request->isAjax) {
            $form = new AwardFuliForm();
            $post = \Yii::$app->request->post();
            $form->scenario = $post['scene'];
            $form->store_id = $this->store->id;
            $form->model = $level;
            $form->attributes = $post;
            if ($post['scene'] == 'edit') {
                $this->renderJson($form->save());
            } else if ($post['scene'] == 'content') {
                $this->renderJson($form->saveContent());
            }
        }
        return $this->render('level-edit', [
            'level' => $level,
            'store' => $store,
            'level_list' => $level_list
        ]);
    }

    /**
     * 奖品等级启用/禁用
     */
    public function actionLevelType($type = 0, $id = null)
    {
        $level = AwardFuli::find()->where(['id' => $id, 'store_id' => $this->store->id])->one();
        if (!$level) {
            $this->renderJson([
                'code' => 1,
                'msg' => '奖品等级不存在'
            ]);
        }
        $level->status = $type;
        if ($type == 0) {
//            $exit = User::find()->where(['store_id' => $this->store->id, 'level' => $level->level])->exists();
//            if ($exit) {
//                $this->renderJson([
//                    'code' => 1,
//                    'msg' => '该奖品等级下有奖品，不可禁用'
//                ]);
//            }
        }
        if ($level->save()) {
            $this->renderJson([
                'code' => 0,
                'msg' => '成功'
            ]);
        } else {
            $this->renderJson([
                'code' => 1,
                'msg' => '网络异常'
            ]);
        }
    }

    /**
     * 奖品等级删除
     */
    public function actionLevelDel($id = null)
    {
        $level = AwardFuli::findOne(['id' => $id, 'store_id' => $this->store->id]);
        if (!$level) {
            $this->renderJson([
                'code' => 1,
                'msg' => '奖品等级不存在'
            ]);
        }
        $exit = AwardFuli::find()->where(['store_id' => $this->store->id, 'level' => $level->level])->exists();
        if ($exit) {
            $this->renderJson([
                'code' => 1,
                'msg' => '该奖品等级下有奖品，不可删除'
            ]);
        }
        $level->is_delete = 1;
        if ($level->save()) {
            $level->delete();
            $this->renderJson([
                'code' => 0,
                'msg' => '成功'
            ]);
        } else {
            $this->renderJson([
                'code' => 1,
                'msg' => '网络异常'
            ]);
        }
    }

    /**
     * 会员奖品编辑
     */
    public function actionEdit($id = null)
    {
        $user = AwardFuli::findOne(['id' => $id, 'store_id' => $this->store->id]);
        if (!$user) {
            $this->redirect(\Yii::$app->urlManager->createUrl(['mch/user/index']))->send();
        }
        if (\Yii::$app->request->isAjax) {
            $form = new AwardFuliForm();
            $form->store_id = $this->store->id;
            $form->user = $user;
            $form->attributes = \Yii::$app->request->post();
            $this->renderJson($form->save());
        }
        $level = AwardFuli::findAll(['store_id' => $this->store->id, 'status' => 1, 'is_delete' => 0]);

        $user_list = AwardFuli::findAll(['store_id' => $this->store->id,'is_distributor' => 1]);


//        $user_list = User::find(['store_id' => $this->store->id,'is_distributor' => 1])->asArray()->all();;
//        foreach($user_list as $index=>$value){
//            $user = User::findOne(['id' =>  $value['parent_id']]);
//            if($user && isset($user->nickname)){
//                $user_list[$index]['parent_id_nickname'] = $user->nickname;
//            }
//        }
        return $this->render('edit', [
            'user' => $user,
            'parent_list' => $user_list,
            'level' => $level
        ]);
    }
 
}