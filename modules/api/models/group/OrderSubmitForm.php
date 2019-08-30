<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/7/17
 * Time: 11:48
 */

namespace app\modules\api\models\group;


use app\extensions\PinterOrder;
use app\extensions\printerPtOrder;
use app\models\Address;
use app\models\Attr;
use app\models\AttrGroup;
use app\models\Goods;
use app\models\Order;
use app\models\PostageRules;
use app\models\PrinterSetting;
use app\models\PtGoods;
use app\models\PtOrder;
use app\models\PtOrderDetail;
use app\models\Store;
use app\models\User;
use app\models\UserCoupon;
use app\modules\api\models\Model;

class OrderSubmitForm extends Model
{
    public $store_id;
    public $user_id;

    public $address_id;
    public $goods_info;

    public $shop_id;

    public $use_integral;

    public $type;
    public $parent_id;

    public $content;
    public $offline;
    public $address_name;
    public $address_mobile;

    public function rules()
    {
        return [
            [['goods_info', 'content', 'address_name', 'address_mobile'], 'string'],
            [['type',], 'required'],
            [['shop_id', 'use_integral'], 'integer'],
            [['parent_id'], 'default', 'value' => 0],
            [['address_id',], 'required', 'on' => "EXPRESS"],
            [['address_name', 'address_mobile'], 'required', 'on' => "OFFLINE"],
            [['offline'], 'default', 'value' => 1],
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

        if ($this->offline == 1) {
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


//        $address = Address::findOne([
//            'id' => $this->address_id,
//            'store_id' => $this->store_id,
//            'user_id' => $this->user_id,
//        ]);
//        if (!$address) {
//            return [
//                'code' => 1,
//                'msg' => '收货地址不存在',
//            ];
//        }
//        $parentOrder = PtOrder::findOne([
//            'id' => $this->parent_id,
//            'is_delete' => 0,
//            'store_id' => $this->store_id,
//            'status' => 1,
//        ]);
        $parentOrder = null;
        if ($this->type == 'GROUP_BUY_C') {
            $parentOrder = PtOrder::findOne([
                'id' => $this->parent_id,
                'is_delete' => 0,
                'store_id' => $this->store_id,
                'status' => 2,
            ]);
            if (!$parentOrder) {
                return [
                    'code' => 1,
                    'msg' => '您参与的团不存在，或已取消',
                ];
            }
            if ($parentOrder->getSurplusGruop() <= 0) {
                return [
                    'code' => 1,
                    'msg' => '您参与的团已满',
                ];
            }

            $isInGroup = PtOrder::find()
                ->andWhere(['or', ['id' => $this->parent_id], ['parent_id' => $this->parent_id]])
                ->andWhere(['is_delete' => 0, 'is_pay' => 1, 'is_group' => 1])
                ->andWhere(['user_id' => $this->user_id])
                ->one();

            if ($isInGroup) {
                return [
                    'code' => 1,
                    'msg' => '您不能重复参团',
                ];
            }
        }
        $goods_list = [];
        $total_price = 0;
        $colonel = 0;
        $data = $this->getGoodsListByGoodsInfo($this->goods_info);
        if (isset($data['code'])) {
            return $data;
        }
        $goods_list = empty($data['list']) ? [] : $data['list'];
        $total_price = empty($data['total_price']) ? 0 : $data['total_price'];
        foreach ($goods_list as $k => $val) {
            $goods = PtGoods::findOne([
                'id' => $val->goods_id,
                'is_delete' => 0,
                'store_id' => $this->store_id,
                'status' => 1,
            ]);
            $colonel = $goods->colonel;
            if ($this->offline == 1) {
                $express_price = PostageRules::getExpressPrice($this->store_id, $address->province_id, $goods, $val->num);
            }
        }
        if (empty($goods_list)) {
            return [
                'code' => 1,
                'msg' => '订单提交失败，所选商品库存不足或已下架',
            ];
        }
        $total_price_1 = $total_price + $express_price; // 总价
        if ($this->type == 'GROUP_BUY') { // 团长购买
            $total_price_2 = (($total_price - $colonel) > 0.01 ? ($total_price - $colonel) : 0.01) + $express_price; // 实际付款价
        } else {
            $total_price_2 = $total_price + $express_price; // 实际付款价
        }

        $order = new PtOrder();
        $order->store_id = $this->store_id;
        $order->user_id = $this->user_id;
        $order->order_no = $this->getOrderNo();
        $order->total_price = $total_price_1;
        $order->pay_price = $total_price_2 < 0.01 ? 0.01 : $total_price_2;
        $order->express_price = $express_price;
        $order->addtime = time();
        $order->offline = $this->offline;
//        $order->address = $address->province . $address->city . $address->district . $address->detail;
//        $order->mobile = $address->mobile;
//        $order->name = $address->name;
        if ($this->offline == 1) {
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
        $order->content = $this->content;
        $order->status = 1; // 待付款
        if ($this->type == 'GROUP_BUY_C') {
            $order->limit_time = $parentOrder->limit_time;
        }
        $order->parent_id = $this->parent_id;

        if ($this->type == 'GROUP_BUY' || $this->type == 'GROUP_BUY_C') {      // 拼团
            $order->is_group = 1;
        } elseif ($this->type == 'ONLY_BUY') {  // 单独购买
            $order->is_group = 0;
        }
        $order->colonel = $colonel;

        if ($order->save()) {
            foreach ($goods_list as $goods) {
                $order_detail = new PtOrderDetail();
                $order_detail->order_id = $order->id;
                $order_detail->goods_id = $goods->goods_id;
                $order_detail->num = $goods->num;
                $order_detail->total_price = $goods->price;
                $order_detail->addtime = time();
                $order_detail->is_delete = 0;
                $order_detail->attr = json_encode($goods->attr_list, JSON_UNESCAPED_UNICODE);
                $order_detail->pic = $goods->goods_pic;

                $attr_id_list = [];
                foreach ($goods->attr_list as $item) {
                    array_push($attr_id_list, $item['attr_id']);
                }
                $_goods = PtGoods::findOne($goods->goods_id);
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
            $printer_setting = PrinterSetting::findOne(['store_id' => $this->store_id, 'is_delete' => 0]);
            if ($printer_setting) {
                $type = json_decode($printer_setting->type, true);
                if ($type['order'] && $type['order'] == 1) {
                    $printer_order = new printerPtOrder($this->store_id, $order->id);
                    $res = $printer_order->print_order();
                }
            }
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
     * @param string $goods_info
     * eg.{"goods_id":"22","attr":[{"attr_group_id":1,"attr_group_name":"颜色","attr_id":3,"attr_name":"橙色"},{"attr_group_id":2,"attr_group_name":"尺码","attr_id":2,"attr_name":"M"}],"num":1}
     */
    private function getGoodsListByGoodsInfo($goods_info)
    {
        $goods_info = json_decode($goods_info);
        $goods = PtGoods::findOne([
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
        // 判断当前商品是否设置限时拼团
        if (!$goods->checkLimitTime()) {
            return [
                'code' => 1,
                'msg' => '该商品拼团活动已经结束',
            ];
        }
//        if ($goods->limit_time != '' && $goods->limit_time < time()){
//
//        }
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
        $goods_pic = isset($goods_attr_info['pic']) ? $goods_attr_info['pic'] ?: $goods->cover_pic : $goods->cover_pic;
        if ($this->type == 'GROUP_BUY' || $this->type == 'GROUP_BUY_C') {      // 拼团
            $price = doubleval(empty($goods_attr_info['price']) ? $goods->price : $goods_attr_info['price']) * $goods_info->num;
        } elseif ($this->type == 'ONLY_BUY') {  // 单独购买
            $price = $goods->original_price * $goods_info->num;
        }
        $goods_item = (object)[
            'goods_id' => $goods->id,
            'goods_name' => $goods->name,
            'goods_pic' => $goods_pic,
            'num' => $goods_info->num,
            'price' => $price,
            'attr_list' => $attr_list,
        ];
        $total_price += $goods_item->price;
        return [
            'total_price' => $total_price,
            'list' => [$goods_item],
        ];
    }

    /**
     * @return null|string
     * 生成订单号
     */
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

}