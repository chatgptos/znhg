<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/7/27
 * Time: 14:14
 */

namespace app\modules\mch\models;


use app\models\Goods;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\User;
use yii\helpers\VarDumper;

class StoreDataForm extends Model
{
    public $store_id;
    public $is_offline;
    public $user_id;
    public $clerk_id;
    public $parent_id;
    public $shop_id;

    public function search()
    {
        return $this->newSearch();

        $condition = ['store_id' => $this->store_id, 'is_delete' => 0, 'is_cancel' => 0];
        $condition_c = ['store_id' => $this->store_id];
        $condition_m = ['store_id' => $this->store_id, 'is_cancel' => 0];
        if ($this->is_offline) {
            $condition['is_offline'] = $this->is_offline;
            $condition_c['is_offline'] = $this->is_offline;
        }
        if ($this->user_id) {
            $condition['user_id'] = $this->user_id;
            $condition_c['user_id'] = $this->user_id;
        }
        if ($this->clerk_id) {
            $condition['clerk_id'] = $this->clerk_id;
        }
        if ($this->shop_id) {
            $condition['shop_id'] = $this->shop_id;
        }
        $data = [
            'all_count' => [
                'all' => Order::find()->where($condition)->count('1'),
                '1day' => Order::find()->where($condition)->andWhere([
                    'AND',
                    ['>=', 'addtime', strtotime(date('Y-m-d 00:00:00'))],
                    ['<=', 'addtime', strtotime(date('Y-m-d 23:59:59'))],
                ])->count('1'),
                '7day' => Order::find()->where($condition)->andWhere([
                    'AND',
                    ['>=', 'addtime', strtotime(date('Y-m-d 23:59:59')) - 86400 * 7],
                    ['<=', 'addtime', strtotime(date('Y-m-d 23:59:59'))],
                ])->count('1'),
                '30day' => Order::find()->where($condition)->andWhere([
                    'AND',
                    ['>=', 'addtime', strtotime(date('Y-m-d 23:59:59')) - 86400 * 30],
                    ['<=', 'addtime', strtotime(date('Y-m-d 23:59:59'))],
                ])->count('1'),
            ],
            'status_count' => [
                'status_0' => Order::find()->where($condition)->andWhere(['is_pay' => 0])->count('1'),
                'status_1' => Order::find()->where($condition)->andWhere(['is_pay' => 1, 'is_send' => 0])->count('1'),
                'status_2' => Order::find()->where($condition)->andWhere(['is_send' => 1, 'is_confirm' => 0])->count('1'),
                'status_3' => Order::find()->where($condition)->andWhere(['is_confirm' => 1])->count('1'),
                'status_5' => Order::find()->where($condition_c)->andWhere(['or', ['is_cancel' => 1], ['is_delete' => 1, 'apply_delete' => 1]])->count('1'),
            ],
            'money' => [
                'day' => Order::find()->where($condition_m)->andWhere([
                    'AND',
                    ['>=', 'addtime', strtotime(date('Y-m-d 00:00:00'))],
                    ['<=', 'addtime', strtotime(date('Y-m-d 23:59:59'))],
                    ['is_pay' => 1]
                ])->select(['sum(pay_price)'])->scalar(),
                'month' => Order::find()->where($condition_m)->andWhere([
                    'AND',
                    ['month(FROM_UNIXTIME(addtime))' => date('m')],
                    ['is_pay' => 1]
                ])->select(['sum(pay_price)'])->scalar(),
                'all' => Order::find()->where($condition_m)->andWhere([
                    'is_pay' => 1
                ])->select(['sum(pay_price)'])->scalar(),
                'refuse_1' => Order::find()->where($condition_m)->andWhere([
                    'is_pay' => 1, 'apply_delete' => 1, 'is_delete' => 1
                ])->select(['sum(pay_price)'])->scalar(),
                'refuse_2' => OrderRefund::find()->where(['store_id' => $this->store_id, 'is_delete' => 0, 'type' => 1])->andWhere([
                    'status' => 1
                ])->select(['sum(refund_price)'])->scalar(),
            ],
        ];
        return $data;
    }

