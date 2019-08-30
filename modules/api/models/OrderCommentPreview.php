<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/15
 * Time: 17:23
 */

namespace app\modules\api\models;


use app\models\Goods;
use app\models\Order;
use app\models\OrderDetail;

class OrderCommentPreview extends Model
{
    public $store_id;
    public $user_id;
    public $order_id;

    public function rules()
    {
        return [
            [['order_id'], 'required'],
        ];
    }

    public function search()
    {
        if (!$this->validate())
            return $this->getModelError();
        $order = Order::findOne([
            'is_delete' => 0,
            'store_id' => $this->store_id,
            'user_id' => $this->user_id,
            'id' => $this->order_id,
        ]);
        if (!$order) {
            return [
                'code' => 1,
                'msg' => '订单不存在！',
            ];
        }
        if ($order->is_confirm != 1) {
            return [
                'code' => 1,
                'msg' => '订单尚未确认收货，无法评价！',
            ];
        }
        if ($order->is_comment == 1) {
            return [
                'code' => 1,
                'msg' => '订单已评价！',
            ];
        }
        $order_detail_list = OrderDetail::find()
            ->where(['order_id' => $order->id, 'is_delete' => 0])
            ->select('id order_detail_id,goods_id')->asArray()->all();
        foreach ($order_detail_list as $i => $order_detail) {
            $order_detail_list[$i]['goods_pic'] = Goods::getGoodsPicStatic($order_detail['goods_id'])->pic_url;
        }
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'order_id' => $order->id,
                'goods_list' => $order_detail_list,
            ],
        ];
    }

}