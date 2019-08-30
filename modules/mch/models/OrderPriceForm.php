<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/9/15
 * Time: 14:17
 */

namespace app\modules\mch\models;


use app\models\Goods;
use app\models\Model;
use app\models\Order;
use app\models\OrderDetail;
use app\models\Setting;
use app\models\User;

class OrderPriceForm extends Model
{
    public $store_id;
    public $order_id;
    public $price;
    public $type;

    public function rules()
    {
        return [
            [['order_id','price','type'],'number'],
            [['type'],'in','range'=>[1,2]],
            [['price'],'required']
        ];
    }
    public function attributeLabels()
    {
        return [
            'price'=>'修改的价格'
        ];
    }

    public function update()
    {
        if(!$this->validate()){
            return $this->getModelError();
        }
        $order = Order::findOne(['id'=>$this->order_id,'is_delete'=>0,'is_pay'=>0]);
        if(!$order){
            return [
                'code'=>0,
                'msg'=>'网络异常'
            ];
        }
        $money = $order->pay_price;
        if($order->before_update_price){
        }else{
            $order->before_update_price = $money;
        }
        if($this->type == 1){
            $order->pay_price = round($money + $this->price,2);
        }else{
            $order->pay_price = round($money - $this->price,2);
        }
        if($order->pay_price < 0.01){
            return [
                'code'=>1,
                'msg'=>'修改后的价格不能小于0.01'
            ];
        }
        if($order->save()){
            $order_detail_list = OrderDetail::findAll(['order_id'=>$order->id,'is_delete'=>0]);
            $goods_total_price = 0.00;
            $goods_total_pay_price = $order->pay_price - $order->express_price;
            foreach($order_detail_list as $goods){
                $goods_total_price += $goods->total_price;
            }
            foreach($order_detail_list as $goods){
                $goods->total_price = doubleval(sprintf('%.2f', $goods_total_pay_price * $goods->total_price / $goods_total_price));
                $goods->save();
            }
            $this->setReturnData($order);
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
    /**
     * 设置佣金
     * @param Order $order
     */
    private function setReturnData($order)
    {
        $setting = Setting::findOne(['store_id' => $order->store_id]);
        if (!$setting || $setting->level == 0)
            return;
        $user = User::findOne($order->user_id);//订单本人
        if (!$user)
            return;
        $order->parent_id = $user->parent_id;
        $parent = User::findOne($user->parent_id);//上级
        if ($parent->parent_id) {
            $order->parent_id_1 = $parent->parent_id;
            $parent_1 = User::findOne($parent->parent_id);//上上级
            if ($parent_1->parent_id) {
                $order->parent_id_2 = $parent_1->parent_id;
            } else {
                $order->parent_id_2 = -1;
            }
        } else {
            $order->parent_id_1 = -1;
            $order->parent_id_2 = -1;
        }
        $order_total = doubleval($order->total_price - $order->express_price);
        $pay_price = doubleval($order->pay_price - $order->express_price);

        $order_detail_list = OrderDetail::find()->alias('od')->leftJoin(['g' => Goods::tableName()], 'od.goods_id=g.id')
            ->where(['od.is_delete' => 0, 'od.order_id' => $order->id])
            ->asArray()
            ->select('g.individual_share,g.share_commission_first,g.share_commission_second,g.share_commission_third,od.total_price,od.num,g.share_type')
            ->all();
        $share_commission_money_first = 0;//一级分销总佣金
        $share_commission_money_second = 0;//二级分销总佣金
        $share_commission_money_third = 0;//三级分销总佣金
        foreach ($order_detail_list as $item) {
            $item_price = doubleval($item['total_price']);
            if ($item['individual_share'] == 1) {
                $rate_first = doubleval($item['share_commission_first']);
                $rate_second = doubleval($item['share_commission_second']);
                $rate_third = doubleval($item['share_commission_third']);
                if ($item['share_type'] == 1) {
                    $share_commission_money_first += $rate_first * $item['num'];
                    $share_commission_money_second += $rate_second * $item['num'];
                    $share_commission_money_third += $rate_third * $item['num'];
                } else {
                    $share_commission_money_first += $item_price * $rate_first / 100;
                    $share_commission_money_second += $item_price * $rate_second / 100;
                    $share_commission_money_third += $item_price * $rate_third / 100;
                }
            } else {
                $rate_first = doubleval($setting->first);
                $rate_second = doubleval($setting->second);
                $rate_third = doubleval($setting->third);
                if ($setting->price_type == 1) {
                    $share_commission_money_first += $rate_first * $item['num'];
                    $share_commission_money_second += $rate_second * $item['num'];
                    $share_commission_money_third += $rate_third * $item['num'];
                } else {
                    $share_commission_money_first += $item_price * $rate_first / 100;
                    $share_commission_money_second += $item_price * $rate_second / 100;
                    $share_commission_money_third += $item_price * $rate_third / 100;
                }
            }
        }


        $order->first_price = $share_commission_money_first < 0.01 ? 0 : $share_commission_money_first;
        $order->second_price = $share_commission_money_second < 0.01 ? 0 : $share_commission_money_second;
        $order->third_price = $share_commission_money_third < 0.01 ? 0 : $share_commission_money_third;
        $order->save();
    }
}