    public function newSearch()
    {
        $data = [
            'panel_1' => $this->getStoreInfo(),
            'panel_2' => [
                'goods_zero_count' => $this->getCountZeroGoodsNum(),
                'order_no_send_count' => $this->getOrderNoSendCount(),
                'order_refunding_count' => $this->getOrderRefundingCount(),
            ],
            'panel_3' => [
                'data_1' => [
                    'order_goods_count' => $this->getOrderGoodsCount(
                        strtotime(date('Y-m-d 00:00:00')),
                        strtotime(date('Y-m-d 23:59:59'))
                    ),
                    'order_price_count' => $this->getOrderPriceCount(
                        strtotime(date('Y-m-d 00:00:00')),
                        strtotime(date('Y-m-d 23:59:59'))
                    ),
                    'order_price_average' => $this->getOrderPriceAverage(
                        strtotime(date('Y-m-d 00:00:00')),
                        strtotime(date('Y-m-d 23:59:59'))
                    ),
                ],
                'data_2' => [
                    'order_goods_count' => $this->getOrderGoodsCount(
                        strtotime(date('Y-m-d 00:00:00') . ' -1 day'),
                        strtotime(date('Y-m-d 23:59:59') . ' -1 day')
                    ),
                    'order_price_count' => $this->getOrderPriceCount(
                        strtotime(date('Y-m-d 00:00:00') . ' -1 day'),
                        strtotime(date('Y-m-d 23:59:59') . ' -1 day')
                    ),
                    'order_price_average' => $this->getOrderPriceAverage(
                        strtotime(date('Y-m-d 00:00:00') . ' -1 day'),
                        strtotime(date('Y-m-d 23:59:59') . ' -1 day')
                    ),
                ],
                'data_3' => [
                    'order_goods_count' => $this->getOrderGoodsCount(
                        strtotime(date('Y-m-d 00:00:00') . ' -6 day'),
                        strtotime(date('Y-m-d 23:59:59'))
                    ),
                    'order_price_count' => $this->getOrderPriceCount(
                        strtotime(date('Y-m-d 00:00:00') . ' -6 day'),
                        strtotime(date('Y-m-d 23:59:59'))
                    ),
                    'order_price_average' => $this->getOrderPriceAverage(
                        strtotime(date('Y-m-d 00:00:00') . ' -6 day'),
                        strtotime(date('Y-m-d 23:59:59'))
                    ),
                ],
                'data_4' => [
                    'order_goods_count' => $this->getOrderGoodsCount(
                        strtotime(date('Y-m-d 00:00:00') . ' -29 day'),
                        strtotime(date('Y-m-d 23:59:59'))
                    ),
                    'order_price_count' => $this->getOrderPriceCount(
                        strtotime(date('Y-m-d 00:00:00') . ' -29 day'),
                        strtotime(date('Y-m-d 23:59:59'))
                    ),
                    'order_price_average' => $this->getOrderPriceAverage(
                        strtotime(date('Y-m-d 00:00:00') . ' -29 day'),
                        strtotime(date('Y-m-d 23:59:59'))
                    ),
                ],
            ],
            'panel_4' => [
                'order_goods_data' => $this->getDaysOrderGoodsData(7),
                'order_goods_price_data' => $this->getDaysOrderGoodsPriceData(7),
            ],
            'panel_5' => [
                'data_1' => $this->getGoodsSaleTopList(
                    strtotime(date('Y-m-d 00:00:00')),
                    strtotime(date('Y-m-d 23:59:59')),
                    5
                ),
                'data_2' => $this->getGoodsSaleTopList(
                    strtotime(date('Y-m-d 00:00:00') . ' -1 day'),
                    strtotime(date('Y-m-d 23:59:59') . ' -1 day'),
                    5
                ),
                'data_3' => $this->getGoodsSaleTopList(
                    strtotime(date('Y-m-d 00:00:00') . ' -6 day'),
                    strtotime(date('Y-m-d 23:59:59')),
                    5
                ),
                'data_4' => $this->getGoodsSaleTopList(
                    strtotime(date('Y-m-d 00:00:00') . ' -29 day'),
                    strtotime(date('Y-m-d 23:59:59')),
                    5
                ),
            ],
            'panel_6' => $this->getUserTopList(10),
        ];
        $data['panel_4']['date'] = [];
        foreach ($data['panel_4']['order_goods_data']['list'] as $item) {
            $data['panel_4']['date'][] = $item['date'];
        }
        return [
            'code' => 0,
            'data' => $data,
        ];
    }

