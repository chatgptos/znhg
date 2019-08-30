<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/7/20
 * Time: 14:27
 */

namespace app\modules\mch\controllers;


use app\models\Express;
use app\models\Order;
use app\models\OrderRefund;
use app\models\Shop;
use app\models\User;
use app\models\WechatTplMsgSender;
use app\modules\api\models\OrderRevokeForm;
use app\models\OrderForm;
use app\modules\mch\models\OrderListForm;
use app\modules\mch\models\OrderPriceForm;
use app\modules\mch\models\OrderRefundForm;
use app\modules\mch\models\OrderRefundListForm;
use app\modules\mch\models\OrderSendForm;
use app\modules\mch\models\PrintForm;
use app\modules\mch\models\StoreDataForm;

class OrderController extends Controller
{
    public function actionIndex($is_offline = null)
    {
        $form = new OrderListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $form->limit = 10;
        $data = $form->search();

        $store_data_form = new StoreDataForm();
        $store_data_form->store_id = $this->store->id;
        $store_data_form->is_offline = \Yii::$app->request->get('is_offline');
        $user_id = \Yii::$app->request->get('user_id');
        $clerk_id = \Yii::$app->request->get('clerk_id');
        $shop_id = \Yii::$app->request->get('shop_id');
        $store_data_form->user_id = $user_id;
        $store_data_form->clerk_id = $clerk_id;
        $store_data_form->shop_id = $shop_id;
        if ($user_id) {
            $user = User::findOne(['store_id' => $this->store->id, 'id' => $user_id]);
        }
        if ($clerk_id) {
            $clerk = User::findOne(['store_id' => $this->store->id, 'id' => $clerk_id]);
        }
        if ($shop_id) {
            $shop = Shop::findOne(['store_id' => $this->store->id, 'id' => $shop_id]);
        }
        return $this->render('index', [
            'row_count' => $data['row_count'],
            'pagination' => $data['pagination'],
            'list' => $data['list'],
            //'count_data' => OrderListForm::getCountData($this->store->id),
            'store_data' => $store_data_form->search(),
            'express_list' => $this->getExpressList(),
            'user' => isset($user) ? $user : null,
            'clerk' => isset($clerk) ? $clerk : null,
            'shop' => isset($shop) ? $shop : null,
        ]);
    }

    //订单发货
    public function actionSend()
    {
        $form = new OrderSendForm();
        $post = \Yii::$app->request->post();
        if ($post['is_express'] == 1) {
            $form->scenario = 'EXPRESS';
        }
        $form->attributes = $post;
        $form->store_id = $this->store->id;
        $this->renderJson($form->save());
    }

    private function getExpressList()
    {
        $store_express_list = Order::find()
            ->select('express')
            ->where([
                'AND',
                ['store_id' => $this->store->id],
                ['is_send' => 1],
                ['!=', 'express', ''],
            ])->groupBy('express')->orderBy('send_time DESC')->limit(5)->asArray()->all();
        $express_list = Express::find()->select('name AS express')->orderBy('sort ASC')->asArray()->all();
        $new_store_express_list = [];
        foreach ($store_express_list as $i => $item)
            $new_store_express_list[] = $item['express'];

        $new_public_express_list = [];
        foreach ($express_list as $i => $item)
            $new_public_express_list[] = $item['express'];
        return [
            'private' => $new_store_express_list,
            'public' => $new_public_express_list,
        ];
    }

    //售后订单列表
    public function actionRefund()
    {
        if (\Yii::$app->request->isPost) {
            $form = new OrderRefundForm();
            $form->attributes = \Yii::$app->request->post();
            $form->store_id = $this->store->id;
            $this->renderJson($form->save());
        } else {
            $form = new OrderRefundListForm();
            $form->attributes = \Yii::$app->request->get();
            $form->store_id = $this->store->id;
            $form->limit = 10;
            $data = $form->search();

            return $this->render('refund', [
                'row_count' => $data['row_count'],
                'pagination' => $data['pagination'],
                'list' => $data['list'],
            ]);
        }
    }

