<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/7/17
 * Time: 11:48
 */

namespace app\modules\api\models\bookmall;


use app\models\Address;
use app\models\Cart;
use app\models\Coupon;
use app\models\CouponAutoSend;
use app\models\Form;
use app\models\Level;
use app\models\Option;
use app\models\PostageRules;
use app\models\Shop;
use app\models\Store;
use app\models\User;
use app\models\UserCoupon;
use app\modules\api\models\Model;

class OrderSubmitPreviewForm extends Model
{
    public $store_id;
    public $user_id;

    public $address_id;

    public $cart_id_list;
    public $goods_info;

    public $longitude;
    public $latitude;

    public function rules()
    {
        return [
            [['cart_id_list', 'goods_info'], 'string'],
            [['address_id',], 'integer'],
            [['longitude', 'latitude'], 'trim']
        ];
    }

    public function search()
    {
        $store = Store::findOne($this->store_id);
        if (!$this->validate())
            return $this->getModelError();
        if ($this->cart_id_list)
            $res = $this->getDataByCartIdList($this->cart_id_list, $store);

        if ($this->goods_info)
            $res = $this->getDataByGoodsInfo($this->goods_info, $store);

        $buyMaxRes = $this->checkBuyMax($res['data']['list']);
        if ($buyMaxRes)
            return $buyMaxRes;

        if ($res['code'] == 0) {
            $res['data']['coupon_list'] = $this->getCouponList($res['data']['total_price']);
            $res['data']['shop_list'] = $this->getShopList();

        }
        $level = Level::find()->select([
            'name', 'level', 'discount'
        ])->where(['level' => \Yii::$app->user->identity->level, 'store_id' => $this->store_id])->asArray()->one();
        $res['data']['level'] = $level;
        $res['data']['send_type'] = $store->send_type;
        // 获取 店铺积分使用规则
        $res['data']['integral']['integration'] = $store->integration;
        $res['data']['integral']['integrationyushou'] = $store->integrationyushou;
        // 获取用户当前积分
        $user = User::findOne(['id' => $this->user_id, 'type' => 1, 'is_delete' => 0]);
        if ($user->integral < $res['data']['integral']['forehead_integral']) {
            $res['data']['integral']['forehead_integral'] = $user->integral;
            $res['data']['integral']['forehead'] = sprintf("%.2f", $user->integral / $store->integral);
        }
        $res['data']['form']['is_form'] = Option::get('is_form', $this->store_id, 'admin', 0);
        $res['data']['form']['name'] = Option::get('form_name', $this->store_id, 'admin', '表单信息');
        $form_list = Form::find()->where([
            'store_id' => $this->store_id, 'is_delete' => 0
        ])->asArray()->all();
        foreach ($form_list as $index => $value) {
            if (in_array($value['type'], ['radio', 'checkbox'])) {
                $default = str_replace("，", ",", $value['default']);
                $list = explode(',', $default);
                $default_list = [];
                foreach ($list as $k => $v) {
                    $default_list[$k]['name'] = $v;
                    if ($k == 0) {
                        $default_list[$k]['is_selected'] = 1;
                    } else {
                        $default_list[$k]['is_selected'] = 0;
                    }
                }
                $form_list[$index]['default_list'] = $default_list;
            }
        }
        $res['data']['form']['list'] = $form_list;
        return $res;
    }

    private function getCouponList($goods_total_price)
    {
        $list = UserCoupon::find()->alias('uc')
            ->leftJoin(['c' => Coupon::tableName()], 'uc.coupon_id=c.id')
            ->leftJoin(['cas' => CouponAutoSend::tableName()], 'uc.coupon_auto_send_id=cas.id')
            ->where([
                'AND',
                ['uc.is_delete' => 0],
                ['uc.is_use' => 0],
                ['uc.is_expire' => 0],
                ['uc.user_id' => $this->user_id],
                ['<=', 'c.min_price', $goods_total_price],
            ])
            ->select('uc.id user_coupon_id,c.sub_price,c.min_price,cas.event,uc.begin_time,uc.end_time,uc.type')
            ->asArray()->all();
        $events = [
            0 => '平台发放',
            1 => '分享红包',
            2 => '购物返券',
            3 => '领券中心'
        ];
        foreach ($list as $i => $item) {
            $list[$i]['status'] = 0;
            if (isset($item['is_use']))
                $list[$i]['status'] = 1;
            if (isset($item['is_expire']))
                $list[$i]['status'] = 2;
            $list[$i]['min_price_desc'] = $item['min_price'] == 0 ? '无门槛' : '满' . $item['min_price'] . '元可用';
            $list[$i]['begin_time'] = date('Y.m.d H:i', $item['begin_time']);
            $list[$i]['end_time'] = date('Y.m.d H:i', $item['end_time']);
            if (!$item['event']) {
                if ($item['type'] == 2) {
                    $list[$i]['event'] = $item['event'] = 3;
                } else {
                    $list[$i]['event'] = $item['event'] = 0;
                }
            }
            $list[$i]['event_desc'] = $events[$item['event']];
            $list[$i]['min_price'] = doubleval($item['min_price']);
            $list[$i]['sub_price'] = doubleval($item['sub_price']);
        }

        return $list;
    }

