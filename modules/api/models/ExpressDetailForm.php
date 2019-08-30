<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/21
 * Time: 18:00
 */

namespace app\modules\api\models;


use app\models\Goods;
use app\models\Order;
use app\models\OrderDetail;
use yii\helpers\VarDumper;

class ExpressDetailForm extends Model
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
            'store_id' => $this->store_id,
            'id' => $this->order_id,
        ]);
        if (!$order)
            return [
                'code' => 1,
                'msg' => '订单不存在',
            ];

        $model = new \app\models\ExpressDetailForm();
        $model->express = $order->express;
        $model->express_no = $order->express_no;
        $model->store_id = $this->store_id;
        $res = $model->search();
        if ($res['code'] == 0) {
            $res['data']['express'] = $order->express;
            $res['data']['express_no'] = $order->express_no;
            $od = OrderDetail::findOne([
                'order_id' => $order->id,
                'is_delete' => 0,
            ]);
            if ($od) {
                $res['data']['goods_pic'] = Goods::getGoodsPicStatic($od->goods_id)->pic_url;
            } else {
                $res['data']['goods_pic'] = '';
            }
        }
        return $res;
    }
}