    //订单取消申请处理
    public function actionApplyDeleteStatus($id, $status)
    {
        $order = Order::findOne([
            'id' => $id,
            'apply_delete' => 1,
            'is_delete' => 0,
            'store_id' => $this->store->id,
        ]);
        if (!$order) {
            $this->renderJson([
                'code' => 1,
                'msg' => '订单不存在，请刷新页面后重试',
            ]);
        }
        if ($status == 1) {//同意
            $form = new OrderRevokeForm();
            $form->order_id = $order->id;
            $form->delete_pass = true;
            $form->user_id = $order->user_id;
            $form->store_id = $order->store_id;
            $res = $form->save();
            if ($res['code'] == 0) {
                $this->renderJson([
                    'code' => 0,
                    'msg' => '操作成功',
                ]);
            } else {
                $this->renderJson($res);
            }
        } else {//拒绝
            $order->apply_delete = 0;
            $order->save();
            $msg_sender = new WechatTplMsgSender($this->store->id, $order->id, $this->wechat);
            $msg_sender->revokeMsg('您的取消申请已被拒绝');
            $this->renderJson([
                'code' => 0,
                'msg' => '操作成功',
            ]);
        }
    }

    public function actionPrint()
    {
        $id = \Yii::$app->request->get('id');
        $express = \Yii::$app->request->get('express');
        $post_code = \Yii::$app->request->get('post_code');
        $form = new PrintForm();
        $form->store_id = $this->store->id;
        $form->order_id = $id;
        $form->express = $express;
        $form->post_code = $post_code;
        return json_encode($form->send(), JSON_UNESCAPED_UNICODE);
    }

    public function actionAddPrice()
    {
        $form = new OrderPriceForm();
        $form->store_id = $this->store->id;
        $form->attributes = \Yii::$app->request->get();
        return json_encode($form->update());
    }

    public function actionDetail($order_id = null)
    {
        $order = Order::find()->where(['is_delete' => 0, 'store_id' => $this->store->id, 'id' => $order_id])->asArray()->one();
        if (!$order) {
            $url = \Yii::$app->urlManager->createUrl(['mch/order/index']);
            $this->redirect($url)->send();
        }
        $order['integral_arr'] = json_decode($order['integral'], true);
        $form = new OrderListForm();
        $goods_list = $form->getOrderGoodsList($order['id']);
        $user = User::find()->where(['id' => $order['user_id'], 'store_id' => $this->store->id])->asArray()->one();
        $order_form = OrderForm::find()->where(['order_id' => $order['id'], 'is_delete' => 0, 'store_id' => $this->store->id])->asArray()->all();
        $order_refund = OrderRefund::findOne(['store_id' => $this->store->id, 'order_id' => $order['id'], 'is_delete' => 0]);
        $order_refund && $order['refund'] = $order_refund->status;

        return $this->render('detail', [
            'order' => $order,
            'goods_list' => $goods_list,
            'user' => $user,
            'order_form' => $order_form
        ]);
    }

    public function actionOffline()
    {
        $form = new OrderListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->is_offline = 1;
        $form->store_id = $this->store->id;
        $form->limit = 10;
        $data = $form->search();

        $store_data_form = new StoreDataForm();
        $store_data_form->store_id = $this->store->id;
        $store_data_form->is_offline = 1;
        $user_id = \Yii::$app->request->get('user_id');
        $clerk_id = \Yii::$app->request->get('clerk_id');
        $shop_id = \Yii::$app->request->get('shop_id');
        $store_data_form->user_id = $user_id;
        $store_data_form->clerk_id = $clerk_id;
        $store_data_form->shop_id = $shop_id;
        if ($user_id) {
            $user = User::findOne(['store_id' => $this->store->id, 'id' => $user_id]);
        }
        if ($clerk_id) {
            $clerk = User::findOne(['store_id' => $this->store->id, 'id' => $clerk_id]);
        }
        if ($shop_id) {
            $shop = Shop::findOne(['store_id' => $this->store->id, 'id' => $shop_id]);
        }
        return $this->render('index', [
            'row_count' => $data['row_count'],
            'pagination' => $data['pagination'],
            'list' => $data['list'],
            //'count_data' => OrderListForm::getCountData($this->store->id),
            'store_data' => $store_data_form->search(),
            'express_list' => $this->getExpressList(),
            'user' => isset($user) ? $user : null,
            'clerk' => isset($clerk) ? $clerk : null,
            'shop' => isset($shop) ? $shop : null,
        ]);
    }
}