    public function getStoreInfo()
    {
        $goods_count = Goods::find()->where([
            'store_id' => $this->store_id,
            'is_delete' => 0,
        ])->count();
        $order_count = Order::find()->where([
            'store_id' => $this->store_id,
            'is_delete' => 0,
        ])->count();
        $user_count = User::find()->where([
            'store_id' => $this->store_id,
            'is_delete' => 0,
        ])->count();
        return [
            'user_count' => $user_count ? intval($user_count) : 0,
            'goods_count' => $goods_count ? intval($goods_count) : 0,
            'order_count' => $order_count ? intval($order_count) : 0,
        ];
    }

    /**
     * 获取售罄商品数量
     */
    public function getCountZeroGoodsNum()
    {
        $cache_key = 'zero_goods_nym_' . $this->store_id;
        $count = \Yii::$app->cache->get($cache_key);
        if ($count !== false)
            return $count;
        /** @var Goods[] $goods_list */
        $goods_list = Goods::find()->where([
            'is_delete' => 0,
            'store_id' => $this->store_id,
        ])->select('id,attr')->all();
        $count = 0;
        foreach ($goods_list as $goods) {
            if ($goods->getNum() == 0) {
                $count++;
            }
        }
        \Yii::$app->cache->set($cache_key, $count, 60);
        return $count;
    }

    /**
     * 获取待发货订单数
     */
    public function getOrderNoSendCount()
    {
        $count = Order::find()->where([
            'store_id' => $this->store_id,
            'is_delete' => 0,
            'is_pay' => 1,
            'is_send' => 0,
        ])->count();
        return $count ? intval($count) : 0;
    }

    /**
     * 获取售后中订单数
     */
    public function getOrderRefundingCount()
    {
        $count = OrderRefund::find()->where([
            'store_id' => $this->store_id,
            'is_delete' => 0,
            'status' => 0,
        ])->count();
        return $count ? intval($count) : 0;
    }

    /**
     * 获取订单商品总数
     */
    public function getOrderGoodsCount($start_time = null, $end_time = null)
    {
        $query = OrderDetail::find()->alias('od')
            ->leftJoin(['o' => Order::tableName()], 'od.order_id=o.id')
            ->where([
                'o.is_pay' => 1,
                'o.is_delete' => 0,
                'od.is_delete' => 0,
                'o.store_id' => $this->store_id,
            ]);
        if ($start_time !== null) {
            $query->andWhere(['>=', 'o.addtime', $start_time]);
        }
        if ($end_time !== null) {
            $query->andWhere(['<=', 'o.addtime', $end_time]);
        }
        $count = $query->select('SUM(od.num) AS num')->asArray()->one();
        return $count['num'] ? intval($count['num']) : 0;
    }

    /**
     * 获取订单金额总数（实际付款）
     */
    public function getOrderPriceCount($start_time = null, $end_time = null)
    {
        $query = Order::find()->alias('o')
            ->where([
                'o.is_pay' => 1,
                'o.is_delete' => 0,
                'o.store_id' => $this->store_id,
            ]);
        if ($start_time !== null) {
            $query->andWhere(['>=', 'o.addtime', $start_time]);
        }
        if ($end_time !== null) {
            $query->andWhere(['<=', 'o.addtime', $end_time]);
        }
        $count = $query->select('SUM(o.pay_price) AS total_price')->asArray()->one();
        return $count['total_price'] ? doubleval($count['total_price']) : 0;
    }