    /**
     * @param string $cart_id_list eg. [12,32,7]
     */
    private function getDataByCartIdList($cart_id_list, $store)
    {
        /* @var  Cart[] $cart_list */
        $cart_list = Cart::find()->where([
            'store_id' => $this->store_id,
            'user_id' => $this->user_id,
            'is_delete' => 0,
            'id' => json_decode($cart_id_list, true),
        ])->all();
        $list = [];
        $total_price = 0;
        $new_cart_id_list = [];
        $goodsList = [];
        $resIntegral = [
            'forehead' => 0,
            'forehead_integral' => 0,
        ];
        $goodsIds = [];
        $goods_card_list = [];
        foreach ($cart_list as $item) {
            $goods = Goods::findOne([
                'store_id' => $this->store_id,
                'id' => $item->goods_id,
                'is_delete' => 0,
                'status' => 1,
            ]);
            if (!$goods)
                continue;
            $attr_list = Attr::find()->alias('a')
                ->select('ag.attr_group_name,a.attr_name')
                ->leftJoin(['ag' => AttrGroup::tableName()], 'a.attr_group_id=ag.id')
                ->where(['a.id' => json_decode($item->attr, true)])
                ->asArray()->all();
            $goods_attr_info = $goods->getAttrInfo(json_decode($item->attr, true));
            $attr_num = intval(empty($goods_attr_info['num']) ? 0 : $goods_attr_info['num']);
            $goods_pic = isset($goods_attr_info['pic']) ? $goods_attr_info['pic'] ?: $goods->getGoodsPic(0)->pic_url : $goods->getGoodsPic(0)->pic_url;
            if ($attr_num < $item->num)
                continue;
            $new_item = (object)[
                'cart_id' => $item->id,
                'goods_id' => $goods->id,
                'goods_name' => $goods->name,
                'goods_pic' => $goods_pic,
//                'goods_pic' => $goods->getGoodsPic(0)->pic_url,
                'num' => $item->num,
                'price' => doubleval(empty($goods_attr_info['price']) ? $goods->price : $goods_attr_info['price']) * $item->num,
                'attr_list' => $attr_list,
                'give' => 0,
            ];

            //秒杀价计算
            $seckill_data = $this->getSeckillData($goods, json_decode($item->attr, true));
            if ($seckill_data) {
                $temp_price = $this->getSeckillPrice($seckill_data, $goods, json_decode($item->attr, true), $item->num);
                if ($temp_price !== false)
                    $new_item->price = $temp_price;
            }

            $total_price += $new_item->price;
            $new_cart_id_list[] = $item->id;
            $list[] = $new_item;
            $goods_card = Goods::getGoodsCard($goods->id);
            $goods_card_list = array_merge($goods_card_list, $goods_card);
            $new_goods = [
                'goods_id' => $goods->id,
                'goods_name' => $goods->name,
                'freight' => $goods->freight,
                'weight' => $goods->weight,
                'num' => $item->num,
                'full_cut' => $goods->full_cut,
                'price' => $new_item->price,
            ];

            $goodsList[] = $new_goods;

            // 积分
            $integral = json_decode($goods->integral);
            if ($integral) {
                $give = $integral->give;
                if (strpos($give, '%') !== false) {
                    // 百分比
                    $give = trim($give, '%');
//                    $new_item->give = ($new_item->price * ($give/100)) * $store->integral;
                    $new_item->give = (int)($new_item->price * ($give / 100));
                } else {
                    // 固定积分
                    $new_item->give = (int)($give * $new_item->num);
                }

                $forehead = (int)$integral->forehead;
                if (strpos($forehead, '%') !== false) {
                    $forehead = trim($forehead, '%');
                    if ($forehead >= 100) {
                        $forehead = 100;
                    }
                    if ($integral->more == '1') {
                        $resIntegral['forehead_integral'] += (int)(($forehead / 100) * $new_item->price * $store->integral);
                    } elseif ($integral->more != '1' && !in_array($goods->id, $goodsIds)) {
                        $resIntegral['forehead_integral'] += (int)(($forehead / 100) * (empty($goods_attr_info['price']) ? $goods->price : $goods_attr_info['price']) * $store->integral);
                    }
                } else {
                    if ($integral->more == '1') {
                        if ($new_item->price > ($forehead * $new_item->num)) {
                            $resIntegral['forehead_integral'] += (int)(($forehead * $new_item->num) * $store->integral);
                        } else {
                            $resIntegral['forehead_integral'] += (int)($store->integral * $new_item->price);
                        }
                    } else {
                        $goodsPrice = (empty($goods_attr_info['price']) ? $goods->price : $goods_attr_info['price']);
                        if ($goodsPrice > $forehead) {
                            $resIntegral['forehead_integral'] += (int)($forehead * $store->integral);
                        } else {
                            $resIntegral['forehead_integral'] += (int)($store->integral * $goodsPrice);
                        }
                    }
                }

                // 记录下 商品id
                $goodsIds[] = $goods->id;
                $resIntegral['forehead'] = sprintf("%.2f", ($resIntegral['forehead_integral'] / $store->integral));
//                $resIntegral['forehead_integral'] = $resIntegral['forehead'] * $store->integral;
            }
        }
//var_dump($resIntegral);die();
        if (count($list) == 0) {
            return [
                'code' => 1,
                'msg' => '商品不存在或已下架',
            ];
        }

        $address = Address::find()->select('id,name,mobile,province_id,province,city_id,city,district_id,district,detail,is_default')->where([
            'id' => $this->address_id,
            'store_id' => $this->store_id,
            'user_id' => $this->user_id,
            'is_delete' => 0,
        ])->asArray()->one();
        if (!$address) {
            $address = Address::find()->select('id,name,mobile,province_id,province,city_id,city,district_id,district,detail,is_default')->where([
                'store_id' => $this->store_id,
                'user_id' => $this->user_id,
                'is_delete' => 0,
            ])->orderBy('is_default DESC,addtime DESC')->asArray()->one();
        }
        $express_price = 0;
        if ($address) {
            $resGoodsList = (new Goods)->cutFull($goodsList);
//            var_dump($resGoodsList);die();
            $express_price = PostageRules::getExpressPriceMore($this->store_id, $address['province_id'], $resGoodsList);
//            $express_price = PostageRules::getExpressPriceMore($this->store_id, $address['province_id'],$goodsList);
//            $express_price = PostageRules::getExpressPrice($this->store_id, $address['province_id']);
        }

        return [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'total_price' => $total_price,
                'cart_id_list' => $new_cart_id_list,
                'list' => $list,
                'address' => $address,
                'express_price' => $express_price,
                'integral' => $resIntegral,
                'goods_card_list' => $goods_card_list
            ],
        ];

    }

    /**
     * @param string $goods_info
     * JSON,eg.{"goods_id":"22","attr":[{"attr_group_id":1,"attr_group_name":"颜色","attr_id":3,"attr_name":"橙色"},{"attr_group_id":2,"attr_group_name":"尺码","attr_id":7,"attr_name":"L"}],"num":1}
     */
    private function getDataByGoodsInfo($goods_info, $store)
    {
        $goods_info = json_decode($goods_info);
        $goods = Goods::findOne([
            'id' => $goods_info->goods_id,
            'is_delete' => 0,
            'store_id' => $this->store_id,
            'status' => 1,
        ]);
        if (!$goods) {
            return [
                'code' => 1,
                'msg' => '商品不存在或已下架',
            ];
        }

        $attr_id_list = [];
        foreach ($goods_info->attr as $item) {
            array_push($attr_id_list, $item->attr_id);
        }
        $total_price = 0;
        //优惠券个数
        $advance_coupon = 0;
        $advance_integral_buy = 0;


        $goods_attr_info = $goods->getAttrInfo($attr_id_list);


        $attr_list = Attr::find()->alias('a')
            ->select('ag.attr_group_name,a.attr_name')
            ->leftJoin(['ag' => AttrGroup::tableName()], 'a.attr_group_id=ag.id')
            ->where(['a.id' => $attr_id_list])
            ->asArray()->all();
        $goods_pic = isset($goods_attr_info['pic']) ? $goods_attr_info['pic'] ?: $goods->getGoodsPic(0)->pic_url : $goods->getGoodsPic(0)->pic_url;
        $goods_item = (object)[
            'goods_id' => $goods->id,
            'goods_name' => $goods->name,
            'goods_pic' => $goods_pic,
//            'goods_pic' => $goods->getGoodsPic(0)->pic_url,
            'num' => $goods_info->num,
            'price' => intval(empty($goods_attr_info['price']) ? $goods->price : $goods_attr_info['price']) * $goods_info->num,
            'integral_buy' => intval(empty($goods_attr_info['price']) ? $goods->price : $goods_attr_info['price']) * $goods_info->num,
            'attr_list' => $attr_list,
            'coupon' => intval(empty($goods_attr_info['coupon']) ? $goods->coupon : $goods_attr_info['coupon']) * $goods_info->num,
//            'integral_buy' => doubleval(empty($goods_attr_info['integral_buy']) ? $goods->integral_buy : $goods_attr_info['integral_buy']) * $goods_info->num,
            'integral_buy' => intval(empty($goods_attr_info['integral_buy']) ? $goods->integral_buy : $goods_attr_info['integral_buy']) * $goods_info->num,
            'give' => 0,
            'advance' => intval($goods->advance),//预售款比例
        ];
        //秒杀价计算
        $seckill_data = $this->getSeckillData($goods, $attr_id_list);

        if ($seckill_data) {
            $temp_price = $this->getSeckillPrice($seckill_data, $goods, $attr_id_list, $goods_info->num);
            //查询当前总共订单量
//            $query_num_buy_order = Goods::find()->alias('g')->where(['g.id' => $goods->id, 'g.is_delete' => 0, 'g.store_id' => $this->store_id])
//                ->leftJoin(['od' => OrderDetail::tableName()], 'od.goods_id=g.id')
//                ->leftJoin(['o' => Order::tableName()], 'o.id=od.order_id')
//                ->andWhere([
//                    'or',
//                    [
//                        'od.is_delete' => 0,
//                        'o.is_delete' => 0,
//                        'o.is_pay' => 1,
//                        'o.pay_time' => date('Y-m-d'),
//                    ],
//                    'isnull(o.id)'
//                ])->count();

            $num = $seckill_data['sell_num'];
            $charge_coupon = 1;
            $charge_integral_buy = 1;

            if ($goods->is_buy_integral_down) {
                $charge_integral_buy = $this->getCharge($num, $goods);
            }

            if ($goods->is_coupon_down) {
                $charge_coupon = $this->getCharge($num, $goods);
            }
            if ($temp_price !== false) {
//                $goods_item->price = intval($temp_price['total_price'] * (1 - $charge_coupon / 100));
//                $goods_item->coupon = intval($seckill_data['seckill_coupon'] * (1 - $charge_coupon / 100));
//                $goods_item->integral_buy = intval($temp_price['total_price'] * (1 - $charge_integral_buy / 100));


                //取出秒杀价格
                $goods_item->price = $temp_price['total_price'];
                $goods_item->coupon = $seckill_data['seckill_coupon'];
                $goods_item->integral_buy = $temp_price['total_price'];
                //先算出首款固定就是价格*
                //优惠券个数 固定了
                $advance_coupon = intval($goods_item->coupon) * ($goods_item->advance / 100);
                $advance_integral_buy = intval($goods_item->integral_buy) * ($goods_item->advance / 100);


                $total_price += $goods_item->integral_buy;
                //计算出余款价格
                $goods_item->price = intval($temp_price['total_price'] * (1 - $charge_coupon / 100));
                $goods_item->coupon = intval($seckill_data['seckill_coupon'] * (1 - $charge_coupon / 100));
                $goods_item->integral_buy = intval($temp_price['total_price'] * (1 - $charge_integral_buy / 100));


                //余款
                $yukuan_coupon = intval($goods_item->coupon) * (1 - $goods_item->advance / 100);
                $yukuan_integral_buy = intval($goods_item->integral_buy) * (1 - $goods_item->advance / 100);



            } else {
                return [
                    'code' => 1,
                    'msg' => '秒杀商品库存不足',
                ];
            }
        } else {
            return [
                'code' => 1,
                'msg' => '未到开放时间',
            ];
        }

        //现在需要把商品的优惠券转换成秒杀的并且限制个数
        //设置的参数：
        /*
         *0.设置秒杀价格和秒杀优惠券和积分      ok
         *1.秒杀价格计算出来的 下单积分和优惠券  ok
         *2.秒杀生成订单根据比例advance
         *
         *3.根据商品设置的层级价格     1.判断数量属于哪个区间 2.秒杀价格*比例=下单价格
         *5.现实首页的下一个阶段的价格 和 数量
         *4.整个流程：后台设置 用户下单 （显示下一阶段价格）用户购买支付预售款 用户支付余款 发货
         *
         *
         * */
//        $total_price += $goods_item->price;
//        //优惠券个数
////        $advance_coupon += $goods_item->coupon;
////        $advance_integral_buy += $goods_item->integral_buy;
//
//
//        //优惠券个数
//        $advance_coupon += ($goods_item->coupon) * ($goods_item->advance / 100);
//        $advance_integral_buy += ($goods_item->integral_buy) * ($goods_item->advance / 100);
//        //余款
//        $yukuan_coupon = ($goods_item->coupon) * (1 - $goods_item->advance / 100);
//        $yukuan_integral_buy = ($goods_item->integral_buy) * (1 - $goods_item->advance / 100);


        $address = Address::find()->select('id,name,mobile,province_id,province,city_id,city,district_id,district,detail,is_default')->where([
            'id' => $this->address_id,
            'store_id' => $this->store_id,
            'user_id' => $this->user_id,
            'is_delete' => 0,
        ])->asArray()->one();
        if (!$address) {
            $address = Address::find()->select('id,name,mobile,province_id,province,city_id,city,district_id,district,detail,is_default')->where([
                'store_id' => $this->store_id,
                'user_id' => $this->user_id,
                'is_delete' => 0,
            ])->orderBy('is_default DESC,addtime DESC')->asArray()->one();
        }
//        var_dump($goods_info->num);die();
        $express_price = 0;
        if ($address) {
            if ($goods['full_cut']) {
                $full_cut = json_decode($goods['full_cut'], true);
            } else {
                $full_cut = json_decode([
                    'pieces' => 0,
                    'forehead' => 0,
                ], true);
            }

            if ((empty($full_cut['pieces']) || $goods_info->num < ($full_cut['pieces'] ?: 0)) && (empty($full_cut['forehead']) || $goods_item->price < ($full_cut['forehead'] ?: 0))) {
                $express_price = PostageRules::getExpressPrice($this->store_id, $address['province_id'], $goods, $goods_info->num);
            }
//            $express_price = PostageRules::getExpressPrice($this->store_id, $address['province_id'],$goods,$goods_info->num);
        }

        // 积分
        $integral = json_decode($goods->integral);
//        var_dump($integral);
        $resIntegral = [
            'forehead' => 0,
            'forehead_integral' => 0,
        ];
        if ($integral) {
            $give = $integral->give;
            if (strpos($give, '%') !== false) {
                // 百分比
                $give = trim($give, '%');
                $goods_item->give = (int)($goods_item->price * ($give / 100));
//                $goods_item->give = ($goods_item->price * ($give/100)) * $store->integral;
            } else {
                // 固定积分
                $goods_item->give = (int)($give * $goods_info->num);
            }

            //会卡死了
            //查询当前用户订单
            $query_num_buy_order = Goods::find()->alias('g')->where(['o.user_id' => $this->user_id, 'g.id' => $goods->id, 'g.is_delete' => 0, 'g.store_id' => $this->store_id])
                ->leftJoin(['od' => OrderDetail::tableName()], 'od.goods_id=g.id')
                ->leftJoin(['o' => Order::tableName()], 'o.id=od.order_id')
                ->andWhere([
                    'or',
                    [
                        'od.is_delete' => 0,
                        'o.is_delete' => 0,
                        'o.is_pay' => 1,
                        'o.is_confirm' => 1],
                    'isnull(o.id)'
                ])->count();
//
//            if($goods->integral_give_num && $query_num_buy_order > 0){
//                $goods_item->give = 0;
//            }
            if ($goods->integral_give_num) {
                if ($query_num_buy_order > 0) {
                    //订单大于0个 不发放
                    $goods_item->give = 0;
                } else {
                    //没有下过订单 正常方法
                    if ($goods->num > 1) {//数量大于1
                        $goods_item->give = (int)($goods_item->give / $goods_info->num);
                        //只发放一次
                    }
                }
            }
            //结束

            $forehead = $integral->forehead;
            if (strpos($forehead, '%') !== false) {
                $forehead = trim($forehead, '%');
                if ($forehead >= 100) {
                    $forehead = 100;
                }
                if ($integral->more == '1') {
                    $resIntegral['forehead_integral'] = (int)(($forehead / 100) * $goods_item->price * $store->integral);
                } else {
                    $resIntegral['forehead_integral'] = (int)(($forehead / 100) * (empty($goods_attr_info['price']) ? $goods->price : $goods_attr_info['price']) * $store->integral);
                }
            } else {
//                if ($integral->more == '1') {
//                    $resIntegral['forehead'] = sprintf("%.2f", ($forehead * $goods_item->price));
//                } else {
//                    $resIntegral['forehead'] = sprintf("%.2f", ($forehead * (empty($goods_attr_info['price']) ? $goods->price : $goods_attr_info['price'])));
//                }
                if ($integral->more == '1') {
                    $resIntegral['forehead_integral'] = (int)($store->integral * $goods_item->price);
//                    $resIntegral['forehead'] = sprintf("%.2f", ($store->integral * $goodsPrice));
                    if ($goods_item->price > ($forehead * $goods_item->num)) {
                        $resIntegral['forehead_integral'] = (int)($forehead * $goods_item->num * $store->integral);
                    }
                } else {
                    $goodsPrice = (empty($goods_attr_info['price']) ? $goods->price : $goods_attr_info['price']);
                    $resIntegral['forehead_integral'] = (int)($store->integral * $goodsPrice);
                    if ($goodsPrice > $forehead) {
                        $resIntegral['forehead_integral'] = (int)($forehead * $store->integral);
                    }
                }
            }
            $resIntegral['forehead'] = sprintf("%.2f", ($resIntegral['forehead_integral'] / $store->integral));
        }



        $seckill_date= $seckill_data['seckill_data'];


        $goods_card_list = Goods::getGoodsCard($goods->id);
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'total_price' => intval($total_price),
                'goods_info' => $goods_info,
                'list' => [
                    $goods_item
                ],
                'address' => $address,
                'express_price' => $express_price,
                'integral' => $resIntegral,
                'goods_card_list' => $goods_card_list,
                'advance_coupon' => intval($advance_coupon),
                'advance_integral_buy' => intval($advance_integral_buy),
                'yukuan_integral_buy' => intval($yukuan_integral_buy),
                'yukuan_coupon' => intval($yukuan_coupon),
                'advance' => $goods_item->advance,
                'seckill_data' =>array(
                    'num'=>$num,
                    'end_date_bookmall'=>$seckill_date->end_date_bookmall,
                    'start_date_bookmall'=>$seckill_date->start_date_bookmall,
                ),
                'charge_integral_buy'=>$charge_integral_buy,
                'chargeprice'=>$this->getChargecontance($num, $goods, $temp_price['total_price'],$seckill_data['seckill_coupon']),
            ],
        ];
    }


    public function getCharge($num, $goods)
    {
        $charge = 0;
        if ($num <= $goods->chargeNum && $num > 0) {
            $charge = $goods->charge;  //1张
        } elseif ($num <= $goods->chargeNum1 && $num > $goods->chargeNum) {
            $charge = $goods->charge1; //1-6
        } elseif ($num <= $goods->chargeNum2 && $num > $goods->chargeNum1) {
            $charge = $goods->charge2;//7-18
        } elseif ($num <= $goods->chargeNum3 && $num > $goods->chargeNum2) {
            $charge = $goods->charge3; //18以上
        } else {
            $charge = $goods->charge5;  //1张
        }
        return $charge;
    }

    public function getChargecontance($num, $goods,$integral=0,$seckill_coupon=0)
    {

        $chargeprice[]=array(
            'num'=>'0～'.$goods->chargeNum, 'integral'=> intval($integral * (1 - $goods->advance / 100)*(1-$goods->charge/100)),'charge'=>$goods->charge,
        );
        $chargeprice[]=array(
            'num'=>$goods->chargeNum.'~'.$goods->chargeNum1, 'integral'=> intval($integral * (1 - $goods->advance / 100)*(1-$goods->charge1/100)),'charge'=>$goods->charge1,
        );
        $chargeprice[]=array(
            'num'=>$goods->chargeNum1.'~'.$goods->chargeNum2, 'integral'=> intval($integral * (1 - $goods->advance / 100)*(1-$goods->charge2/100)),'charge'=>$goods->charge2,
        );
        $chargeprice[]=array(
            'num'=>$goods->chargeNum2.'~'.$goods->chargeNum3,'integral'=> intval($integral * (1 - $goods->advance / 100)*(1-$goods->charge3/100)),'charge'=>$goods->charge3,
        );
        $chargeprice[]=array(
            'num'=>'超过'.$goods->chargeNum3, 'integral'=> intval($integral * (1 - $goods->advance / 100)*(1-$goods->charge5/100)),'charge'=>$goods->charge5,
        );
        $charge = 0;
        if ($num <= $goods->chargeNum && $num >= 0) {
            $charge = $goods->charge;  //1张
            $chargeprice[0]['select']=true;
        } elseif ($num <= $goods->chargeNum1 && $num > $goods->chargeNum) {
            $chargeprice[1]['select']=true;
            $charge = $goods->charge1; //1-6
        } elseif ($num <= $goods->chargeNum2 && $num > $goods->chargeNum1) {
            $chargeprice[2]['select']=true;
            $charge = $goods->charge2;//7-18
        } elseif ($num <= $goods->chargeNum3 && $num > $goods->chargeNum2) {
            $chargeprice[3]['select']=true;
            $charge = $goods->charge3; //18以上
        } else {
            $chargeprice[4]['select']=true;
            $charge = $goods->charge5;  //1张
        }

        return $chargeprice;
    }

    private function getShopList()
    {
        $list = Shop::find()->select(['address', 'mobile', 'id', 'name', 'longitude', 'latitude'])
            ->where(['store_id' => $this->store_id, 'is_delete' => 0])->asArray()->all();
        $distance = array();
        foreach ($list as $index => $item) {
            $list[$index]['distance'] = -1;
            if ($item['longitude'] && $this->longitude) {
                $from = [$this->longitude, $this->latitude];
                $to = [$item['longitude'], $item['latitude']];
                $list[$index]['distance'] = $this->get_distance($from, $to, false, 2);
            }
            $distance[] = $list[$index]['distance'];
        }
        array_multisort($distance, SORT_ASC, $list);
        $min = min(count($list), 30);
        $list_arr = array();
        foreach ($list as $index => $item) {
            if ($index <= $min) {
                $list[$index]['distance'] = $this->distance($item['distance']);
                array_push($list_arr, $list[$index]);
            }
        }
        return $list;
    }

    /**
     * @param Goods $goods
     * @param array $attr_id_list eg.[12,34,22]
     * @return array ['attr_list'=>[],'seckill_price'=>'秒杀价格','seckill_num'=>'秒杀数量','sell_num'=>'已秒杀商品数量']
     */
    public function getSeckillData($goods, $attr_id_list = [])
    {
        $seckill_goods = SeckillGoods::findOne([
            'goods_id' => $goods->id,
            'is_delete' => 0,
            'open_date' => date('Y-m-d'),
            'start_time' => intval(date('H')),
        ]);
        if (!$seckill_goods)
            return null;
        $attr_data = json_decode($seckill_goods->attr, true);
        sort($attr_id_list);
        $seckill_data = null;
        foreach ($attr_data as $i => $attr_data_item) {
            $_tmp_attr_id_list = [];
            foreach ($attr_data_item['attr_list'] as $item) {
                $_tmp_attr_id_list[] = $item['attr_id'];
            }
            sort($_tmp_attr_id_list);
            if ($attr_id_list == $_tmp_attr_id_list) {
                $seckill_data = $attr_data_item;
                break;
            }
        }
        $seckill_data['seckill_data']=$seckill_goods;
        return $seckill_data;
    }

    /**
     * 获取商品秒杀价格，若库存不足则使用商品原价，若有部分库存，则部分数量使用秒杀价，部分使用商品原价，商品库存不足返回false
     * @param array $seckill_data ['attr_list'=>[],'seckill_price'=>'秒杀价格','seckill_num'=>'秒杀数量','sell_num'=>'已秒杀商品数量']
     * @param Goods $goods
     * @param array $attr_id_list eg.[12,34,22]
     * @param integer $buy_num 购买数量
     *
     * @return false|float
     */
    private function getSeckillPrice($seckill_data, $goods, $attr_id_list, $buy_num)
    {
        $attr_data = json_decode($goods->attr, true);
        sort($attr_id_list);
        $goost_attr_data = null;
        foreach ($attr_data as $i => $attr_data_item) {
            $_tmp_attr_id_list = [];
            foreach ($attr_data_item['attr_list'] as $item) {
                $_tmp_attr_id_list[] = intval($item['attr_id']);
            }
            sort($_tmp_attr_id_list);
            if ($attr_id_list == $_tmp_attr_id_list) {
                $goost_attr_data = $attr_data_item;
                break;
            }
        }
        $goods_price = $goost_attr_data['price'];
        if (!$goods_price)
            $goods_price = $goods->price;

        $seckill_price = min($seckill_data['seckill_price'], $goods_price);

        if ($buy_num > $goost_attr_data['num'])//商品库存不足
        {
//            return [
//                'code' => 1,
//                'msg' => '商品库存不足',
//            ];
            \Yii::warning([
                'res' => '库存不足',
                'm_data' => $seckill_data,
                'g_data' => $goost_attr_data,
                'attr_id_list' => $attr_id_list,
            ]);
            return false;
        }

        if ($buy_num <= ($seckill_data['seckill_num'] - $seckill_data['sell_num'])) {
            \Yii::warning([
                'res' => '库存充足',
                'price' => $buy_num * $seckill_price,
                'm_data' => $seckill_data,
            ]);
//            return $buy_num * $seckill_price;
            return [
                'seckill_price_num' => $buy_num,
                'original_price_num' => 0,
                'total_price' => $buy_num * $seckill_price
            ];
        }
        $seckill_num = ($seckill_data['seckill_num'] - $seckill_data['sell_num']);
        $original_num = $buy_num - $seckill_num;
        return false;
//        return [
//            'code' => 1,
//            'msg' => '库存部分不足',
//        ];
        \Yii::warning([
            'res' => '部分充足',
            'price' => $seckill_num * $seckill_price + $original_num * $goods_price,
            'm_data' => $seckill_data,
        ]);
        return $seckill_num * $seckill_price + $original_num * $goods_price;
    }

    private static function distance($distance)
    {
        if ($distance == -1) {
            return -1;
        }
        if ($distance > 1000) {
            $distance = round($distance / 1000, 2) . 'km';
        } else {
            $distance .= 'm';
        }
        return $distance;
    }

    /**
     * 根据起点坐标和终点坐标测距离
     * @param  [array]   $from  [起点坐标(经纬度),例如:array(118.012951,36.810024)]
     * @param  [array]   $to    [终点坐标(经纬度)]
     * @param  [bool]    $km        是否以公里为单位 false:米 true:公里(千米)
     * @param  [int]     $decimal   精度 保留小数位数
     * @return [string]  距离数值
     */
    function get_distance($from, $to, $km = true, $decimal = 2)
    {
        sort($from);
        sort($to);
        $EARTH_RADIUS = 6370.996; // 地球半径系数

        $distance = $EARTH_RADIUS * 2 * asin(sqrt(pow(sin(($from[0] * pi() / 180 - $to[0] * pi() / 180) / 2), 2) + cos($from[0] * pi() / 180) * cos($to[0] * pi() / 180) * pow(sin(($from[1] * pi() / 180 - $to[1] * pi() / 180) / 2), 2))) * 1000;

        if ($km) {
            $distance = $distance / 1000;
        }

        return round($distance, $decimal);
    }

    /**
     * 检查订单中是否有秒杀商品并且限购
     * @return null||array null表示无限购
     */
    public function checkBuyMax($list)
    {
        $goods_list = [];
        foreach ($list as $item) {
            if (empty($goods_list[$item->goods_id])) {
                $goods_list[$item->goods_id] = [
                    'goods_name' => $item->goods_name,
                    'num' => $item->num,
                ];
            } else {
                $goods_list[$item->goods_id]['num'] += intval($item->num);
            }
        }

        foreach ($goods_list as $goods_id => $item) {
            $seckill_goods = SeckillGoods::find()->where([
                'AND',
                [
                    'goods_id' => $goods_id,
                    'is_delete' => 0,
                    'open_date' => date('Y-m-d'),
                    'start_time' => intval(date('H')),
                ],
                ['!=', 'buy_max', 0],
                ['<', 'buy_max', $item['num']],
            ])->one();
            if ($seckill_goods) {
                return [
                    'code' => 1,
                    'msg' => "购买数量超过限制！ 商品“" . $item['goods_name'] . '”最多允许购买' . $seckill_goods->buy_max . '件，请返回重新下单',
                ];
            }
        }
        return null;
    }

}