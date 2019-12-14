<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/7
 * Time: 10:55
 */

namespace app\modules\api\models\crowdc;

use app\modules\api\models\Model;

class OrderRefundPreviewForm extends Model
{
    public $store_id;
    public $user_id;
    public $order_detail_id;

    public function rules()
    {
        return [
            [['order_detail_id'], 'required'],
        ];
    }

    public function search()
    {
        if (!$this->validate())
            return $this->getModelError();
        $data = OrderDetail::find()->alias('od')->leftJoin(['g' => Goods::tableName()], 'od.goods_id=g.id')->leftJoin(['o' => Order::tableName()], 'od.order_id=o.id')
            ->where([
                'o.is_delete' => 0,
                'o.user_id' => $this->user_id,
                'o.store_id' => $this->store_id,
                'od.id' => $this->order_detail_id,
            ])->select('od.id AS order_detail_id,g.id AS goods_id,g.name,od.attr,od.num,od.total_price,o.pay_price')->asArray()->one();
        if (!$data) {
            return [
                'code' => 1,
                'msg' => '订单不存在',
            ];
        }
        $data['attr'] = json_decode($data['attr']);
        $data['goods_pic'] = Goods::getGoodsPicStatic($data['goods_id'])->pic_url;
        $data['max_refund_price'] = min($data['total_price'], $data['pay_price']);
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => $data,
        ];
    }
}