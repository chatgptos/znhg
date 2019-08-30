<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/7/24
 * Time: 18:42
 */

namespace app\modules\mch\models\group;

use app\models\PtOrder;

use app\modules\mch\models\Model;

class OrderSendForm extends Model
{
    public $store_id;
    public $order_id;
    public $express;
    public $express_no;
    public $words;

    public function rules()
    {
        return [
            [['express', 'express_no','words'], 'trim'],
            [['express', 'express_no',], 'required','on'=>'EXPRESS'],
            [['order_id'],'required'],
            [['express', 'express_no',], 'string',],
            [['express', 'express_no',], 'default','value'=>''],
        ];
    }

    public function attributeLabels()
    {
        return [
            'express' => '快递公司',
            'express_no' => '快递单号',
            'words' => '商家留言',
        ];
    }

    public function save()
    {
        if (!$this->validate())
            return $this->getModelError();
        $order = PtOrder::findOne([
            'is_delete' => 0,
            'store_id' => $this->store_id,
            'id' => $this->order_id,
            'is_pay' => 1,
        ]);
        if (!$order) {
            return [
                'code' => 1,
                'msg' => '订单不存在或已删除',
            ];
        }
        $order->express = $this->express;
        $order->express_no = $this->express_no;
        $order->words = $this->words;
        $order->is_send = 1;
        $order->send_time = time();
        if ($order->save()) {
            try {
                $wechat_tpl_meg_sender = new WechatTplMsgSender($this->store_id, $order->id, $this->getWechat());
                $wechat_tpl_meg_sender->sendMsg();
            } catch (\Exception $e) {
                \Yii::warning($e->getMessage());
            }
            return [
                'code' => 0,
                'msg' => '发货成功',
            ];
        } else {
            return [
                'code' => 1,
                'msg' => '操作失败',
            ];
        }
    }

}