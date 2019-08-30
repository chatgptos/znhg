<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/9/8
 * Time: 17:20
 */

namespace app\modules\api\models;


use app\models\Order;
use app\models\User;

class OrderClerkForm extends Model
{
    public $order_id;
    public $store_id;
    public $user_id;

    public function save()
    {
        $order = Order::findOne(['id'=>$this->order_id,'store_id'=>$this->store_id,'is_pay'=>1]);
        if(!$order){
            return [
                'code'=>1,
                'msg'=>'网络异常-1'
            ];
        }
        $user = User::findOne(['id'=>$this->user_id]);
        if($user->is_clerk == 0){
            return [
                'code'=>1,
                'msg'=>'不是核销员'
            ];
        }
        if($order->is_send == 1){
            return [
                'code'=>1,
                'msg'=>'订单已核销'
            ];
        }
        $order->clerk_id = $user->id;
        $order->is_send = 1;
        $order->shop_id = $user->shop_id;
        $order->send_time = time();
        $order->is_confirm = 1;
        $order->confirm_time = time();

        if($order->save()){
            return [
                'code'=>0,
                'msg'=>'成功'
            ];
        }else{
            return [
                'code'=>1,
                'msg'=>'网络异常'
            ];
        }
    }
}