    /**
     * 获取订单平均消费金额（实际付款）
     */
    public function getOrderPriceAverage($start_time = null, $end_time = null)
    {
        $query = Order::find()->alias('o')
            ->where([
                'o.is_pay' => 1,
                'o.is_delete' => 0,
                'o.store_id' => $this->store_id,
            ]);
        if ($start_time !== null) {
            $query->andWhere(['>=', 'o.addtime', $start_time]);
        }
        if ($end_time !== null) {
            $query->andWhere(['<=', 'o.addtime', $end_time]);
        }
        $count = $query->count();
        $count = $count ? intval($count) : 0;
        if ($count == 0)
            return 0;
        $price = $this->getOrderPriceCount($start_time, $end_time) / $count;
        $price = doubleval(sprintf('%.2f', $price));
        return $price;
    }

    /**
     * 获取n日内每日销量
     */
    public function getDaysOrderGoodsData($days = 7)
    {
        $list = [];
        $data = [];
        for ($i = 0; $i < $days; $i++) {
            $start_time = strtotime(date('Y-m-d 00:00:00') . ' -' . $i . ' days');
            $end_time = strtotime(date('Y-m-d 23:59:59') . ' -' . $i . ' days');
            $date = date('m-d', $start_time);
            $val = $this->getOrderGoodsCount($start_time, $end_time);
            $list[] = [
                'date' => $date,
                'val' => $val,
                'start_time' => date('Y-m-d H:i:s', $start_time),
                'end_time' => date('Y-m-d H:i:s', $end_time),
            ];
            $data[] = $val;
        }
        return [
            'list' => array_reverse($list),
            'data' => array_reverse($data),
        ];
    }

    /**
     * 获取n日内每日成交额（已付款）
     */
    public function getDaysOrderGoodsPriceData($days = 7)
    {
        $list = [];
        $data = [];
        for ($i = 0; $i < $days; $i++) {
            $start_time = strtotime(date('Y-m-d 00:00:00') . ' -' . $i . ' days');
            $end_time = strtotime(date('Y-m-d 23:59:59') . ' -' . $i . ' days');
            $date = date('m-d', $start_time);
            $val = $this->getOrderPriceCount($start_time, $end_time);
            $list[] = [
                'date' => $date,
                'val' => $val,
                'start_time' => date('Y-m-d H:i:s', $start_time),
                'end_time' => date('Y-m-d H:i:s', $end_time),
            ];
            $data[] = $val;
        }
        return [
            'list' => array_reverse($list),
            'data' => array_reverse($data),
        ];
    }


    /**
     * 获取商品销量排行
     */
    public function getGoodsSaleTopList($start_time = null, $end_time = null, $limit = 10)
    {
        $query = OrderDetail::find()->alias('od')
            ->leftJoin(['o' => Order::tableName()], 'od.order_id=o.id')
            ->leftJoin(['g' => Goods::tableName()], 'od.goods_id=g.id')
            ->where([
                'g.store_id' => $this->store_id,
                'g.is_delete' => 0,
                'o.is_delete' => 0,
                'o.is_pay' => 1,
                'od.is_delete' => 0,
            ]);
        if ($start_time !== null) {
            $query->andWhere(['>=', 'o.addtime', $start_time]);
        }
        if ($end_time !== null) {
            $query->andWhere(['<=', 'o.addtime', $end_time]);
        }
        return $query->select('g.name,SUM(od.num) AS num')->groupBy('od.goods_id')->orderBy('num DESC')->limit($limit)->asArray()->all();
    }

    /**
     * 获取用户消费排行列表
     */
    public function getUserTopList($limit = 10)
    {
        $list = Order::find()->alias('o')->leftJoin(['u' => User::tableName()], 'o.user_id=u.id')
            ->where([
                'o.store_id' => $this->store_id,
                'o.is_pay' => 1,
                'o.is_delete' => 0,
            ])->groupBy('o.user_id')->limit($limit)->orderBy('money DESC')
            ->select('u.id,u.nickname,u.avatar_url AS avatar,SUM(o.pay_price) AS money')
            ->asArray()->all();
        if (!$list)
            return [];
        foreach ($list as $i => $item) {
            $money = doubleval($item['money']);
            if ($money >= 10000) {
                $list[$i]['money'] = number_format($money / 10000, 2, '.', '') . 'w';
            } else {
                $list[$i]['money'] = number_format($money, 2, '.', '');
            }
        }
        return $list;
    }
}