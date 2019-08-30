<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/7/20
 * Time: 10:25
 */

namespace app\modules\api\models\group;


use app\extensions\PinterOrder;
use app\extensions\printerPtOrder;
use app\models\Order;
use app\models\PrinterSetting;
use app\models\PtOrder;
use app\modules\api\models\Model;

class OrderConfirmForm extends Model
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

    public function save()
    {
        if (!$this->validate()) {
            return $this->getModelError();
        }
        $order = PtOrder::findOne([
            'store_id' => $this->store_id,
            'user_id' => $this->user_id,
            'id' => $this->order_id,
            'is_pay' => 1,
            'is_send' => 1,
            'is_delete' => 0,
        ]);
        if (!$order) {
            return [
                'code' => 1,
                'msg' => '订单不存在'
            ];
        }
        $order->is_confirm = 1;
        $order->confirm_time = time();


        if ($order->save()) {
            $printer_setting = PrinterSetting::findOne(['store_id'=>$this->store_id,'is_delete'=>0]);
            $type = json_decode($printer_setting->type,true);
            if($type['confirm'] && $type['confirm'] == 1){
                $printer_order = new printerPtOrder($this->store_id,$order->id);
                $res = $printer_order->print_order();
            }
            return [
                'code' => 0,
                'msg' => '已确认收货'
            ];
        } else {
            return [
                'code' => 1,
                'msg' => '确认收货失败'
            ];
        }
    }
}