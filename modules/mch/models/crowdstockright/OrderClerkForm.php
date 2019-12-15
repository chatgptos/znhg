<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/9/8
 * Time: 17:20
 */

namespace app\modules\mch\models\crowdstockright;


use app\models\IntegralLog;
use app\models\User;
use app\modules\mch\models\Model;

/**
 * Class OrderClerkForm
 * @package app\modules\mch\models\book
 * 预约订线下核销
 */
class OrderClerkForm extends Model
{
    public $order_id;
    public $store_id;
    public $user_id;

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
                'msg' => '订单不存在'
            ];
        }
        if ($order->is_use == 1) {
            return [
                'code' => 1,
                'msg' => '订单已核销'
            ];
        }
        // 获取用户当前积分
        $user = User::findOne(['id' => $order->user_id, 'type' => 1, 'is_delete' => 0]);
        // 减去当前用户账户积分
        $t = \Yii::$app->db->beginTransaction();
        $order->clerk_id = 9999;//后台核销
        $order->shop_id = 9999;//后台核销
        $order->is_use = 1;
        $order->use_time = time();
        $user->integral += $order->return_integral;
        $user->coupon += $order->return_integral;
        //记录日志
        $hld = 0;
        $coupon = $order->return_integral;
        $integral = $order->return_integral;

        $integralLog = new IntegralLog();
        $integralLog->user_id = $user->id;
        //卖优惠券
        $integralLog->content = "（奖励众筹股东：" . $user->nickname . " 欢乐豆" . $user->hld . "：" . $hld . " 豆" . " 优惠券" . $user->coupon . "：" . $coupon . " 张" . $integral . '积分）';
        $integralLog->integral = $integral;
        $integralLog->hld = $hld;
        $integralLog->coupon = $coupon;
        $integralLog->addtime = time();
        $integralLog->username = $user->nickname;
        $integralLog->operator = 'admin';
        $integralLog->store_id = $this->store_id;
        $integralLog->operator_id = 0;
        $integralLog->save();

        if ($user->save() && $order->save()) {
            $t->commit();
            return [
                'code' => 0,
                'msg' => '成功'
            ];
        } else {
            $t->rollBack();
            return [
                'code' => 1,
                'msg' => '网络异常'
            ];;
        }
    }
}