<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/7/17
 * Time: 11:48
 */

namespace app\modules\api\models;


use app\models\Address;
use app\models\Attr;
use app\models\AttrGroup;
use app\models\Cart;
use app\models\Coupon;
use app\models\Goods;
use app\models\Level;
use app\models\SeckillGoods;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderForm;
use app\models\PostageRules;
use app\models\PrinterSetting;
use app\models\Store;
use app\models\User;
use app\models\UserCoupon;
use app\modules\api\controllers\OrderController;
use app\extensions\PinterOrder;
use yii\helpers\VarDumper;

class OrderSubmitForm extends Model
{
    public $store_id;
    public $user_id;
    public $version;

    public $address_id;
    public $cart_id_list;
    public $goods_info;

    public $user_coupon_id;

    public $content;
    public $offline;
    public $address_name;
    public $address_mobile;
    public $shop_id;

    public $use_integral;

    public $form;//自定义表单信息

    public function rules()
    {
        return [
            [['cart_id_list', 'goods_info', 'content', 'address_name', 'address_mobile'], 'string'],
            [['address_id',], 'required', 'on' => "EXPRESS"],
            [['address_name', 'address_mobile'], 'required', 'on' => "OFFLINE"],
            [['user_coupon_id', 'offline', 'shop_id', 'use_integral'], 'integer'],
            [['offline'], 'default', 'value' => 0],
            [['form'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'address_id' => '收货地址',
            'address_name' => '收货人',
            'address_mobile' => '联系电话'
        ];
    }

    public function save()
    {
        if (!$this->validate())
            return $this->getModelError();
        $t = \Yii::$app->db->beginTransaction();
        $express_price = 0;
        $form = json_decode($this->form, true);
        $form_list = $form['list'];
        if ($form['is_form'] == 1) {
            foreach ($form_list as $index => $value) {
                if ($value['required'] == 1) {
                    if (in_array($value['type'], ['radio', 'checkbox'])) {
                        $is_true = false;
                        foreach ($value['default_list'] as $k => $v) {
                            if ($v['is_selected'] == 1) {
                                $is_true = true;
                            }
                        }
                        if (!$is_true) {
                            return [
                                'code' => 1,
                                'msg' => '请填写' . $form['name'] . '，加“*”为必填项',
                                'name' => $value['name']
                            ];
                        }
                    } else {
                        if (!$value['default'] && $value['default'] != 0) {
                            return [
                                'code' => 1,
                                'msg' => '请填写' . $form['name'] . '，加“*”为必填项',
                                'name' => $value['name']
                            ];
                        }
                    }
                }
                if (in_array($value['type'], ['radio', 'checkbox'])) {
                    $d = [];
                    foreach ($value['default_list'] as $k => $v) {
                        if ($v['is_selected'] == 1) {
                            $d[] = $v['name'];
                        }
                    }
                    $form_list[$index]['default'] = implode(',', $d);
                }
            }
        }
        if ($this->offline == 0) {
            $address = Address::findOne([
                'id' => $this->address_id,
                'store_id' => $this->store_id,
                'user_id' => $this->user_id,
            ]);
            if (!$address) {
                return [
                    'code' => 1,
                    'msg' => '收货地址不存在',
                ];
            }

//            $express_price = PostageRules::getExpressPrice($this->store_id, $address->province_id);
        } else {
            if (!preg_match_all("/1\d{10}/", $this->address_mobile, $arr)) {
                return [
                    'code' => 1,
                    'msg' => '手机号格式错误'
                ];
            }
        }
        $goods_list = [];
        $total_price = 0;

        $resIntegral = [
            'forehead' => 0,
            'forehead_integral' => 0,
        ];
        $goodsIds = [];
        $store = Store::findOne($this->store_id);
        if ($this->cart_id_list) {
            $data = $this->getGoodsListByCartIdList($this->cart_id_list);
            $goods_list = empty($data['list']) ? [] : $data['list'];
            $total_price = empty($data['total_price']) ? 0 : $data['total_price'];
            $goodsList = [];
            foreach ($goods_list as $k => $val) {
                $goods = Goods::findOne([
                    'id' => $val->goods_id,
                    'is_delete' => 0,
                    'store_id' => $this->store_id,
                    'status' => 1,
                ]);
                $new_goods = [
                    'goods_id' => $goods->id,
                    'goods_name' => $goods->name,
                    'freight' => $goods->freight,
                    'weight' => $goods->weight,
                    'num' => $val->num,
                    'full_cut' => $goods->full_cut,
                    'price' => $val->price,
                ];
                $goodsList[] = $new_goods;

                // 积分
                $integral = json_decode($goods->integral);
                if ($integral) {
                    // 获取商品规格信息
                    $attr_id_list = [];
                    foreach ($val->attr_list as $item) {
                        array_push($attr_id_list, $item['attr_id']);
                    }
                    $goods_attr_info = $goods->getAttrInfo($attr_id_list);

                    $give = $integral->give;
                    if (strpos($give, '%') !== false) {
                        // 百分比
                        $give = trim($give, '%');
                        $goods_list[$k]->give = (int)($val->price * ($give / 100));
                    } else {
                        // 固定积分
                        $goods_list[$k]->give = (int)($give * $val->num);
                    }
                    if ($this->use_integral == '1') {
                        $forehead = (int)$integral->forehead;
                        if (strpos($forehead, '%') !== false) {
                            $forehead = trim($forehead, '%');
                            if ($forehead >= 100) {
                                $forehead = 100;
                            }
                            if ($integral->more == '1') {
                                $resIntegral['forehead_integral'] += (int)(($forehead / 100) * $val->price * $store->integral);
                            } elseif ($integral->more != '1' && !in_array($goods->id, $goodsIds)) {
                                $resIntegral['forehead_integral'] += (int)(($forehead / 100) * (empty($goods_attr_info['price']) ? $goods->price : $goods_attr_info['price']) * $store->integral);
                            }
//                        } else {
//                            if ($integral->more == '1') {
//                                $resIntegral['forehead'] += sprintf("%.2f", ($forehead * $val->num));
//                            } elseif ($integral->more != '1' && !in_array($goods->id, $goodsIds)) {
//                                $resIntegral['forehead'] += sprintf("%.2f", ($forehead * 1));
//                            }
//                        }
//                        // 记录下 商品id
//                        $goodsIds[] = $goods->id;
//                        $resIntegral['forehead_integral'] = $resIntegral['forehead'] * $store->integral;
                        } else {
                            if ($integral->more == '1') {
                                //                    $resIntegral['forehead'] = sprintf("%.2f", ($store->integral * $goodsPrice));
                                if ($val->price > ($forehead * $val->num)) {
                                    $resIntegral['forehead_integral'] += (int)(($forehead * $val->num) * $store->integral);
                                } else {
                                    $resIntegral['forehead_integral'] += (int)($store->integral * $val->price);
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
//                    }
                }
            }
//            var_dump($goodsList);die();
            if ($this->offline == 0) {
                $resGoodsList = (new Goods)->cutFull($goodsList);
                $express_price = PostageRules::getExpressPriceMore($this->store_id, $address->province_id, $resGoodsList);
            }

        } elseif ($this->goods_info) {
            $data = $this->getGoodsListByGoodsInfo($this->goods_info);
            $goods_list = empty($data['list']) ? [] : $data['list'];
            $total_price = empty($data['total_price']) ? 0 : $data['total_price'];
            $resIntegral = [
                'forehead' => 0,
                'forehead_integral' => 0,
            ];
            foreach ($goods_list as $k => $val) {
                $goods = Goods::findOne([
                    'id' => $val->goods_id,
                    'is_delete' => 0,
                    'store_id' => $this->store_id,
                    'status' => 1,
                ]);

                // 积分
                $integral = json_decode($goods->integral);
                if ($integral) {
                    // 获取商品规格信息
                    $attr_id_list = [];
                    foreach ($val->attr_list as $item) {
                        array_push($attr_id_list, $item['attr_id']);
                    }
                    $goods_attr_info = $goods->getAttrInfo($attr_id_list);

                    $give = $integral->give;
                    if (strpos($give, '%') !== false) {
                        // 百分比
                        $give = trim($give, '%');
                        $goods_list[$k]->give = (int)($val->price * ($give / 100));
                    } else {
                        // 固定积分
                        $goods_list[$k]->give = (int)($give * $val->num);
                    }
                    if ($this->use_integral == '1') {
                        $forehead = (int)$integral->forehead;
                        if (strpos($forehead, '%') !== false) {
                            $forehead = trim($forehead, '%');
                            if ($forehead >= 100) {
                                $forehead = 100;
                            }
                            if ($integral->more == '1') {
                                $resIntegral['forehead_integral'] = (int)(($forehead / 100) * $val->price * $store->integral);
                            } else {
                                $resIntegral['forehead_integral'] = (int)(($forehead / 100) * (empty($goods_attr_info['price']) ? $goods->price : $goods_attr_info['price']) * $store->integral);
                            }
                        } else {
//                            if ($integral->more == '1') {
//                                $resIntegral['forehead'] = sprintf("%.2f", ($forehead * $val->price));
//                            } else {
//                                $resIntegral['forehead'] = sprintf("%.2f", ($forehead * (empty($goods_attr_info['price']) ? $goods->price : $goods_attr_info['price'])));
//                            }
                            if ($integral->more == '1') {
                                $resIntegral['forehead_integral'] = (int)($store->integral * $val->price);
//                    $resIntegral['forehead'] = sprintf("%.2f", ($store->integral * $goodsPrice));
                                if ($val->price > ($forehead * $val->num)) {
                                    $resIntegral['forehead_integral'] = (int)(($forehead * $val->num) * $store->integral);
                                }
                            } else {
                                $goodsPrice = (empty($goods_attr_info['price']) ? $goods->price : $goods_attr_info['price']);
                                $resIntegral['forehead_integral'] = (int)($store->integral * $goodsPrice);
                                if ($goodsPrice > $forehead) {
                                    $resIntegral['forehead_integral'] = (int)($store->integral * $forehead);
                                }
                            }
                        }
//                        $resIntegral['forehead_integral'] = $resIntegral['forehead'] * $store->integral;
                        $resIntegral['forehead'] = sprintf("%.2f", ($resIntegral['forehead_integral'] / $store->integral));
//                        $resIntegral['forehead_integral'] = ceil($resIntegral['forehead']）
                    }
                }
                if ($this->offline == 0) {
                    if ($goods['full_cut']) {
                        $full_cut = json_decode($goods['full_cut'], true);
                    } else {
                        $full_cut = json_decode([
                            'pieces' => 0,
                            'forehead' => 0,
                        ], true);
                    }

                    if ((empty($full_cut['pieces']) || $val->num < ($full_cut['pieces'] ?: 0)) && (empty($full_cut['forehead']) || $val->price < ($full_cut['forehead'] ?: 0))) {
                        $express_price = PostageRules::getExpressPrice($this->store_id, $address->province_id, $goods, $val->num);
                    }
//                    $express_price = PostageRules::getExpressPrice($this->store_id, $address->province_id, $goods,$val->num);
                }
            }

        }
        if (empty($goods_list)) {
            return [
                'code' => 1,
                'msg' => '订单提交失败，所选商品库存不足或已下架',
            ];
        }


        $total_price_1 = $total_price + $express_price;
        $level = Level::find()->where(['store_id' => $this->store_id, 'level' => \Yii::$app->user->identity->level])->asArray()->one();
        if ($level) {
            $discount = $level['discount'];
        } else {
            $discount = 10;
        }

        // 获取用户当前积分
        $user = User::findOne(['id' => $this->user_id, 'type' => 1, 'is_delete' => 0]);
        if ($user->integral < $resIntegral['forehead_integral']) {
            $resIntegral['forehead_integral'] = $user->integral;
            $resIntegral['forehead'] = sprintf("%.2f", $user->integral / $store->integral);
        }

        // 减去 积分折扣金额
        $total_price_3 = $total_price - $resIntegral['forehead'];

        $total_price_2 = max(0.01, round($total_price_3 * $discount / 10, 2)) + $express_price;

//        // 减去 积分折扣金额
//        $total_price_3 = $total_price_2 - $resIntegral['forehead'];

        $order = new Order();
        $order->store_id = $this->store_id;
        $order->user_id = $this->user_id;
        $order->order_no = $this->getOrderNo();
        $order->total_price = $total_price_1;
        $order->pay_price = $total_price_2 < 0.01 ? 0.01 : $total_price_2;
        $order->express_price = $express_price;
        $order->discount = $discount;
        $order->addtime = time();
        if ($this->offline == 0) {
            $order->address = $address->province . $address->city . $address->district . $address->detail;
            $order->mobile = $address->mobile;
            $order->name = $address->name;
            $order->address_data = json_encode([
                'province' => $address->province,
                'city' => $address->city,
                'district' => $address->district,
                'detail' => $address->detail,
            ], JSON_UNESCAPED_UNICODE);
        } else {
            $order->name = $this->address_name;
            $order->mobile = $this->address_mobile;
            $order->shop_id = $this->shop_id;
        }
        $order->first_price = 0;
        $order->second_price = 0;
        $order->third_price = 0;
        $order->content = $this->content;
        $order->is_offline = $this->offline;
        $order->integral = json_encode($resIntegral, JSON_UNESCAPED_UNICODE);
        $order->version = $this->version;
//        if($this->offline == 1){
//            $qrcode_form = new QrcodeForm();
//            $qrcode_form->order_no = $order->order_no;
//            $res = $qrcode_form->getQrcode();
//            $order->offline_qrcode = $res['data']['url'];
//        }

        if ($this->user_coupon_id) {
            $coupon = Coupon::find()->alias('c')
                ->leftJoin(['uc' => UserCoupon::tableName()], 'uc.coupon_id=c.id')
                ->where([
                    'uc.id' => $this->user_coupon_id,
                    'uc.is_delete' => 0,
                    'uc.is_use' => 0,
                    'uc.is_expire' => 0,
                    'uc.user_id' => $this->user_id
                ])
                ->select('c.*')->one();
//            $goods_total_pay_price = $order->pay_price - $order->express_price;//原本需支付的商品总价
            $goods_total_pay_price = $order->total_price - $order->express_price;//原本需支付的商品总价
            if ($coupon && $goods_total_pay_price >= $coupon->min_price) {
                $goods_price = ($goods_total_pay_price - $coupon->sub_price - $resIntegral['forehead']) * $discount / 10;
                $order->pay_price = max(0.01, round($goods_price, 2)) + $order->express_price;
                $order->coupon_sub_price = $goods_total_pay_price - max(0.01, $goods_total_pay_price - $coupon->sub_price);
                UserCoupon::updateAll(['is_use' => 1], ['id' => $this->user_coupon_id]);
                $order->user_coupon_id = $this->user_coupon_id;
            }
        }
        if ($order->save()) {

            foreach ($form_list as $index => $value) {
                $order_form = new OrderForm();
                $order_form->store_id = $this->store_id;
                $order_form->order_id = $order->id;
                $order_form->key = $value['name'];
                $order_form->value = $value['default'];
                $order_form->is_delete = 0;
                $order_form->save();
            }

            // 减去当前用户账户积分
            if ($resIntegral['forehead_integral'] > 0) {
                $user->integral -= $resIntegral['forehead_integral'];
                $user->save();
            }
            $goods_total_pay_price = $order->pay_price - $order->express_price;
            $goods_total_price = 0.00;
            foreach ($goods_list as $goods) {
                $goods_total_price += $goods->price;
            }
            foreach ($goods_list as $goods) {
                $order_detail = new OrderDetail();
                $order_detail->order_id = $order->id;
                $order_detail->goods_id = $goods->goods_id;
                $order_detail->num = $goods->num;
                $order_detail->total_price = doubleval(sprintf('%.2f', $goods_total_pay_price * $goods->price / $goods_total_price));
                $order_detail->addtime = time();
                $order_detail->is_delete = 0;
                $order_detail->attr = json_encode($goods->attr_list, JSON_UNESCAPED_UNICODE);
                $order_detail->pic = $goods->goods_pic;
                $order_detail->integral = $goods->give;

                $attr_id_list = [];
                foreach ($goods->attr_list as $item) {
                    array_push($attr_id_list, $item['attr_id']);
                }
                $_goods = Goods::findOne($goods->goods_id);
                if (!$_goods->numSub($attr_id_list, $order_detail->num)) {
                    $t->rollBack();
                    return [
                        'code' => 1,
                        'msg' => '订单提交失败，商品“' . $_goods->name . '”库存不足',
                        'attr_id_list' => $attr_id_list,
                        'attr_list' => $goods->attr_list,
                    ];
                }

                if (!$order_detail->save()) {
                    $t->rollBack();
                    return [
                        'code' => 1,
                        'msg' => '订单提交失败，请稍后再重试',
                    ];
                }
            }

            // 打印设置
//            $printer_order = new PinterOrder($this->store_id, $order->id);
//            $res = $printer_order->print_order();

            $t->commit();
            return [
                'code' => 0,
                'msg' => '订单提交成功',
                'data' => (object)[
                    'order_id' => $order->id,
                ],
            ];
        } else {
            $t->rollBack();
            return $this->getModelError($order);
        }
    }


    /**
     * 获取购物车商品列表及总价
     * @param string $cart_id_list
     * eg.[1,2,3]
     * @return array 'list'=>商品列表,'total_price'=>总价
     */
    private function getGoodsListByCartIdList($cart_id_list)
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
                ->select('a.id attr_id,ag.attr_group_name,a.attr_name')
                ->leftJoin(['ag' => AttrGroup::tableName()], 'a.attr_group_id=ag.id')
                ->where(['a.id' => json_decode($item->attr, true)])
                ->asArray()->all();
            $goods_attr_info = $goods->getAttrInfo(json_decode($item->attr, true));
            $attr_num = intval(empty($goods_attr_info['num']) ? 0 : $goods_attr_info['num']);
            if ($attr_num < $item->num)
                continue;
            $goods_pic = isset($goods_attr_info['pic']) ? $goods_attr_info['pic'] ?: $goods->getGoodsPic(0)->pic_url : $goods->getGoodsPic(0)->pic_url;
            $new_item = (object)[
                'cart_id' => $item->id,
                'goods_id' => $goods->id,
                'goods_name' => $goods->name,
                'goods_pic' => $goods_pic,
//                'goods_pic' => $goods->getGoodsPic(0)->pic_url,
                'num' => $item->num,
                'price' => doubleval(empty($goods_attr_info['price']) ? $goods->price : $goods_attr_info['price']) * $item->num,
                'attr_list' => $attr_list,
                'max_num' => $attr_num,
                'give' => 0,
            ];

            //秒杀价计算
            $seckill_data = $this->getSeckillData($goods, json_decode($item->attr, true));
            if ($seckill_data) {
                $res = $this->getSeckillPrice($seckill_data, $goods, json_decode($item->attr, true), $item->num);
                if ($res !== false) {
                    $new_item->price = $res['total_price'];
                    $this->setSeckillSellNum($seckill_data['id'], json_decode($item->attr, true), $res['seckill_price_num']);
                }
            }


            $total_price += $new_item->price;
            $new_cart_id_list[] = $item->id;
            $list[] = $new_item;
            $item->is_delete = 1;
            $item->save();
        }
        return [
            'total_price' => $total_price,
            'cart_id_list' => $new_cart_id_list,
            'list' => $list,
        ];
    }


    /**
     * @param string $goods_info
     * eg.{"goods_id":"22","attr":[{"attr_group_id":1,"attr_group_name":"颜色","attr_id":3,"attr_name":"橙色"},{"attr_group_id":2,"attr_group_name":"尺码","attr_id":2,"attr_name":"M"}],"num":1}
     */
    private function getGoodsListByGoodsInfo($goods_info)
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
                'total_price' => 0,
                'list' => [],
            ];
        }
        $attr_id_list = [];
        foreach ($goods_info->attr as $item) {
            array_push($attr_id_list, $item->attr_id);
        }
        $total_price = 0;
        $goods_attr_info = $goods->getAttrInfo($attr_id_list);

        $attr_list = Attr::find()->alias('a')
            ->select('a.id attr_id,ag.attr_group_name,a.attr_name')
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
            'price' => doubleval(empty($goods_attr_info['price']) ? $goods->price : $goods_attr_info['price']) * $goods_info->num,
            'attr_list' => $attr_list,
            'give' => 0,
        ];

        //秒杀价计算
        $seckill_data = $this->getSeckillData($goods, $attr_id_list);
        if ($seckill_data) {
            $res = $this->getSeckillPrice($seckill_data, $goods, $attr_id_list, $goods_info->num);
            if ($res !== false) {
                $goods_item->price = $res['total_price'];
                $this->setSeckillSellNum($seckill_data['id'], $attr_id_list, $res['seckill_price_num']);
            }
        }

        $total_price += $goods_item->price;
        return [
            'total_price' => $total_price,
            'list' => [$goods_item],
        ];
    }

