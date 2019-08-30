<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2018/1/3
 * Time: 15:33
 */

namespace app\models;

use xanyext\wechat\Wechat;


/**
 * @property Store $store
 * @property Cash $cash
 * @property WechatTemplateMessage $wechat_template_message
 * @property User $user
 * @property FormId $form_id
 * @property Wechat $wechat
 */
class CashWechatTplSender
{

    public $store_id;
    public $cash_id;

    public $store;
    public $cash;
    public $wechat_template_message;
    public $user;
    public $form_id;
    public $wechat;

    /**
     * @param integer $store_id
     * @param integer $order_id
     * @param Wechat $wechat
     */
    public function __construct($store_id, $cash_id, $wechat)
    {
        $this->store_id = $store_id;
        $this->wechat = $wechat;
        $this->store = Store::findOne($this->store_id);
        $this->cash = Cash::findOne(['id' => $cash_id]);
        $this->wechat_template_message = Option::getList('cash_success_tpl,cash_fail_tpl', $this->store->id, 'share', '');
        if (!$this->cash)
            return;
        $this->user = User::findOne($this->cash->user_id);
        $this->form_id = FormId::find()->where(['order_no' => 'cash' . md5("id={$this->cash->id}&store_id={$this->store_id}")])->orderBy('id DESC')->one();
    }

    /**
     * 发送提现到账模板消息
     */
    public function cashMsg()
    {
        try {
            if (!$this->wechat_template_message['cash_success_tpl'])
                return;
            $value = $this->cash->type == 1 ? "请前往支付宝查看" : "请前往微信查看";
            $data = [
                'touser' => $this->user->wechat_open_id,
                'template_id' => $this->wechat_template_message['cash_success_tpl'],
                'form_id' => $this->form_id->form_id,
                'page' => 'pages/cash-detail/cash-detail',
                'data' => [
                    'keyword1' => [
                        'value' => $this->cash->price,
                        'color' => '#333333',
                    ],
                    'keyword2' => [
                        'value' => $value,
                        'color' => '#333333',
                    ],
                    'keyword3' => [
                        'value' => '实时到账',
                        'color' => '#333333',
                    ],
                ],
            ];
            $this->sendTplMsg($data);
        } catch (\Exception $e) {
            \Yii::warning($e->getMessage());
        }
    }

    /**
     * 发送提现失败模板消息
     */
    public function cashFailMsg()
    {
        try {
            if (!$this->wechat_template_message['cash_fail_tpl'])
                return;
            $data = [
                'touser' => $this->user->wechat_open_id,
                'template_id' => $this->wechat_template_message['cash_fail_tpl'],
                'form_id' => $this->form_id->form_id,
                'page' => 'pages/cash-detail/cash-detail',
                'data' => [
                    'keyword1' => [
                        'value' => $this->cash->price,
                        'color' => '#333333',
                    ],
                    'keyword2' => [
                        'value' => '审核不通过',
                        'color' => '#333333',
                    ],
                ],
            ];
            $this->sendTplMsg($data);
        } catch (\Exception $e) {
            \Yii::warning($e->getMessage());
        }
    }


    private function sendTplMsg($data)
    {
        $access_token = $this->wechat->getAccessToken();
        $api = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token={$access_token}";
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $this->wechat->curl->post($api, $data);
        $res = json_decode($this->wechat->curl->response, true);
        if (!empty($res['errcode']) && $res['errcode'] != 0) {
            \Yii::warning("模板消息发送失败：\r\ndata=>{$data}\r\nresponse=>" . json_encode($res, JSON_UNESCAPED_UNICODE));
        }
    }
}