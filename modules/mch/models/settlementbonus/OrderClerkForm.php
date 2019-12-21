<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/9/8
 * Time: 17:20
 */

namespace app\modules\mch\models\settlementbonus;


use app\models\IntegralLog;
use app\models\OrderDetail;
use app\models\User;
use app\modules\mch\models\Model;
use app\modules\mch\models\settlementstatistics\Award;

/**
 * Class OrderClerkForm
 * @package app\modules\mch\models\book
 * 预约订线下核销
 */
class OrderClerkForm extends Model
{
    public $order_id;
    public $store_id;
    public $user_id;

    public $type;
    public $price;
    /**
     * @return array
     * 预约记录线下核销
     * 逻辑操作
     */
    public function save()
    {
        $order = Order::findOne(['id' => $this->order_id, 'store_id' => $this->store_id, 'is_pay' => 1, 'apply_delete' => 0]);
        if (!$order) {
            return [
                'code' => 1,
                'msg' => '记录不存在'
            ];
        }
        if ($order->is_use == 1) {
            return [
                'code' => 1,
                'msg' => '记录已核销'
            ];
        }



        // 获取用户当前积分
        $user = User::findOne(['id' => $order->user_id, 'type' => 1, 'is_delete' => 0]);
        // 减去当前用户账户积分
        $t = \Yii::$app->db->beginTransaction();
        $order->clerk_id = 9999;//后台核销
        $order->shop_id = 9999;//后台核销
        $order->is_use = 1;
        $order->use_time = time();


        if ($this->price) {
            if ($this->type == 1) {
                $order->return_integral = round($order->return_integral + $this->price, 2);
            } else {
                $order->return_integral = round($order->return_integral - $this->price, 2);
            }
        }

        if($order->return_integral < 0){
            return [
                'code'=>1,
                'msg'=>'修改后的积分不能小于0'
            ];
        }
        $user->integral += $order->return_integral;
        $user->coupon += $order->return_integral;
        //记录日志
        $hld = 0;
        $coupon = $order->return_coupon;
        $integral = $order->return_integral;

        $integralLog = new IntegralLog();
        $integralLog->user_id = $user->id;
        //卖优惠券
        $integralLog->content = "（业绩等奖励）：" . $user->nickname . " 欢乐豆" . $user->hld . "：奖励" . $hld . " 豆" . " 优惠券" . $user->coupon . "：奖励" . $coupon . " 张，积分".$user->integral  . '奖励'.$integral;
        $integralLog->integral = $integral;
        $integralLog->hld = $hld;
        $integralLog->coupon = $coupon;
        $integralLog->addtime = time();
        $integralLog->username = $user->nickname;
        $integralLog->operator = 'admin';
        $integralLog->store_id = $this->store_id;
        $integralLog->operator_id = 0;
        $integralLog->save();


        if ($user->save() && $order->save()) {
            $t->commit();
            return [
                'code' => 0,
                'msg' => '成功'
            ];
        } else {
            $t->rollBack();
            return [
                'code' => 1,
                'msg' => '网络异常'
            ];;
        }
    }



