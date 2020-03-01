<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/12/21
 * Time: 14:25
 */

namespace app\modules\mch\controllers\settlementbonus;

use app\modules\mch\models\settlementbonus\Cat;
use app\modules\mch\models\settlementbonus\Order;
use app\modules\mch\models\settlementbonus\OrderClerkForm;
use app\modules\mch\models\settlementbonus\OrderForm;
use app\modules\mch\models\settlementbonus\OrderSendForm;
use app\modules\mch\models\settlementbonus\WechatTplMsgSender;

class OrderController extends Controller
{

    /**
     * @return string
     * 订单列表
     */
    public function actionIndex()
    {
        $form = new OrderForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $arr = $form->getList();

        $cat_list = Cat::find()->select('id,name')
            ->andWhere(['store_id'=>$this->store->id,'is_delete'=>0])
            ->orderBy('sort ASC')
            ->asArray()->all();


        return $this->render('index', [
            'list' => $arr['list'],
            'pagination' => $arr['p'],
            'row_count' => $arr['row_count'],
            'cat_list'  => $cat_list,
        ]);
    }

    public function actionRefund()
    {
        $order_id = \Yii::$app->request->get('order_id');
        $order = Order::find()
            ->andWhere([
                'id' => $order_id,
                'is_delete' => 0,
                'store_id' => $this->store->id,
                'is_pay' => 1,
                'is_refund' => 0,
                'apply_delete' => 1,
            ])
            ->one();
        if (!$order) {
            $this->renderJson([
                'code' => 1,
                'msg' => '订单错误1'
            ]);
        }

        if ($order->pay_price < 0) {
            $this->renderJson([
                'code' => 1,
                'msg' => '订单错误2'
            ]);
        }
        /** @var Wechat $wechat */
        $wechat = isset(\Yii::$app->controller->wechat) ? \Yii::$app->controller->wechat : null;

        $data = [
            'out_trade_no' => $order->order_no,
            'out_refund_no' => $order->order_no,
            'total_fee' => $order->pay_price * 100,
            'refund_fee' => $order->pay_price * 100,
        ];
        $res = $wechat->pay->refund($data);
        if (!$res) {
            $this->renderJson([
                'code' => 1,
                'msg' => '订单取消失败，退款失败，服务端配置出错',
            ]);
        }
        if ($res['return_code'] != 'SUCCESS') {
            $this->renderJson([
                'code' => 1,
                'msg' => '订单取消失败，退款失败，' . $res['return_msg'],
                'res' => $res,
            ]);
        }
        if ($res['result_code'] != 'SUCCESS') {
            $this->renderJson([
                'code' => 1,
                'msg' => '订单取消失败，退款失败，' . $res['err_code_des'],
                'res' => $res,
            ]);
        }
        $order->is_refund = 1;
        if ($order->save()) {
            $msg_sender = new WechatTplMsgSender($this->store_id, $order->id, $wechat);
            if ($order->is_pay) {
                $remark = '订单已退款，退款金额：' . $order->pay_price;
                $refund_reason = '用户取消';
                $msg_sender->refundMsg($order->pay_price, $refund_reason, $remark);
            }
            $this->renderJson([
                'code' => 0,
                'msg' => '订单已退款'
            ]);
        } else {
            $this->renderJson([
                'code' => 1,
                'msg' => '订单退款失败'
            ]);
        }
    }


    //订单发货 /*
    //
    //未完成
    //*/
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


    /**
     * 核销订单
     */
    public function actionClerk()
    {
        $form = new OrderClerkForm();
        $form->order_id = \Yii::$app->request->get('order_id');
        $form->price = \Yii::$app->request->get('price');
        $form->type = \Yii::$app->request->get('type');
        $form->store_id = $this->store->id;
        $this->renderJson($form->save());
    }



    /**
     * 获取奖励金额
     */
    public function actionGetsettlementbonus()
    {
        $form = new OrderClerkForm();
        $form->order_id = \Yii::$app->request->get('order_id');
        $form->store_id = $this->store->id;
        $this->renderJson($form->Getsettlementbonus());
    }


    public function actionOffline()
    {
        $form = new OrderForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $form->cat_id_is_open = true;



        $Cat = [
            1 => '业绩奖金',
            2 => '加权分红',
            3 => '返点奖金',
            4 => '福利分红',
            5 => '服务权奖金',
        ];

        $Cat = Cat::find()
            ->andWhere(['is_delete'=>0,'store_id'=>$this->store->id])
            ->asArray()
            ->orderBy('sort ASC')
            ->all();

        $cat_list = Cat::find()->select('id,name')
            ->andWhere(['store_id'=>$this->store->id,'is_delete'=>0])
            ->orderBy('sort ASC')
            ->asArray()->all();



        $arr = $form->getList();
        return $this->render('index', [
            'cat' => $Cat,
            'list' => $arr['list'],
            'pagination' => $arr['p'],
            'row_count' => $arr['row_count'],
            'cat_list'  => $cat_list,
        ]);
    }

}