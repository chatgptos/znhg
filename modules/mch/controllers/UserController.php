<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/3
 * Time: 13:51
 */

namespace app\modules\mch\controllers;


use app\models\IntegralLog;
use app\models\Level;
use app\models\Shop;
use app\models\Store;
use app\models\User;
use app\models\UserCoupon;
use app\modules\mch\models\LevelForm;
use app\modules\mch\models\LevelListForm;
use app\modules\mch\models\UserCardListForm;
use app\modules\mch\models\UserCouponForm;
use app\modules\mch\models\UserForm;
use app\modules\mch\models\UserListForm;
use yii\data\Pagination;

class UserController extends Controller
{
    public function actionIndex()
    {
        $form = new UserListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $data = $form->search();
        $level_list = Level::find()->where(['store_id' => $this->store->id, 'is_delete' => 0, 'status' => 1])
            ->orderBy(['level' => SORT_ASC])->asArray()->all();
        return $this->render('index', [
            'row_count' => $data['row_count'],
            'pagination' => $data['pagination'],
            'list' => $data['list'],
            'level_list' => $level_list
        ]);
    }

    /**
     * @param null $id
     * @param int $status //0--解除核销员  1--设置核销员
     * @param int $edit //0--设置/取消核销员 1--变更门店
     * @return null
     * 设置/取消核销员
     */
    public function actionClerkEdit($id = null, $status = 0, $shop_id = 0, $edit = 0)
    {
        $user = User::findOne(['id' => $id, 'is_delete' => 0, 'store_id' => $this->store->id]);
        if (!$user) {
            return $this->renderJson([
                'code' => 1,
                'msg' => '网络异常'
            ]);
        }
        if ($status == 1) {
            $shop_exit = Shop::find()->where(['store_id' => $this->store->id, 'is_delete' => 0, 'id' => $shop_id])->exists();
            if (!$shop_exit) {
                return $this->renderJson([
                    'code' => 1,
                    'msg' => '店铺不存在'
                ]);
            }
            $user->shop_id = $shop_id;
        }
        if ($edit == 0) {
            if ($user->is_clerk == $status) {
                $this->renderJson([
                    'code' => 1,
                    'msg' => '网络异常'
                ]);
            }
            $user->is_clerk = $status;
            if ($status == 0) {
                $user->shop_id = 0;
            }
        }
        if ($user->save()) {
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
     * @return string
     * 核销员列表
     */
    public function actionClerk()
    {
        User::updateAll(['shop_id' => 0], ['is_clerk' => 0]);
        $form = new UserListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $form->is_clerk = 1;
        $data = $form->search();
        $data_list = $form->getUser();
        $shop_list = Shop::find()->where(['store_id' => $this->store->id, 'is_delete' => 0])->asArray()->all();
        return $this->render('clerk', [
            'row_count' => $data['row_count'],
            'pagination' => $data['pagination'],
            'list' => $data['list'],
            'user_list' => json_encode($data_list, JSON_UNESCAPED_UNICODE),
            'shop_list' => json_encode($shop_list, JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function actionGetUser()
    {
        $form = new UserListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $data_list = $form->getUser();
        return json_encode($data_list, JSON_UNESCAPED_UNICODE);
    }

    public function actionDel($id = null)
    {
        $user = User::findOne(['id' => $id, 'is_delete' => 0, 'store_id' => $this->store->id]);
        if (!$user) {
            return $this->renderJson([
                'code' => 1,
                'msg' => '用户不存在'
            ]);
        }
        $user->is_delete = 1;
        if ($user->save()) {
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

    public function actionCoupon()
    {
        $form = new UserCouponForm();
        $form->store_id = $this->store->id;
        $form->attributes = \Yii::$app->request->get();
        $form->limit = 10;
        $arr = $form->search();
        $data = $form->getCountData();
        $user_id = \Yii::$app->request->get('user_id');
        $user = User::findOne(['store_id' => $this->store->id, 'id' => $user_id]);
        return $this->render('coupon', [
            'row_count' => $arr['row_count'],
            'pagination' => $arr['pagination'],
            'list' => $arr['list'],
            'data' => $data,
            'user' => $user
        ]);
    }

    public function actionCouponDel($id = null)
    {
        $user_coupon = UserCoupon::findOne(['id' => $id, 'store_id' => $this->store->id]);
        if (!$user_coupon)
            return json_encode([
                'code' => 1,
                'msg' => '网络异常_1'
            ], JSON_UNESCAPED_UNICODE);
        $user_coupon->is_delete = 1;
        if ($user_coupon->save()) {
            return json_encode([
                'code' => 0,
                'msg' => '成功'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode([
                'code' => 1,
                'msg' => '网络异常'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * 会员等级
     */
    public function actionLevel()
    {
        $form = new LevelListForm();
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
     * 会员等级编辑
     */
    public function actionLevelEdit($id = null)
    {
        $level = Level::findOne(['id' => $id, 'is_delete' => 0, 'store_id' => $this->store->id]);
        if (!$level) {
            $level = new Level();
        }
        $store = Store::findOne(['id' => $this->store->id]);
        if (\Yii::$app->request->isAjax) {
            $form = new LevelForm();
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
            'store' => $store
        ]);
    }

    /**
     * 会员等级启用/禁用
     */
    public function actionLevelType($type = 0, $id = null)
    {
        $level = Level::find()->where(['id' => $id, 'store_id' => $this->store->id])->one();
        if (!$level) {
            $this->renderJson([
                'code' => 1,
                'msg' => '会员等级不存在'
            ]);
        }
        $level->status = $type;
        if ($type == 0) {
            $exit = User::find()->where(['store_id' => $this->store->id, 'level' => $level->level])->exists();
            if ($exit) {
                $this->renderJson([
                    'code' => 1,
                    'msg' => '该会员等级下有会员，不可禁用'
                ]);
            }
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
     * 会员等级删除
     */
    public function actionLevelDel($id = null)
    {
        $level = Level::findOne(['id' => $id, 'store_id' => $this->store->id]);
        if (!$level) {
            $this->renderJson([
                'code' => 1,
                'msg' => '会员等级不存在'
            ]);
        }
        $exit = User::find()->where(['store_id' => $this->store->id, 'level' => $level->level])->exists();
        if ($exit) {
            $this->renderJson([
                'code' => 1,
                'msg' => '该会员等级下有会员，不可删除'
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
     * 会员编辑
     */
    public function actionEdit($id = null)
    {
        $user = User::findOne(['id' => $id, 'store_id' => $this->store->id]);
        if (!$user) {
            $this->redirect(\Yii::$app->urlManager->createUrl(['mch/user/index']))->send();
        }
        if (\Yii::$app->request->isAjax) {
            $form = new UserForm();
            $form->store_id = $this->store->id;
            $form->user = $user;
            $form->attributes = \Yii::$app->request->post();
            $this->renderJson($form->save());
        }
        $level = Level::findAll(['store_id' => $this->store->id, 'status' => 1, 'is_delete' => 0]);
        return $this->render('edit', [
            'user' => $user,
            'level' => $level
        ]);
    }

    /**
     * @return mixed|string
     * 后台用户积分充值
     */
    public function actionRechange()
    {
        $integral = (int)\Yii::$app->request->post('integral');
        $user_id = \Yii::$app->request->post('user_id');
        $rechangeType = \Yii::$app->request->post('rechangeType');
        $user = User::findOne(['id' => $user_id, 'store_id' => $this->store->id]);
        if (!$user) {
            return json_encode([
                'code' => 1,
                'msg' => '用户不存在，或已删除'
            ], JSON_UNESCAPED_UNICODE);
        }
        if (empty($integral)) {
            return json_encode([
                'code' => 1,
                'msg' => '积分设置不正确'
            ], JSON_UNESCAPED_UNICODE);
        }

        if ($rechangeType == '2') {
            if ($integral > $user->integral) {
                return json_encode([
                    'code' => 1,
                    'msg' => '用户当前积分不足'
                ], JSON_UNESCAPED_UNICODE);
            }
            $user->integral -= $integral;
        } elseif ($rechangeType == '1') {
            $user->integral += $integral;
            $user->total_integral += $integral;
        }
        if (!$user->save()) {
            return json_encode([
                'code' => 1,
                'msg' => '充值失败！请重试'
            ], JSON_UNESCAPED_UNICODE);
        }

        $integralLog = new IntegralLog();
        $integralLog->user_id = $user->id;
        if ($rechangeType == '2') {
            $integralLog->content = "管理员 后台操作账号：" . $user->nickname . " 积分扣除：" . $integral . " 积分";
        } elseif ($rechangeType == '1') {
            $integralLog->content = "管理员 后台操作账号：" . $user->nickname . " 积分充值：" . $integral . " 积分";
        }

        $integralLog->integral = $integral;
        $integralLog->addtime = time();
        $integralLog->username = $user->nickname;
        $integralLog->operator = 'admin';
        $integralLog->store_id = $this->store->id;
        $integralLog->operator_id = 0;
        if ($integralLog->save()) {
            return json_encode([
                'code' => 0,
                'msg' => '充值成功'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode([
                'code' => 1,
                'msg' => '充值失败'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function actionRechangeLog($user_id = 0)
    {
        $query = IntegralLog::find()
            ->andWhere(['store_id' => $this->store->id, 'user_id' => $user_id]);
        $count = $query->count();
        $p = new Pagination(['totalCount' => $count, 'pageSize' => 20]);
        $list = $query->orderBy('addtime DESC')
            ->limit($p->limit)
            ->offset($p->offset)
            ->all();

        return $this->render('rechange-log', [
            'list' => $list,
            'pagination' => $p,
        ]);
    }

    /**
     * 会员卡券
     */
    public function actionCard()
    {
        $form = new UserCardListForm();
        $form->store_id = $this->store->id;
        $form->attributes = \Yii::$app->request->get();
        $user_id = \Yii::$app->request->get('user_id');
        $user = User::findOne(['store_id' => $this->store->id, 'id' => $user_id]);
        $clerk_id = \Yii::$app->request->get('clerk_id');
        $clerk = User::findOne(['store_id' => $this->store->id, 'id' => $clerk_id]);
        $shop_id = \Yii::$app->request->get('shop_id');
        $shop = Shop::findOne(['store_id' => $this->store->id, 'id' => $shop_id]);
        $arr = $form->search();
        $data = $form->getCount();
        return $this->render('card', [
            'list' => $arr['list'],
            'pagination' => $arr['pagination'],
            'row_count' => $arr['row_count'],
            'data' => $data,
            'user' => $user,
            'clerk' => $clerk,
            'shop' => $shop,
        ]);
    }
}