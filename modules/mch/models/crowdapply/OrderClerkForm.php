<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/9/8
 * Time: 17:20
 */

namespace app\modules\mch\models\crowdapply;


use app\models\Room;
use app\models\User;
use app\modules\api\models\BusinessCommentForm;
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
    public $price;
    public $type;



    /**
     * @return array
     * 预约订单线下核销
     * 逻辑操作
     */
    public function save()
    {
        $order = Order::findOne(['id'=>$this->order_id,'store_id'=>$this->store_id,'is_pay'=>1,'apply_delete'=>0]);
        if(!$order){
            return [
                'code'=>1,
                'msg'=>'网络异常-1'
            ];
        }
        $user = User::findOne(['id'=>$order->user_id]);
        if( $this->price == 0){
            return [
                'code'=>1,
                'msg'=>'请输入直播间id'
            ];
        }
        if($order->is_use == 1){
            return [
                'code'=>1,
                'msg'=>'订单已核销'
            ];
        }
        $order->clerk_id = 9999;//后台核销
        $order->shop_id = 9999;//后台核销
        $order->room_id = $this->price;



        $room_info =  Room::findOne(['room_id' => $order->room_id, 'store_id' => $this->store_id ,'is_delete' =>0]);
        if(!$room_info){
            return [
                'code'=>1,
                'msg'=>'请同步腾讯直播间'
            ];
        }

        $coupon=1;//赠送券
        //新人增加
        User::updateAll(
            ['coupon'=>$user->coupon+$coupon],
            ['id' => $order->user_id]
        );
        //增加一张券
        $form = new BusinessCommentForm();
        $form->user_id = $order->user_id;
        $form->store_id = 1;
        $form->num = 1;
        $form->title = $room_info->name;
        $form->room_id = $room_info->room_id;//是智能鲜蜂服务点 智能鲜蜂服务点表象
        $form->good_id = 0;//是智能鲜蜂服务点 智能鲜蜂服务点表象
        $form->article_id = 0;//是智能鲜蜂服务点 智能鲜蜂服务点表象
        $res1 = $form->add();

        if(!$res1){
            return [
                'code'=>1,
                'msg'=>'创建直播间发现板块失败'
            ];
        }

        if($this->type==2){
            $order->is_use = 1;
        }else{
            $order->is_use = 0;
        }

        
        $order->use_time = time();

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