    /**
     * @return array
     * 预约记录线下核销
     * 逻辑操作
     */
    public function Getsettlementbonus()
    {
        $order = Order::findOne(['id' => $this->order_id, 'store_id' => $this->store_id, 'is_pay' => 1, 'apply_delete' => 0]);
        if (!$order) {
            return [
                'code' => 1,
                'msg' => '记录不存在'
            ];
        }
        if ($order->is_use == 1) {
            return [
                'code' => 1,
                'msg' => '记录已核销'
            ];
        }
        // 获取用户当前积分
        $user = User::findOne(['id' => $order->user_id, 'type' => 1, 'is_delete' => 0]);

        if($order->is_settlementbonus){
            $settlementbonus =array(
            'all_son_sum_price' =>intval($order->all_son_sum_price), //所有到消费金额
            'all_son_sum_price_level'=> intval($order->all_son_sum_price_level), //所有到奖励金额
            'all_son_sum_price_bookmall'=> intval($order->all_son_sum_price_bookmall), //所有到奖励金额
            'all_son_sum_price_level_bookmall' => intval($order->all_son_sum_price_level_bookmall), //所有到奖励金额
            'all_son_sum_price_crowdc' => intval($order->all_son_sum_price_crowdc), //所有到奖励金额
            'all_son_sum_price_level_crowdc' => intval($order->all_son_sum_price_level_crowdc), //所有到奖励金额
             'all' => intval($order->return_integral), //所有到奖励金额
           );

        } else{
            $settlementbonus=$this->getsettlementbonusByUserId($order->user_id);
            $order->all_son_sum_price = $settlementbonus['all_son_sum_price'];
            $order->all_son_sum_price_level = $settlementbonus['all_son_sum_price_level'];;
            $order->all_son_sum_price_bookmall = $settlementbonus['all_son_sum_price_bookmall'];;
            $order->all_son_sum_price_level_bookmall = $settlementbonus['all_son_sum_price_level_bookmall'];;
            $order->all_son_sum_price_crowdc = $settlementbonus['all_son_sum_price_crowdc'];;
            $order->all_son_sum_price_level_crowdc = $settlementbonus['all_son_sum_price_level_crowdc'];
            $order->settlementbonus_time = time(date(ym));
            $order->is_settlementbonus=1;
            $order->return_integral = $settlementbonus['all'];
            $order->settlementbonus_time = time(date(ym));
        }
        $t = \Yii::$app->db->beginTransaction();

        //记录日志

        if ($order->save()) {
            $t->commit();
        } else {
            $t->rollBack();
            return [
                'code' => 1,
                'msg' => '网络异常'
            ];;
        }

        return [
                'code' => 0,
                'msg' => '成功',
                'data' => $settlementbonus
            ];

    }







    /**
     * $status //1--消费金额排序  2--订单数排序
     */
    public function getsettlementbonusByUserId($user_id)
    {
        $query = User::find()->alias('u')->where(['u.store_id' => $this->store_id, 'u.is_delete' => 0])
            ->where( [
                'u.id' => $user_id])
            //不包含普通积分业绩/普通积分业绩计算预售订单关闭为主
            ->groupBy('u.id');
        $count = $query->count();

        $list = $query->select([
            'u.*',
        ])  ->asArray()->all();

        //总付费人数
        //总人数
        $list_user_haslevel = User::find()->select('id,parent_id')
            ->andWhere(['>', 'level', 0])
            ->asArray()->all();
        $list_user = User::find()->select('id,parent_id')
            ->asArray()->all();

        foreach ($list as $key => $value) {
            $allson = $this->getSubs($list_user, $value['id']);
            $son = $this->getSons($list_user, $value['id']);
            $allson_num = count($allson);
            $son_num = count($son);
            $levelMax = $this->searchmax($allson, 'level');
            $list[$key]['allson_num'] = $allson_num;
            $list[$key]['son_num'] = $son_num;
            //获取层级和人数
            $list[$key]['levelMax'] = $levelMax;
            $list[$key]['level_s_children'] = array_count_values(array_column($allson, 'level'));
            $allson_haslevel = $this->getSubs($list_user_haslevel, $value['id']);
            $son_haslevel = $this->getSons($list_user_haslevel, $value['id']);
            $allson_num_haslevel = count($allson_haslevel);
            $son_num_haslevel = count($son_haslevel);
            $levelMax_haslevel = $this->searchmax($allson_haslevel, 'level');
            $list[$key]['allson_num_haslevel'] = $allson_num_haslevel;
            $list[$key]['son_num_haslevel'] = $son_num_haslevel;
            //获取层级和人数
            $list[$key]['levelMax_haslevel'] = $levelMax_haslevel;
            $list[$key]['level_s_children_haslevel'] = array_count_values(array_column($allson_haslevel, 'level'));
            $all_son_sum_price = 0;
            $all_son_sum_price_level = 0;
            $all_son_sum_price_bookmall = 0;
            $all_son_sum_price_level_bookmall = 0;
            $all_son_sum_price_crowdc = 0;
            $all_son_sum_price_level_crowdc = 0;
            if ($list[$key]['level_s_children']) {//包括所有用户
                foreach ($allson as $key_son => $value_son) {
                    //用户总价格
                    //用户返点 一个用户到价格
                    $one_son_price = $this->actionGetLevelpriceByUser($value_son['id'], $value_son['level']);
                    $all_son_sum_price_level += $one_son_price[0];
                    $all_son_sum_price += $one_son_price[1];


                    //bookmall
                    //用户返点 一个用户到价格
                    $one_son_price_bookmall = $this->actionGetLevelpriceByUserbookmall($value_son['id'], $value_son['level']);
                    $all_son_sum_price_level_bookmall += $one_son_price_bookmall[0];
                    $all_son_sum_price_bookmall += $one_son_price_bookmall[1];

                    //crowdc
                    //用户返点 一个用户到价格
                    $one_son_price_crowdc = $this->actionGetLevelpriceByUsercrowdc($value_son['id'], $value_son['level']);
                    $all_son_sum_price_level_crowdc += $one_son_price_crowdc[0];
                    $all_son_sum_price_crowdc += $one_son_price_crowdc[1];

                }
            }

            $list[$key]['all_son_sum_price'] = intval($all_son_sum_price); //所有到消费金额
            $list[$key]['all_son_sum_price_level'] = intval($all_son_sum_price_level); //所有到奖励金额

            $list[$key]['all_son_sum_price_bookmall'] = intval($all_son_sum_price_bookmall); //所有到奖励金额
            $list[$key]['all_son_sum_price_level_bookmall'] = intval($all_son_sum_price_level_bookmall); //所有到奖励金额

            $list[$key]['all_son_sum_price_crowdc'] = intval($all_son_sum_price_crowdc); //所有到奖励金额
            $list[$key]['all_son_sum_price_level_crowdc'] = intval($all_son_sum_price_level_crowdc); //所有到奖励金额
            $list[$key]['all'] =  intval($all_son_sum_price)+
                intval($all_son_sum_price_level)+
                intval($all_son_sum_price_bookmall)+
                intval($all_son_sum_price_level_bookmall)+
                intval($all_son_sum_price_crowdc)+
                intval($all_son_sum_price_level_crowdc);

        }

        return $list[0];
    }



