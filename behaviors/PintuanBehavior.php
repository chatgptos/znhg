<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/12/6
 * Time: 15:46
 */

namespace app\behaviors;


use app\extensions\PinterOrder;
use app\extensions\printerPtOrder;
use app\models\PrinterSetting;
use app\models\PtGoods;
use app\models\PtNoticeSender;
use app\models\PtOrder;
use app\models\Store;
use xanyext\wechat\Wechat;
use yii\base\ActionEvent;
use yii\base\Behavior;
use yii\helpers\VarDumper;
use yii\web\Controller;

class PintuanBehavior extends Behavior
{
    public $only_routes = [
        'mch/group/*',
        'api/group/*',
    ];

    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction',
        ];
    }

    /**
     * @param ActionEvent $event
     * @return bool
     */
    public function beforeAction($event)
    {
        if (DoNotDoIt::doNotDoIt($this->only_routes))
            return;
        \Yii::warning('----PINTUAN BEHAVIOR----');
        try {
            $this->checkOrderTimeout($event); //处理未在规定时间内成团的订单
            $this->checkOrderTimeoutRefund($event); //处理未在规定时间内成团的订单
            $this->checkNoPayOrderTimeout($event); //处理未在规定时间内付款的订单
            $this->checkGoodsTimeout($event); //处理超时的拼团商品自动下架
            $this->checkOrderConfirmTimeout($event); //处理超时自动确认收货的订单
        } catch (\Exception $e) {
        }
    }


    /**
     * 处理未在规定时间内成团的订单
     * @param ActionEvent $event
     * @return bool
     */
    private function checkOrderTimeout($event)
    {
        $order_max = 100;//每次最多处理的团购个数，防止运行太久
        $cache_key = 'pt_order_timeout_checker';
        if (\Yii::$app->cache->get($cache_key))
            return true;
        \Yii::$app->cache->set($cache_key, true, 10);

        /** @var Wechat $wechat */
        $wechat = isset($event->action->controller->wechat) ? $event->action->controller->wechat : null;
        if (!$wechat) {
            \Yii::$app->cache->set($cache_key, false);
            return true;
        }
        /** @var PtOrder[] $parent_order_list */
        $parent_order_list = PtOrder::find()->where([
            'AND',
            [
                'is_group' => 1,
                'parent_id' => 0,
                'is_pay' => 1,
                'status' => 2,
            ],
            ['IS NOT', 'limit_time', null,],
            ['<=', 'limit_time', time(),],
        ])->limit($order_max)->all();

        foreach ($parent_order_list as $parent_order) {
            $sub_order_list = PtOrder::find()->where([
                'AND',
                [
                    'is_group' => 1,
                    'parent_id' => $parent_order->id,
                    'is_pay' => 1,
                    'status' => 2,
                ],
            ])->all();
            /** @var PtOrder[] $order_list */
            $order_list = array_merge($sub_order_list, [$parent_order]);
            foreach ($order_list as $i => $order) {
                $order->status = 4;
//                $order->is_returnd = 1;
                $order->save(false);
            }
        }
        \Yii::$app->cache->set($cache_key, false);
        return true;
    }

    /**
     * 拼团检测退款
     * @param $event
     * @return bool
     */
    public function checkOrderTimeoutRefund($event)
    {
        $order_max = 100;//每次最多处理的团购个数，防止运行太久
        $cache_key = 'pt_order_timeout_checker';
        if (\Yii::$app->cache->get($cache_key))
            return true;
        \Yii::$app->cache->set($cache_key, true, 10);

        /** @var Wechat $wechat */
        $wechat = isset($event->action->controller->wechat) ? $event->action->controller->wechat : null;
        if (!$wechat) {
            \Yii::$app->cache->set($cache_key, false);
            return true;
        }

        /** @var PtOrder[] $parent_order_list */
        $parent_order_list = PtOrder::find()->where([
            'AND',
            [
                'is_group' => 1,
                'parent_id' => 0,
                'is_pay' => 1,
                'status' => 4,
                'is_returnd' => 0,
            ],
            ['IS NOT', 'limit_time', null,],
            ['<=', 'limit_time', time(),],
        ])->limit($order_max)->all();

        foreach ($parent_order_list as $parent_order) {
            $sub_order_list = PtOrder::find()->where([
                'AND',
                [
                    'is_group' => 1,
                    'parent_id' => $parent_order->id,
                    'is_pay' => 1,
                    'status' => 4,
                    'is_returnd' => 0,
                ],
            ])->all();

            /** @var PtOrder[] $order_list */
            $order_list = array_merge($sub_order_list, [$parent_order]);
            //VarDumper::dump($order_list, 4, 1);
            foreach ($order_list as $i => $order) {
                $res = $wechat->pay->refund([
                    'out_trade_no' => $order->order_no,
                    'out_refund_no' => $order->order_no,
                    'total_fee' => $order->pay_price * 100,
                    'refund_fee' => $order->pay_price * 100,
                ]);
                if (!$res && $i == 0) {
                    \Yii::warning('拼团退款失败，CURL错误：' . $wechat->curl->error_code . ' ' . $wechat->curl->error_message);
                    \Yii::$app->cache->set($cache_key, false);
                    return true;
                }
                if ($res['return_code'] != 'SUCCESS' && $i == 0) {
                    \Yii::warning('拼团退款失败，' . $res['return_msg']);
                    break;
                }
                if ($res['result_code'] != 'SUCCESS' && $i == 0) {
                    \Yii::warning('拼团退款失败，' . $res['err_code_des']);
                    break;
                }
//                $order->status = 4;
                $order->is_returnd = 1;
                $order->save(false);
            }

            $notice = new PtNoticeSender($wechat, $parent_order->store_id);
            $notice->sendFailNotice($parent_order->id);
        }
        \Yii::$app->cache->set($cache_key, false);
        //\Yii::$app->end();
        return true;
    }


    /**
     * 处理未在规定时间内付款的订单
     * @param ActionEvent $event
     * @return bool
     */
    private function checkNoPayOrderTimeout($event)
    {
        $order_max = 100;//每次最多处理的个数，防止运行太久
        $cache_key = 'pt_no_pay_order_timeout_checker';
        if (\Yii::$app->cache->get($cache_key))
            return true;
        \Yii::$app->cache->set($cache_key, true, 10);
        /** @var Store $store */
        $store = isset($event->action->controller->store) ? $event->action->controller->store : null;
        if (!$store) {
            \Yii::$app->cache->set($cache_key, false);
            return true;
        }

        if (!$store->over_day || $store < 0)
            return true;
        $expire_time = intval($store->over_day) * 3600;
        /** @var PtOrder[] $order_list */
        $order_list = PtOrder::find()->where([
            'AND',
            [
                'is_pay' => 0,
                'is_cancel' => 0,
            ],
            ['<=', 'addtime', time() - $expire_time],
        ])->limit($order_max)->all();
        foreach ($order_list as $order) {
            $order->is_cancel = 1;
            $order->save(false);
        }

        \Yii::$app->cache->set($cache_key, false);
        return true;
    }

    /**
     * 处理超时的拼团商品自动下架
     * @param ActionEvent $event
     * @return bool
     */
    private function checkGoodsTimeout($event)
    {
        $order_max = 100;//每次最多处理的个数，防止运行太久
        $cache_key = 'pt_goods_timeout_checker';
        if (\Yii::$app->cache->get($cache_key))
            return true;
        \Yii::$app->cache->set($cache_key, true, 10);

        /** @var PtGoods[] $goods_list */
        $goods_list = PtGoods::find()->where([
            'AND',
            [
                'is_delete' => 0,
            ],
            ['>', 'limit_time', 0],
            ['<=', 'limit_time', time()],
        ])->limit($order_max)->all();

        foreach ($goods_list as $goods) {
            $goods->limit_time = 0;
            $goods->status = 2;
            $goods->save(false);
        }
        \Yii::$app->cache->set($cache_key, false);
        return true;
    }

    /**
     * 处理超时自动确认收货的订单
     * @param ActionEvent $event
     * @return bool
     */
    private function checkOrderConfirmTimeout($event)
    {
        $order_max = 100;//每次最多处理的个数，防止运行太久
        $cache_key = 'pt_order_confirm_timeout_checker';
        if (\Yii::$app->cache->get($cache_key))
            return true;
        \Yii::$app->cache->set($cache_key, true, 10);

        /** @var Store $store */
        $store = isset($event->action->controller->store) ? $event->action->controller->store : null;
        if (!$store) {
            \Yii::$app->cache->set($cache_key, false);
            return true;
        }
        $delivery_time = intval($store->delivery_time);
        if ($delivery_time < 0)
            $delivery_time = 0;
        $expire_time = $delivery_time * 86400;

        /** @var PtOrder[] $order_list */
        $order_list = PtOrder::find()->where([
            'AND',
            [
                'is_send' => 1,
                'is_confirm' => 0,
            ],
            ['<=', 'send_time', time() - $expire_time],
        ])->limit($order_max)->all();

        foreach ($order_list as $order) {
            $order->is_confirm = 1;
            $order->confirm_time = time();
            $order->save(false);
            $printer_setting = PrinterSetting::findOne(['store_id' => $order->store_id, 'is_delete' => 0]);
            $type = json_decode($printer_setting->type, true);
            if ($type['confirm'] && $type['confirm'] == 1) {
                $printer_order = new printerPtOrder($order->store_id, $order->id);
                $res = $printer_order->print_order();
            }
        }

        \Yii::$app->cache->set($cache_key, false);
        return true;
    }
}