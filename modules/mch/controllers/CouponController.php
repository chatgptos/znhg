<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/24
 * Time: 10:15
 */

namespace app\modules\mch\controllers;


use app\models\Coupon;
use app\models\CouponAutoSend;
use app\models\User;
use app\modules\mch\models\CouponEditForm;
use app\modules\mch\models\CouponSendForm;
use app\modules\mch\models\Model;
use yii\helpers\VarDumper;

class CouponController extends Controller
{
    //优惠券列表
    public function actionIndex()
    {
        $list = Coupon::find()->where(['store_id' => $this->store->id, 'is_delete' => 0,])->orderBy('sort ASC')->all();
        return $this->render('index', [
            'list' => $list,
        ]);
    }

    //优惠券编辑
    public function actionEdit($id = null)
    {
        $model = Coupon::findOne([
            'id' => $id,
            'store_id' => $this->store->id,
            'is_delete' => 0,
        ]);
        if (!$model) {
            $model = new Coupon();
        }
        if (\Yii::$app->request->isPost) {
            $form = new CouponEditForm();
            $form->attributes = \Yii::$app->request->post();
            $form->store_id = $this->store->id;
            $form->coupon = $model;
            $this->renderJson($form->save());
        } else {
            return $this->render('edit', [
                'model' => $model,
            ]);
        }
    }

    //优惠券删除
    public function actionDelete($id)
    {
        $model = Coupon::findOne([
            'id' => $id,
            'store_id' => $this->store->id,
        ]);
        if ($model) {
            $model->is_delete = 1;
            $model->save();
            CouponAutoSend::updateAll(['is_delete' => 1], ['coupon_id' => $model->id]);
        }
        $this->renderJson([
            'code' => 0,
            'msg' => '操作成功',
        ]);
    }

    //优惠券发放
    public function actionSend($id)
    {
        $coupon = Coupon::findOne([
            'id' => $id,
            'store_id' => $this->store->id,
            'is_delete' => 0,
        ]);
        if (!$coupon) {
            \Yii::$app->response->redirect(\Yii::$app->request->referrer)->send();
            return;
        }
        if (\Yii::$app->request->isPost) {
            $form = new CouponSendForm();
            $form->attributes = \Yii::$app->request->post();
            $form->store_id = $this->store->id;
            $form->coupon_id = $coupon->id;
            $this->renderJson($form->save());
        } else {
            return $this->render('send', [
                'coupon' => $coupon,
            ]);
        }
    }

    //查找用户
    public function actionSearchUser($keyword)
    {
        $keyword = trim($keyword);
        $query = User::find()->alias('u')->where([
            'AND',
            ['LIKE', 'u.nickname', $keyword],
            ['store_id' => $this->store->id],
        ]);
        $list = $query->orderBy('u.nickname')->limit(30)->asArray()->select('id,nickname,avatar_url')->all();
        $this->renderJson([
            'code' => 0,
            'msg' => 'success',
            'data' => (object)[
                'list' => $list
            ],
        ]);
    }

    //自动发放
    public function actionAutoSend()
    {
        $list = CouponAutoSend::find()->where([
            'store_id' => $this->store->id,
            'is_delete' => 0,
        ])->orderBy('addtime DESC')->all();
        return $this->render('auto-send', [
            'list' => $list,
        ]);
    }

    //自动发放编辑
    public function actionAutoSendEdit($id = null)
    {
        $model = CouponAutoSend::findOne([
            'id' => $id,
            'store_id' => $this->store->id,
            'is_delete' => 0,
        ]);
        if (!$model) {
            $model = new CouponAutoSend();
        }
        if (\Yii::$app->request->isPost) {
            $coupon = Coupon::findOne([
                'id' => \Yii::$app->request->post('coupon_id'),
                'store_id' => $this->store->id,
                'is_delete' => 0,
            ]);
            if (!$coupon)
                $this->renderJson([
                    'code' => 1,
                    'msg' => '优惠券不存在或已删除，请刷新页面后重试',
                ]);
            $model->event = \Yii::$app->request->post('event');
            $model->coupon_id = $coupon->id;
            $model->send_times = \Yii::$app->request->post('send_times');
            if ($model->send_times === '' || $model->send_times === null) {
                $this->renderJson([
                    'code' => 1,
                    'msg' => '最多发放次数不能为空',
                ]);
            }
            if ($model->isNewRecord) {
                $model->store_id = $this->store->id;
                $model->addtime = time();
                $model->is_delete = 0;
            }
            if ($model->save()) {
                $this->renderJson([
                    'code' => 0,
                    'msg' => '保存成功',
                ]);
            } else {
                $this->renderJson((new Model())->getModelError($model));
            }

        } else {
            $coupon_list = Coupon::find()->where(['store_id' => $this->store->id, 'is_delete' => 0])->all();
            return $this->render('auto-send-edit', [
                'model' => $model,
                'coupon_list' => $coupon_list,
            ]);
        }
    }


    //自动发放方案删除
    public function actionAutoSendDelete($id)
    {
        $model = CouponAutoSend::findOne([
            'id' => $id,
            'store_id' => $this->store->id,
        ]);
        if ($model) {
            $model->is_delete = 1;
            $model->save();
        }
        $this->renderJson([
            'code' => 0,
            'msg' => '操作成功',
        ]);
    }

}