<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/9/8
 * Time: 17:20
 */

namespace app\modules\mch\models\bookmall;


use app\models\IntegralLog;
use app\modules\api\models\bookmall\OrderSubmitForm;
use \app\modules\mch\models\bookmall\Order;
use app\models\User;
use app\models\YyOrder;
use app\modules\api\models\Model;

/**
 * Class OrderClerkForm
 * @package app\modules\api\models\book
 * 预约订线下核销
 */
class OrderClerkForm extends Model
{
    public $order_id;
    public $store_id;
    public $user_id;
    public $is_check_yukuan;

    /**
     * @return array
     * 预约订单线下核销
     * 逻辑操作
     */
    public function save()
    {
        $order = Order::findOne(['id' => $this->order_id, 'store_id' => $this->store_id, 'is_pay' => 1, 'apply_delete' => 0]);
        if (!$order) {
            return [
                'code' => 1,
                'msg' => '订单不存在-1'
            ];
        }
        //重新计算价格


        $order_detail = OrderDetail::findOne(['order_id' => $this->order_id]);
        $goods = Goods::findOne(['id' => $order_detail->goods_id]);
        //会卡死了

        $query_num_buy_order = Order::find()->alias('o')
            ->where(
                [
                    'od.goods_id' => $order_detail->goods_id,
                    'o.is_delete' => 0,
                    'o.store_id' => $this->store_id])
            ->leftJoin(['od' => OrderDetail::tableName()], 'od.order_id=o.id')
            ->andWhere([
                'AND',
                [
                    'od.is_delete' => 0,
                    'o.is_delete' => 0,
                    'o.is_pay' => 1,
//                    'o.is_check_yukuan' => 0,//还未审核到
                    'o.is_yukuan' => 0//未支付余款的
                ],
            ])->count();

        //查询当前用户订单
//        $query_num_buy_order = Goods::find()->alias('g')
//            ->where(
//                [
//                    'od.goods_id' => $order_detail->goods_id,
//                    'o.is_delete' => 0,
//                    'o.store_id' => $this->store_id])
//            ->leftJoin(['od' => OrderDetail::tableName()], 'od.goods_id=g.id')
//            ->leftJoin(['o' => Order::tableName()], 'o.id=od.order_id')
//            ->andWhere([
//                'AND',
//                [
//                    'od.is_delete' => 0,
//                    'o.is_delete' => 0,
//                    'o.is_pay' => 1,
//                    'o.is_check_yukuan' => 0,//还未审核到
//                    'o.is_yukuan' => 0//未支付余款的
//                ],
//            ])->count();


        $form = new OrderSubmitForm();
        $charge_integral_buy = $form->getCharge($query_num_buy_order, $goods);
        $charge_coupon = $form->getCharge($query_num_buy_order, $goods);
        //余款
        $yukuan_coupon = intval($order->seckill_coupon) * (1 - $goods->advance / 100);
        $yukuan_integral_buy = intval($order->seckill_integral) * (1 - $goods->advance / 100);
        //计算出余款价格
//        $charge_coupon = intval($yukuan_integral_buy * (1 - $charge_coupon / 100));
        $yukuan_coupon = intval($yukuan_coupon* (1 - $charge_coupon / 100));
        $yukuan_integral_buy= intval($yukuan_integral_buy* (1 - $charge_integral_buy / 100));



        $order->clerk_id = 9999;//后台核销
        $order->shop_id = 9999;//后台核销
        $order->is_check_yukuan = $this->is_check_yukuan;
        $order->check_yukuan_time = time();
        $order->yukuan_coupon = $yukuan_coupon;
        $order->yukuan_integral_buy = $yukuan_integral_buy;

        //当是订单拒绝
        if ($this->is_check_yukuan == 2 && $order->is_pay == 1) {
            // 获取用户当前积分
            $user = User::findOne(['id' => $order->user_id, 'type' => 1, 'is_delete' => 0]);
            //返回积分
            // 减去当前用户账户积分

            $total_price_2 = $order->advance_integral_buy;
            $advance_coupon = $order->advance_coupon;
            $t = \Yii::$app->db->beginTransaction();

            if ($total_price_2 > 0 || $advance_coupon > 0) {
                $user->integral += $total_price_2;
                $user->coupon += $advance_coupon;
                $user->save();
                //记录日志
                $hld = 0;
                $coupon = $advance_coupon;
                $integral = $total_price_2;

                $integralLog = new IntegralLog();
                $integralLog->user_id = $user->id;
                //卖优惠券
                $integralLog->content = "管理员（优惠券预售商城后台退换） 后台操作账号：" . $user->nickname . " 欢乐豆" . $user->hld . "已经发放：" . $hld . " 豆" . " 优惠券" . $user->coupon . "已经返还：" . $coupon . " 张（购买时候时候已经扣除优惠券）,（交易时积分" . $integral . '个积分）';
                $integralLog->integral = $integral;
                $integralLog->hld = $hld;
                $integralLog->coupon = $coupon;
                $integralLog->addtime = time();
                $integralLog->username = $user->nickname;
                $integralLog->operator = 'admin';
                $integralLog->store_id = $this->store_id;
                $integralLog->operator_id = 0;
                if ($user->save() && $integralLog->save() && $order->save()) {
                    $t->commit();
                    return [
                        'code' => 0,
                        'msg' => 'success',
                        'data' => '积分返回成功',
                    ];
                } else {
                    $t->rollBack();
                    return [
                        'code' => 1,
                        'msg' => 'error',
                        'data' => '保存失败',
                    ];;
                }
            }else{
                $wechat = $this->getWechat();
                $wechat_tpl_meg_sender = new WechatTplMsgSender($order->store_id, $order->id, $wechat);
                $wechat_tpl_meg_sender->payYukuanMsg();
            }
        }

        if ($order->save()) {
            return [
                'code' => 0,
                'msg' => '成功'
            ];
        } else {
            return [
                'code' => 1,
                'msg' => '网络异常'
            ];
        }
    }
}