    public function actionGetpriceByUser($user_id)
    {
        $sum_price = \app\models\Order::find()
            ->alias('o')
            ->where([
//                'is_delete'=>0,
//                'is_cancel'=>0,
                'user_id' => $user_id
            ])
            ->andWhere(['<>', 'o.name', '平台积分'])
            ->sum('pay_price');
        return $sum_price;
    }

    public function actionGetLevelpriceByUser($user_id, $level)
    {

        //统计奖励必须以支付订单支付金额为准 这是钱 所有到商品 订单已经有过滤了
        $order = \app\models\Goods::find()->alias('g')
            ->leftJoin(['od' => OrderDetail::tableName()], 'od.goods_id=g.id')
            ->leftJoin(['o' => \app\models\Order::tableName()], 'o.id=od.order_id')
            ->where(['o.user_id' => $user_id,
                'g.store_id' => $this->store_id])
            ->andWhere(['<>', 'o.name', '平台积分'])
            ->select('g.name,o.pay_price,cat_id,g.id as goods_id')
            ->asArray()
            ->all();

        //统计支付奖励积分

        //根据level获取到层级比例
        $all_price = 0;
        $all_price_level = 0;
        foreach ($order as $key => $value) {
            //获取订单总的提成价格
            //get($level)获取到
            $charge_get = $this->getCharge($level, $value['goods_id']);
            $all_price_level += $charge_get * $value['pay_price'];//支付金额*返回比例
            $all_price += $value['pay_price'];//支付金额*返回比例
        }
        return [$all_price_level, $all_price];
    }


    public function actionGetpriceByUserbookmall($user_id)
    {
        $sum_price = \app\modules\mch\models\bookmall\Order::find()
            ->alias('o')
            ->where([
                'is_delete' => 0,
                'is_cancel' => 0,
                'user_id' => $user_id
            ])
            ->sum('pay_price');
        return $sum_price;
    }


    public function actionGetpriceByUsercrowdc($user_id)
    {
        $sum_price = \app\modules\mch\models\crowdc\Order::find()
            ->alias('o')
            ->where([
                'is_delete' => 0,
                'is_cancel' => 0,
                'user_id' => $user_id
            ])
            ->sum('pay_price');
        return $sum_price;
    }

    public function actionGetLevelpriceByUserbookmall($user_id, $level)
    {

        //统计奖励必须以支付订单支付金额为准 这是钱 所有到商品 订单已经有过滤了
        $order = \app\modules\mch\models\bookmall\Goods::find()->alias('g')
            ->leftJoin(['od' => \app\modules\mch\models\bookmall\OrderDetail::tableName()], 'od.goods_id=g.id')
            ->leftJoin(['o' => \app\modules\mch\models\bookmall\Order::tableName()], 'o.id=od.order_id')
            ->where(['o.user_id' => $user_id,
                'g.store_id' => $this->store_id])
            ->select('g.name,o.pay_price,cat_id,g.id as goods_id')
            ->asArray()
            ->all();
        //统计支付奖励积分
        //根据level获取到层级比例
        $all_price = 0;
        foreach ($order as $key => $value) {
            //获取订单总的提成价格
            //get($level)获取到
            $charge_get = $this->getCharge($level, $value['goods_id']);
            $all_price += $charge_get * $value['pay_price'];//支付金额*返回比例
        }
        return $all_price;
    }

