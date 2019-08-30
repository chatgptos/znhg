<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/12/5
 * Time: 17:37
 */

namespace app\models;


use xanyext\wechat\Wechat;
use yii\helpers\VarDumper;

class PtNoticeSender
{
    public $wechat;
    public $store_id;


    /**
     * PtNoticeSender constructor.
     * @param Wechat $wechat
     * @param integer $store_id
     */
    public function __construct($wechat, $store_id)
    {
        $this->wechat = $wechat;
        $this->store_id = $store_id;
    }

    /**
     * 发送拼团成功模板消息
     * @param integer $order_id 拼团订单id（团长订单或团员订单）
     * @return bool
     */
    public function sendSuccessNotice($order_id)
    {
        $tpl_id = Option::get('pintuan_success_notice', $this->store_id);
        if (!$tpl_id) {
            \Yii::warning('模板消息发送失败，模板消息ID未配置');
            return false;
        }
        $order = PtOrder::find()->alias('po')
            ->select('po.*,u.wechat_open_id,u.nickname,pg.name AS goods_name,fi.form_id')
            ->leftJoin(['u' => User::tableName()], 'po.user_id=u.id')
            ->leftJoin(['pod' => PtOrderDetail::find()->where(['is_delete' => 0])->orderBy('addtime DESC')], 'po.id=pod.order_id')
            ->leftJoin(['pg' => PtGoods::tableName()], 'pod.goods_id=pg.id')
            ->leftJoin(['fi' => FormId::find()->orderBy('id DESC')], 'po.order_no=fi.order_no')
            ->where([
                'AND',
                [
                    'po.id' => $order_id,
                    'po.is_pay' => 1,
                    'po.is_delete' => 0,
                    'po.status' => 3,
                    'po.is_success' => 1,
                ],
                ['IS NOT', 'pod.id', null],
            ])
            ->limit(1)
            ->asArray()
            ->one();
        if (!$order) {
            \Yii::warning('模板消息发送失败，订单不存在');
            return false;
        }
        if ($order['parent_id'] != 0) {
            $order = PtOrder::find()->alias('po')
                ->select('po.*,u.wechat_open_id,u.nickname,pg.name AS goods_name,fi.form_id')
                ->leftJoin(['u' => User::tableName()], 'po.user_id=u.id')
                ->leftJoin(['pod' => PtOrderDetail::find()->where(['is_delete' => 0])->orderBy('addtime DESC')], 'po.id=pod.order_id')
                ->leftJoin(['pg' => PtGoods::tableName()], 'pod.goods_id=pg.id')
                ->leftJoin(['fi' => FormId::find()->orderBy('id DESC')], 'po.order_no=fi.order_no')
                ->where([
                    'AND',
                    [
                        'po.id' => $order['parent_id'],
                        'po.is_pay' => 1,
                        'po.is_delete' => 0,
                        'po.status' => 3,
                        'po.is_success' => 1,
                    ],
                    ['IS NOT', 'pod.id', null],
                ])
                ->limit(1)
                ->asArray()
                ->one();
            if (!$order) {
                \Yii::warning('模板消息发送失败，订单不存在');
                return false;
            }
        }
        $sub_order_list = PtOrder::find()->alias('po')
            ->select('po.*,u.wechat_open_id,u.nickname,pg.name AS goods_name,fi.form_id')
            ->leftJoin(['u' => User::tableName()], 'po.user_id=u.id')
            ->leftJoin(['pod' => PtOrderDetail::find()->where(['is_delete' => 0])->orderBy('addtime DESC')], 'po.id=pod.order_id')
            ->leftJoin(['pg' => PtGoods::tableName()], 'pod.goods_id=pg.id')
            ->leftJoin(['fi' => FormId::find()->orderBy('id DESC')], 'po.order_no=fi.order_no')
            ->where([
                'AND',
                [
                    'po.parent_id' => $order['id'],
                    'po.is_pay' => 1,
                    'po.is_delete' => 0,
                    'po.status' => 3,
                    'po.is_success' => 1,
                ],
                ['IS NOT', 'pod.id', null],
            ])
            ->orderBy('po.addtime')
            ->asArray()
            ->all();
        $order_list = array_merge([$order], $sub_order_list);
        $nickname_list = [];
        foreach ($order_list as $order) {
            $nickname_list[] = $order['nickname'];
        }
        foreach ($order_list as $order) {
            if (!$order['form_id']) {
                \Yii::warning("拼团订单(id={$order['id']})未发送模板消息，form_id不存在");
                continue;
            }
            $data = [
                'touser' => $order['wechat_open_id'],
                'template_id' => $tpl_id,
                'page' => 'pages/pt/order/order?status=2',
                'form_id' => $order['form_id'],
                'data' => [
                    'keyword1' => [
                        'value' => $order['goods_name'],
                        'color' => '#555555',
                    ],
                    'keyword2' => [
                        'value' => $order['order_no'],
                        'color' => '#555555',
                    ],
                    'keyword3' => [
                        'value' => implode(',', $nickname_list),
                        'color' => '#555555',
                    ],
                ],
            ];
            $res = $this->sendNotice(json_encode($data, JSON_UNESCAPED_UNICODE));
            if ($res == false) {
                \Yii::warning("拼团订单(id={$order['id']})发送消息失败，网络错误");
                continue;
            }
            if (isset($res['errcode']) && $res['errcode'] != 0) {
                \Yii::warning("拼团订单(id={$order['id']})发送消息失败，" . (isset($res['errmsg']) ? $res['errmsg'] : null));
                continue;
            }
        }
        return true;
    }

