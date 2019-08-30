<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/9/8
 * Time: 17:20
 */

namespace app\modules\api\models\group;


use app\models\PtOrder;
use app\models\User;
use app\modules\api\models\Model;

/**
 * Class OrderClerkForm
 * @package app\modules\api\models\group
 * 拼团订单线下核销
 */
class OrderClerkForm extends Model
{
    public $order_id;
    public $store_id;
    public $user_id;

    /**
     * @return array
     * 拼团到店自提订单线下核销
     */
    public function save()
    {
        $order = PtOrder::findOne(['id'=>$this->order_id,'store_id'=>$this->store_id,'is_pay'=>1,'apply_delete'=>0]);
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
        if($order->is_confirm == 1){
            return [
                'code'=>1,
                'msg'=>'订单已核销'
            ];
        }

        $order->clerk_id = $user->id;
        $order->shop_id = $user->shop_id;
        $order->is_confirm = 1;
        $order->confirm_time = time();
        $order->is_send = 1;
        $order->send_time = time();

        if($order->save()){
            return [
                'code'=>0,
                'msg'=>'成功',
            ];
        }else{
            return [
                'code'=>1,
                'msg'=>'网络异常'
            ];
        }
    }
}