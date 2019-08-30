<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/7/20
 * Time: 10:25
 */

namespace app\modules\api\models;


use app\extensions\SendMail;
use app\extensions\Sms;
use app\models\Goods;
use app\models\Order;
use app\models\OrderDetail;
use app\models\UserCoupon;
use app\models\WechatTplMsgSender;

class OrderRevokeForm extends Model
{
    public $store_id;
    public $user_id;
    public $order_id;
    public $delete_pass = false;

    public function rules()
    {
        return [
            [['order_id'], 'required'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getModelError();
        }
        $order = Order::findOne([
            'store_id' => $this->store_id,
            'user_id' => $this->user_id,
            'id' => $this->order_id,
            'is_send' => 0,
            'is_delete' => 0,
        ]);
        if (!$order) {
            return [
                'code' => 1,
                'msg' => '订单不存在'
            ];
        }
        //已支付订单需要后台先审核
        if ($order->is_pay == 1 && !$this->delete_pass) {
            $order->apply_delete = 1;
            Sms::send($order->store_id, $order->order_no);
            $mail = new SendMail($order->store_id,$order->id,0);
            $mail->send();
            if ($order->save()) {
                return [
                    'code' => 0,
                    'msg' => '订单取消申请已提交，请等候管理员审核'
                ];
            } else {
                return $this->getModelError($order);
            }
        }

        $order->is_delete = 1;
        $order_detail_list = OrderDetail::find()->where(['order_id' => $order->id, 'is_delete' => 0])->all();

        $t = \Yii::$app->db->beginTransaction();

        //库存恢复
        foreach ($order_detail_list as $order_detail) {
            $goods = Goods::findOne($order_detail->goods_id);
            $attr_id_list = [];
            foreach (json_decode($order_detail->attr) as $item)
                array_push($attr_id_list, $item->attr_id);
            $goods->numAdd($attr_id_list, $order_detail->num);
            /*
            if (!$goods->numAdd($attr_id_list, $order_detail->num)) {
                $t->rollBack();
                return [
                    'code' => 1,
                    'msg' => '订单取消失败，库存操作失败',
                ];
            }
            */
        }

        //已付款就退款
        if ($order->is_pay == 1) {
            $wechat = $this->getWechat();
            $data = [
                'out_trade_no' => $order->order_no,
                'out_refund_no' => $order->order_no,
                'total_fee' => $order->pay_price * 100,
                'refund_fee' => $order->pay_price * 100,
            ];
            $res = $wechat->pay->refund($data);
            if (!$res) {
                $t->rollBack();
                return [
                    'code' => 1,
                    'msg' => '订单取消失败，退款失败，服务端配置出错',
                ];
            }
            if ($res['return_code'] != 'SUCCESS') {
                $t->rollBack();
                return [
                    'code' => 1,
                    'msg' => '订单取消失败，退款失败，' . $res['return_msg'],
                    'res' => $res,
                ];
            }
            if ($res['result_code'] != 'SUCCESS') {
                $t->rollBack();
                return [
                    'code' => 1,
                    'msg' => '订单取消失败，退款失败，' . $res['err_code_des'],
                    'res' => $res,
                ];
            }
        }

        if ($order->save()) {
            if($order->is_pay == 0){
                UserCoupon::updateAll(['is_use'=>0],['id'=>$order->user_coupon_id]);
            }
            $t->commit();
            $msg_sender = new WechatTplMsgSender($this->store_id, $order->id, $this->getWechat());
            if ($order->is_pay) {
                $remark = '订单已取消，退款金额：' . $order->pay_price;
                $msg_sender->revokeMsg($remark);
            } else {
                $msg_sender->revokeMsg();
            }
            return [
                'code' => 0,
                'msg' => '订单已取消'
            ];
        } else {
            $t->rollBack();
            return [
                'code' => 1,
                'msg' => '订单取消失败'
            ];
        }
    }
}