    /**
     * 发送拼团失败消息
     * @param integer $order_id 拼团订单id（团长订单）
     */
    public function sendFailNotice($order_id)
    {
        $tpl_id = Option::get('pintuan_fail_notice', $this->store_id);
        if (!$tpl_id) {
            \Yii::warning('模板消息发送失败，模板消息ID未配置');
            return false;
        }
        $order = PtOrder::find()->alias('po')
            ->select('po.*,u.wechat_open_id,u.nickname,pg.name AS goods_name,fi.form_id')
            ->leftJoin(['u' => User::tableName()], 'po.user_id=u.id')
            ->leftJoin(['pod' => PtOrderDetail::find()->where(['is_delete' => 0])->orderBy('addtime DESC')], 'po.id=pod.order_id')
            ->leftJoin(['pg' => PtGoods::tableName()], 'pod.goods_id=pg.id')
            ->leftJoin(['fi' => FormId::find()->orderBy('id DESC')], 'po.order_no=fi.order_no')
            ->where([
                'AND',
                [
                    'po.id' => $order_id,
                    'po.is_pay' => 1,
                    'po.is_delete' => 0,
                    'po.status' => 4,
                    'po.parent_id' => 0,
                ],
                ['IS NOT', 'pod.id', null],
            ])
            ->limit(1)
            ->asArray()
            ->one();
        if (!$order) {
            \Yii::warning('模板消息发送失败，订单不存在');
            return false;
        }
        $sub_order_list = PtOrder::find()->alias('po')
            ->select('po.*,u.wechat_open_id,u.nickname,pg.name AS goods_name,fi.form_id')
            ->leftJoin(['u' => User::tableName()], 'po.user_id=u.id')
            ->leftJoin(['pod' => PtOrderDetail::find()->where(['is_delete' => 0])->orderBy('addtime DESC')], 'po.id=pod.order_id')
            ->leftJoin(['pg' => PtGoods::tableName()], 'pod.goods_id=pg.id')
            ->leftJoin(['fi' => FormId::find()->orderBy('id DESC')], 'po.order_no=fi.order_no')
            ->where([
                'AND',
                [
                    'po.parent_id' => $order['id'],
                    'po.is_pay' => 1,
                    'po.is_delete' => 0,
                    'po.status' => 4,
                ],
                ['IS NOT', 'pod.id', null],
            ])
            ->orderBy('po.addtime')
            ->asArray()
            ->all();
        $order_list = array_merge([$order], $sub_order_list);

        foreach ($order_list as $order) {
            if (!$order['form_id']) {
                \Yii::warning("拼团订单(id={$order['id']})未发送模板消息，form_id不存在");
                continue;
            }
            $data = [
                'touser' => $order['wechat_open_id'],
                'template_id' => $tpl_id,
                'page' => 'pages/pt/order/order?status=3',
                'form_id' => $order['form_id'],
                'data' => [
                    'keyword1' => [
                        'value' => $order['goods_name'],
                        'color' => '#555555',
                    ],
                    'keyword2' => [
                        'value' => '未在规定时间内凑集拼团人数',
                        'color' => '#555555',
                    ],
                    'keyword3' => [
                        'value' => $order['order_no'],
                        'color' => '#555555',
                    ],
                ],
            ];
            $res = $this->sendNotice(json_encode($data, JSON_UNESCAPED_UNICODE));
            if ($res == false) {
                \Yii::warning("拼团订单(id={$order['id']})发送消息失败，网络错误");
                continue;
            }
            if (isset($res['errcode']) && $res['errcode'] != 0) {
                \Yii::warning("拼团订单(id={$order['id']})发送消息失败，" . (isset($res['errmsg']) ? $res['errmsg'] : null));
                continue;
            }
        }
        return true;

    }


    public function sendRefundNotice($order_id)
    {

    }

    private function sendNotice($data)
    {
        if (!$this->wechat)
            return false;
        $access_token = $this->wechat->getAccessToken();
        $api = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token={$access_token}";
        $curl = $this->wechat->curl;
        $curl->post($api, $data);
        if ($curl->http_error)
            return false;
        return json_decode($curl->response, true);
    }
}