    public function actionGetLevelpriceByUsercrowdc($user_id, $level)
    {

        //统计奖励必须以支付订单支付金额为准 这是钱 所有到商品 订单已经有过滤了
        $order = \app\modules\mch\models\crowdc\Goods::find()->alias('g')
            ->leftJoin(['od' => \app\modules\mch\models\crowdc\OrderDetail::tableName()], 'od.goods_id=g.id')
            ->leftJoin(['o' => \app\modules\mch\models\crowdc\Order::tableName()], 'o.id=od.order_id')
            ->where(['o.user_id' => $user_id,
                'g.store_id' => $this->store_id])
            ->select('g.name,o.pay_price,cat_id,g.id as goods_id')
            ->asArray()
            ->all();
        //统计支付奖励积分
        //根据level获取到层级比例
        $all_price = 0;
        foreach ($order as $key => $value) {
            //获取订单总的提成价格
            //get($level)获取到
            $charge_get = $this->getCharge($level, $value['goods_id']);
            $all_price += $charge_get * $value['pay_price'];//支付金额*返回比例
        }
        return $all_price;
    }


    public function getCharge($level, $goods_id ,$type=1)
    {
        $charge = 0;
        $levelinfo = Award::findOne([
            'level' => $level,
            'quan' => $type,
            'chance' => $goods_id,
            'is_delete' => 0,
            'store_id' => $this->store_id]);

        if (!$levelinfo) {
            $levelinfo = Award::findOne(['level' => $level,'quan' => $type, 'is_delete' => 0, 'store_id' => $this->store_id]);
            if ($levelinfo) {
                $charge = $levelinfo->discount;
            }
        } else {
            $charge = $levelinfo->discount;
        }
        return $charge;
    }

    public function actionTongji($list_user, $user_id)
    {
        //存放team
        //下级
        $allson = $this->getSubs($list_user, $user_id);
        $son = $this->getSons($list_user, $user_id);
        $allson_num = count($allson);
        $son_num = count($son);
        $levelMax = $this->searchmax($allson, 'level');
        $value['allson'] = $allson;
        $value['son'] = $son;
        $value['allson_num'] = $allson_num;
        $value['son_num'] = $son_num;
        //获取层级和人数
        $value['levelMax'] = $levelMax;
        $value['level_s_children'] = array_count_values(array_column($allson, 'level'));

        return $value;
    }


    //获取某分类的直接子分类
    public function getSons($categorys, $catId = 0)
    {
        $sons = array();
        foreach ($categorys as $item) {
            if ($item['parent_id'] == $catId)
                $sons[] = $item;
        }
        return $sons;
    }

    //获取某个分类的所有子分类
    public function getSubs($categorys, $catId = 0, $level = 1)
    {
        $subs = array();
        foreach ($categorys as $item) {
            if ($item['parent_id'] == $catId) {
                $item['level'] = $level;
                $subs[] = $item;
                $subs = array_merge($subs, $this->getSubs($categorys, $item['id'], $level + 1));
            }

        }
        return $subs;
    }

    //获取某个分类的所有父分类
    //方法一，递归
    public function getParents($categorys, $catId)
    {
        $tree = array();
        foreach ($categorys as $item) {
            if ($item['id'] == $catId) {
                if ($item['parent_id'] > 0)
                    $tree = array_merge($tree, $this->getParents($categorys, $item['parentId']));
                $tree[] = $item;
                break;
            }
        }
        return $tree;
    }


    public function searchmax($arr, $field) // 最小值 只需要最后一个max函数  替换为 min函数即可
    {
        if (!is_array($arr) || !$field) { //判断是否是数组以及传过来的字段是否是空
            return false;
        }

        $temp = array();
        foreach ($arr as $key => $val) {
            $temp[] = $val[$field]; // 用一个空数组来承接字段
        }
        return max($temp);  // 用php自带函数 max 来返回该数组的最大值，一维数组可直接用max函数
    }




}