    public function getOrderNo()
    {
        $store_id = empty($this->store_id) ? 0 : $this->store_id;
        $order_no = null;
        while (true) {
            $order_no = date('YmdHis') . rand(100000, 999999);
            $exist_order_no = Order::find()->where(['order_no' => $order_no])->exists();
            if (!$exist_order_no)
                break;
        }
        return $order_no;
    }


    /**
     * @param Goods $goods
     * @param array $attr_id_list eg.[12,34,22]
     * @return array ['id'=>'seckill_goods id','attr_list'=>[],'seckill_price'=>'秒杀价格','seckill_num'=>'秒杀数量','sell_num'=>'已秒杀商品数量']
     */
    private function getSeckillData($goods, $attr_id_list = [])
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
        $seckill_data['id'] = $seckill_goods->id;
        return $seckill_data;
    }

    /**
     * 获取商品秒杀价格，若库存不足则使用商品原价，若有部分库存，则部分数量使用秒杀价，部分使用商品原价，商品库存不足返回false
     * @param array $seckill_data ['attr_list'=>[],'seckill_price'=>'秒杀价格','seckill_num'=>'秒杀数量','sell_num'=>'已秒杀商品数量']
     * @param Goods $goods
     * @param array $attr_id_list eg.[12,34,22]
     * @param integer $buy_num 购买数量
     *
     * @return false|array
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
            \Yii::warning([
                'res' => '库存不足',
                'm_data' => $seckill_data,
                'g_data' => $goost_attr_data,
                '$attr_id_list' => $attr_id_list,
            ]);
            return false;
        }

        if ($buy_num <= ($seckill_data['seckill_num'] - $seckill_data['sell_num'])) {
            \Yii::warning([
                'res' => '库存充足',
                'price' => $buy_num * $seckill_price,
                'm_data' => $seckill_data,
            ]);
            return [
                'seckill_price_num' => $buy_num,
                'original_price_num' => 0,
                'total_price' => $buy_num * $seckill_price
            ];
        }

        $seckill_num = ($seckill_data['seckill_num'] - $seckill_data['sell_num']);
        $original_num = $buy_num - $seckill_num;

        \Yii::warning([
            'res' => '部分充足',
            'price' => $seckill_num * $seckill_price + $original_num * $goods_price,
            'm_data' => $seckill_data,
        ]);

        return [
            'seckill_price_num' => $seckill_num,
            'original_price_num' => $original_num,
            'total_price' => $seckill_num * $seckill_price + $original_num * $goods_price,
        ];
    }

    private function setSeckillSellNum($seckill_goods_id, $attr_id_list, $num)
    {
        $seckill_goods = SeckillGoods::findOne($seckill_goods_id);
        if (!$seckill_goods)
            return false;
        sort($attr_id_list);
        $attr_data = json_decode($seckill_goods->attr, true);
        foreach ($attr_data as $i => $attr_row) {
            $_tmp_attr_id_list = [];
            foreach ($attr_row['attr_list'] as $attr) {
                $_tmp_attr_id_list[] = intval($attr['attr_id']);
            }
            sort($_tmp_attr_id_list);
            if ($_tmp_attr_id_list == $attr_id_list) {
                $attr_data[$i]['sell_num'] = intval($attr_data[$i]['sell_num']) + intval($num);
                break;
            }
        }
        $seckill_goods->attr = json_encode($attr_data, JSON_UNESCAPED_UNICODE);
        $res = $seckill_goods->save();
        return $res;
    }

}