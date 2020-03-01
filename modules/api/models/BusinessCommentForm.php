<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/16
 * Time: 10:11
 */

namespace app\modules\api\models;


use app\models\Business;
use app\models\BusinessComment;
use app\models\BusinessSetting;
use app\models\IntegralLog;
use app\models\Order;
use app\models\OrderComment;
use app\models\OrderDetail;
use app\models\User;
use yii\helpers\Html;

class BusinessCommentForm extends Model
{
    public $store_id;
    public $user_id;
    public $order_id;
    public $goods_list;
    public $sethuanledou = 7;//7欢乐豆一张
    public $JFTOHLD = 10;//积分对欢乐豆
    public $do_JFTOHLD = false;//积分对欢乐豆是否打开
    public $BusinessSetting;//积分对欢乐豆是否打开

    public $time;//当前时间
    public $open_time;//开放时间
    public $hldtoyhq = 7;//7欢乐豆一张
    public $xtjl = 1;//系统赠送张数
    public $xtjlsell;//系统赠送张数卖方
    public $jftohld;//积分对欢乐豆
    public $hldtojf;//欢乐豆对积分
    public $charge = 3;//百分比手续费
    public $charge1;//百分比手续费2级
    public $charge2;//百分比手续费3级
    public $is_hldtoyhq;//欢乐豆对优惠券是否打开 买优惠券
    public $is_jftohld;//积分对欢乐豆是否打开
    public $is_hldtojf;//欢乐豆对积分是否打开
    public $is_yhqtohld;//优惠券对欢乐豆是否打开 卖优惠券
    public $charge3;//优惠券对欢乐豆是否打开 卖优惠券
    public $chargeNum;//优惠券对欢乐豆是否打开 卖优惠券
    public $chargeNum1;//优惠券对欢乐豆是否打开 卖优惠券
    public $chargeNum2;//优惠券对欢乐豆是否打开 卖优惠券
    public $chargeNum3;//优惠券对欢乐豆是否打开 卖优惠券
    public $charge5;//优惠券对欢乐豆是否打开 卖优惠券


    public function rules()
    {
        return [
        ];
    }


    //卖
    public function add()
    {
        $check = $this->getBusinessSetting();
        if ($check) {
            return json_encode([
                'code' => 1,
                'msg' => $check
            ], JSON_UNESCAPED_UNICODE);
        }
        if (!$this->is_yhqtohld) {
            return json_encode([
                'code' => 1,
                'msg' => '暂未开放'
            ], JSON_UNESCAPED_UNICODE);
        }

        $num = (int)\Yii::$app->request->post('num');

        $user = User::findOne(['id' => $this->user_id]);
        $coupon = $user->coupon;
        $coupon_total = $user->coupon_total;


        if ($num < 1 || !is_int($num)) {
            return json_encode([
                'code' => 1,
                'msg' => '数量不正确'
            ], JSON_UNESCAPED_UNICODE);
        } elseif ($num > $user->coupon) {
            return json_encode([
                'code' => 1,
                'msg' => '优惠券不足'
            ], JSON_UNESCAPED_UNICODE);
        }


        if (!$this->validate())
            return $this->getModelError();

        //发布的时候券出去了
        $user->coupon = $coupon - $num;
        $user->coupon_total = $coupon_total - $num;


        $Business = new Business();
        $Business->store_id = $this->store_id;
        $Business->status = 1;//售卖中 上架
        $Business->user_id = $this->user_id;//卖方用户id

        $guanggao = array(
            '1' => "欢乐豆出，机不可失 买到就是赚到！！"
        , '2' => "欢乐豆出，平台优惠券，立刻出手！！"
        , '3' => "欢乐豆出，可以兑抽奖的优惠券出手！！"
        , '4' => "欢乐豆出，平台保证！立刻出兑换"
        , '5' => '欢乐豆出，良心优惠券出手'
        , '6' => '欢乐豆出，优惠券可参加福利分红'
        , '7' => '欢乐豆出，劲爆优惠券 欢乐豆就出手'
        , '8' => '欢乐豆出，系统奖励系统奖励 兑换就送'
        , '9' => '欢乐豆出，机不可失 买到就是赚到！！'
        , '10' => '欢乐豆出，千万不要错过 ！！'
        , '10' => '欢乐豆出，错过就后悔 ！！'
        , '10' => '欢乐豆出，手续费劲爆最低 ！！'
        , '10' => '欢乐豆出，货柜分红想要就  收集优惠券 ！！'
        , '10' => '欢乐豆出，今天不抢 明天就后悔 ！！'
        );
        $Business->title = $num . '优惠券，' . $this->hldtoyhq * $num . $guanggao[array_rand($guanggao)];//卖的张数
//        $Business->order_num = $this->user_id; //成交交易数量
//        $Business->integral = $this->user_id; //需要积分


//      成交金额内扣除手续费（冻结手续费 减少可用欢乐豆）
//      发布时候扣除卡券数量

//      卖的张数
        $Business->num = $num;//卖的张数


//      欢乐豆实际总价值
        $Business->huanledou = (int) intval($this->hldtoyhq * $num);//卖的张数*平台固定的每张欢乐豆价值

//      手续费欢乐豆价值
        $Business->huanledou_charge = (int) intval(($this->getCharge($num)) * 0.01 * ($this->hldtoyhq * $num));//卖的张数*平台固定的欢乐豆

//      系统奖励
        $Business->xtjl = (int)intval($this->xtjl);//系统奖励

//      合计收益
        $huanledou_total = (int)intval($Business->huanledou - $Business->huanledou_charge);// 需要的欢乐豆 + 总的*手续费

        $Business->addtime = time();



        $t = \Yii::$app->db->beginTransaction();

        //卖家 卖
        $this->insertintegralLog(1, $user->id, $Business->num,$Business->huanledou, $Business->xtjl, $Business->huanledou_charge);


        if ($Business->save() && $user->save()) {
            $t->commit();

            $user = User::findOne(['id' => $this->user_id]);
            return [
                'code' => 0,
                'data' => array(
                    'huanledou_total' => $huanledou_total,//合计收益
                    'coupon' => $user->coupon,//合计收益
                    'coupon_total' => $user->coupon_total,//合计收益
                    'huanledou_charge' => $Business->huanledou_charge,//合计收益
                ),
                'msg' => '提交成功',
            ];
        } else {
            $t->rollBack();
            return $this->getModelError($Business);
        }

    }

    /*
     * 买
     * */
    public function exchange()
    {

        $check = $this->getBusinessSetting();
        if ($check) {
            return json_encode([
                'code' => 1,
                'msg' => $check
            ], JSON_UNESCAPED_UNICODE);
        }

        if (!$this->is_hldtoyhq) {
            return json_encode([
                'code' => 1,
                'msg' => '暂未开放'
            ], JSON_UNESCAPED_UNICODE);
        }


        if (!$this->validate())
            return $this->getModelError();

        $order_id = (int)\Yii::$app->request->post('order_id');

        $order = Business::findOne([
            'id' => $order_id,
            'store_id' => $this->store_id,
            'is_delete' => 0,
        ]);
        if (!$order)
            return [
                'code' => 1,
                'msg' => '交易不存在',
            ];


        $user = User::findOne(['id' => $order->user_id]);


        $user_buyer = User::findOne(['id' => $this->user_id]);

        if (!$order || !$user_buyer)
            return [
                'code' => 1,
                'msg' => '用户不存在',
            ];



        if ($this->user_id == $order->user_id) {
            return [
                'code' => 1,
                'msg' => '自己不能购买',
            ];
        }


        $order->is_exchange = 1;
        $order->user_id_buyer = $this->user_id;

        //扣除双方手续费
        //卖家
        $sellhld = (int)intval($user->hld + $order->huanledou - $order->huanledou_charge);//欢乐豆卖家 + 总的-手续费
        $selltotal_hld = (int)intval($user->total_hld + $order->huanledou - $order->huanledou_charge);//欢乐豆卖家 + 总的-手续费


        $user->hld = intval($sellhld);
        $user->total_hld = intval($selltotal_hld);
        //xtjl
        //失去券 发布的时候券就失去了
//        $user->coupon = $user->coupon - $order->num;
//        $user->coupon_total = $user->coupon_total - $order->num;

        //买家
        $buyhld = (int)intval($user_buyer->hld - $order->huanledou - $order->huanledou_charge);//欢乐豆卖家 + 总的-手续费
        $buytotal_hld = (int)intval($user_buyer->total_hld - $order->huanledou - $order->huanledou_charge);//欢乐豆卖家 + 总的-手续费

        $user_buyer->hld =intval( $buyhld);
        $user_buyer->total_hld = intval($buytotal_hld);
        //xtjl


        //得到券
        $buycoupon = (int)intval($user_buyer->coupon + $order->num + $order->xtjl);
        $buycoupon_total = (int)intval($user_buyer->coupon_total + $order->num + $order->xtjl);

        $user_buyer->coupon = $buycoupon;
        $user_buyer->coupon_total = $buycoupon_total;

        if (($user_buyer->hld) < 0) {
            return [
                'code' => 1,
                'msg' => '欢乐豆不够',
            ];
        }

        $t = \Yii::$app->db->beginTransaction();


        //卖家 卖
        $this->insertintegralLog(1, $user->id, $order->num, $order->huanledou, $order->xtjl, $order->huanledou_charge);
        //买家 买
        $this->insertintegralLog(2, $user_buyer->id, $order->num, $order->huanledou, $order->xtjl, $order->huanledou_charge);





        if ($order->save() && $user->save() && $user_buyer->save()) {
            $t->commit();
            return [
                'code' => 0,
                'msg' => '交易成功',
                'data' => array(
                    'coupon' => $user_buyer->coupon,
                    'nickname' => $user_buyer->nickname,
                    'is_exchange' => 1,
                )
            ];
        } else {
            $t->rollBack();
            return $this->getModelError($order);
        }

    }


    public function insertintegralLog($rechangeType, $user_id, $num, $hld = 0, $xtjl = 0, $sxf)
    {


        $user = User::findOne(['id' => $user_id]);
        $integralLog = new IntegralLog();
        $integralLog->user_id = $user->id;
        if ($rechangeType == '2') {
            //买优惠券
            $integralLog->content = "管理员（欢乐豆兑换优惠券） 后台操作账号：" . $user->nickname . " 欢乐豆".$user->hld."已经扣除：" . $hld . " 豆" . " 优惠券".$user->coupon."已经充值（包含奖励）：" . $num . " 张" . "系统奖励" . $xtjl;
        } elseif ($rechangeType == '1') {
            //卖优惠券
            $integralLog->content = "管理员（优惠券换欢乐豆） 后台操作账号：" . $user->nickname . " 欢乐豆".$user->hld."已经充值：" . $hld . " 豆" . " 优惠券".$user->coupon."已经扣除：" . $num . " 张,（发布时候已经扣除优惠券）（交易时扣除去手续费" . $sxf . '个欢乐豆）';
        }

        $integralLog->hld = $hld;
        $integralLog->coupon = $num + $xtjl;
        $integralLog->addtime = time();
        $integralLog->username = $user->nickname;
        $integralLog->operator = 'admin';
        $integralLog->store_id = $this->store_id;
        $integralLog->operator_id = 0;
        $integralLog->save();
    }


    public function getBusinessSetting()
    {
        $this->BusinessSetting = BusinessSetting::findOne(['store_id' => $this->store_id]);
        $this->open_time = $this->BusinessSetting['open_time'];
        $this->hldtoyhq = $this->BusinessSetting['hldtoyhq'];
        $this->xtjl = $this->BusinessSetting['xtjl'];
        $this->xtjlsell = $this->BusinessSetting['xtjlsell'];
        $this->jftohld = $this->BusinessSetting['jftohld'];
        $this->hldtojf = $this->BusinessSetting['hldtojf'];
        $this->charge = $this->BusinessSetting['charge'];
        $this->charge1 = $this->BusinessSetting['charge1'];
        $this->charge2 = $this->BusinessSetting['charge2'];


        $this->is_hldtoyhq = $this->BusinessSetting['is_hldtoyhq'];
        $this->is_jftohld = $this->BusinessSetting['is_jftohld'];
        $this->is_hldtojf = $this->BusinessSetting['is_hldtojf'];
        $this->is_yhqtohld = $this->BusinessSetting['is_yhqtohld'];

        $this->charge3 = $this->BusinessSetting['charge3'];
        $this->charge5 = $this->BusinessSetting['charge5'];
        $this->chargeNum = $this->BusinessSetting['chargeNum'];
        $this->chargeNum1 = $this->BusinessSetting['chargeNum1'];
        $this->chargeNum2 = $this->BusinessSetting['chargeNum2'];
        $this->chargeNum3 = $this->BusinessSetting['chargeNum3'];


        $open_time = json_decode($this->open_time, true);
        $this->time = intval(date('H'));

        if (!in_array($this->time, $open_time)) {
            return '集市未到开放时间';
        }

        return false;
    }


    public function getBusinessSettingAll()
    {
        $this->BusinessSetting = BusinessSetting::findOne(['store_id' => $this->store_id]);
        $this->open_time = $this->BusinessSetting['open_time'];
        $this->hldtoyhq = $this->BusinessSetting['hldtoyhq'];
        $this->xtjl = $this->BusinessSetting['xtjl'];
        $this->xtjlsell = $this->BusinessSetting['xtjlsell'];
        $this->jftohld = $this->BusinessSetting['jftohld'];
        $this->hldtojf = $this->BusinessSetting['hldtojf'];
        $this->charge = $this->BusinessSetting['charge'];
        $this->charge1 = $this->BusinessSetting['charge1'];
        $this->charge2 = $this->BusinessSetting['charge2'];
        $this->is_hldtoyhq = $this->BusinessSetting['is_hldtoyhq'];
        $this->is_jftohld = $this->BusinessSetting['is_jftohld'];
        $this->is_hldtojf = $this->BusinessSetting['is_hldtojf'];
        $this->is_yhqtohld = $this->BusinessSetting['is_yhqtohld'];
        $open_time = json_decode($this->open_time, true);
        $this->time = intval(date('H'));


        $this->charge3 = $this->BusinessSetting['charge3'];
        $this->charge5 = $this->BusinessSetting['charge5'];
        $this->chargeNum = $this->BusinessSetting['chargeNum'];
        $this->chargeNum1 = $this->BusinessSetting['chargeNum1'];
        $this->chargeNum2 = $this->BusinessSetting['chargeNum2'];
        $this->chargeNum3 = $this->BusinessSetting['chargeNum3'];

        $rechangeType = (int)\Yii::$app->request->post('rechangeType');

        if ($rechangeType == 0) {//is_yhqtohld     卖 sell

            if (!in_array($this->time, $open_time)) {
                return json_encode([
                    'code' => 1,
                    'msg' => '集市未到开放时间',
                ], JSON_UNESCAPED_UNICODE);
            } elseif (!$this->is_yhqtohld) {
                return json_encode([
                    'code' => 1,
                    'msg' => '暂未开放',
                ], JSON_UNESCAPED_UNICODE);
            }

        } elseif ($rechangeType == 2) {//is_jftohld


        } elseif ($rechangeType == 2) {//is_jftohld


        } elseif ($rechangeType == 3) {//   is_hldtoyhq   getcard 买


            if (!in_array($this->time, $open_time)) {
                return json_encode([
                    'code' => 1,
                    'msg' => '集市未到开放时间',
                ], JSON_UNESCAPED_UNICODE);
            } elseif (!$this->is_hldtoyhq) {
                return json_encode([
                    'code' => 1,
                    'msg' => '暂未开放',
                ], JSON_UNESCAPED_UNICODE);
            }


        } elseif ($rechangeType == 4) {//is_hldtojf


        } elseif ($rechangeType == 5) {//is_yhqtohld


        } else {


        }


        return json_encode([
            'code' => 0,
            'data' => array(
                'open_time' => $this->open_time,
                'hldtoyhq' => $this->open_time,
                'xtjl' => $this->open_time,
                'xtjlsell' => $this->open_time,
                'jftohld' => $this->open_time,
                'hldtojf' => $this->open_time,
                'charge' => $this->open_time,
                'charge1' => $this->open_time,
                'is_hldtoyhq' => $this->open_time,
                'is_jftohld' => $this->open_time,
                'is_hldtojf' => $this->open_time,
                'is_yhqtohld' => $this->open_time,
                'is_opentime' => in_array($this->time, $open_time),
            )
        ], JSON_UNESCAPED_UNICODE);
    }


    /*
     *
     * 卖优惠券 预计欢乐豆
     *
     * */
    public function PreJfToHld()
    {
        $check = $this->getBusinessSetting();
        if ($check) {
            return json_encode([
                'code' => 1,
                'msg' => $check
            ], JSON_UNESCAPED_UNICODE);
        }

        if (!$this->is_yhqtohld) {
            return json_encode([
                'code' => 1,
                'msg' => '暂未开放'
            ], JSON_UNESCAPED_UNICODE);
        }


        $num = (int)\Yii::$app->request->post('num');

        if (empty($num)) {
            return json_encode([
                'code' => 1,
                'msg' => '数量不正确'
            ], JSON_UNESCAPED_UNICODE);
        }


//      成交金额内扣除手续费（冻结手续费 减少可用欢乐豆）
//      发布时候扣除卡券数量

//      卖的张数

//      欢乐豆实际总价值
        $huanledou =  (int)intval($this->hldtoyhq * $num);//卖的张数*平台固定的每张欢乐豆价值

//      手续费欢乐豆价值
        $huanledou_charge = (int)intval( ($this->getCharge($num)) * 0.01 * ($this->hldtoyhq * $num));//卖的张数*平台固定的欢乐豆

//      系统奖励
        $xtjl = $this->xtjl;//系统奖励

//      合计收益
        $huanledou_total =  (int)intval($huanledou -$huanledou_charge) ;// 需要的欢乐豆 + 总的*手续费

        return [
            'code' => 0,
            'data' => array(
                'num' => (int)$num,//合计收益
                'huanledou' => (int)$huanledou,//合计收益
                'huanledou_charge' => (int)$huanledou_charge,//合计收益
                'xtjl' => 0,//合计收益
                'huanledou_total' => (int)$huanledou_total,//合计收益
            ),
            'msg' => '计算中...',
        ];

    }

    public function getCharge($num)
    {
        $charge = 0;

        if ($num <= $this->chargeNum && $num >= 0) {
//            $this->charge = 100 / 7;
            $charge = $this->charge;  //1张
        } elseif ($num <= $this->chargeNum1 && $num > $this->chargeNum) {
//            $this->charge = 100 / 7;
            $charge = $this->charge1; //1-6
        } elseif ($num <= $this->chargeNum2 && $num > $this->chargeNum1) {
            $charge = $this->charge2;//7-18
//            $this->charge = 3;
        } elseif ($num <= $this->chargeNum3 && $num > $this->chargeNum2) {
//            $this->charge = 1;
            $charge = $this->charge3; //18以上
        } else {
            $charge = $this->charge5;  //1张
        }

        return $charge;
    }

    public function save()
    {
        if (!$this->validate())
            return $this->getModelError();
        $order = Business::findOne([
            'id' => $this->order_id,
            'store_id' => $this->store_id,
            'user_id' => $this->user_id,
            'is_delete' => 0,
        ]);
        if (!$order)
            return [
                'code' => 1,
                'msg' => '订单不存在或已删除',
            ];
        $goods_list = $this->goods_list;
        if (!$goods_list)
            return [
                'code' => 1,
                'msg' => '信息不能为空',
            ];
        $t = \Yii::$app->db->beginTransaction();

        $order_comment = new BusinessComment();
        $order_comment->store_id = $this->store_id;
        $order_comment->user_id = $this->user_id;
        $order_comment->order_id = $this->order_id;
        $order_comment->content = Html::encode($this->goods_list);
        //$order_comment->content = mb_convert_encoding($order_comment->content, 'UTF-8');
        $order_comment->content = preg_replace('/[\xf0-\xf7].{3}/', '', $order_comment->content);
        $order_comment->addtime = time();
        if (!$order_comment->save()) {
            $t->rollBack();
            return $this->getModelError($order_comment);
        }
        //被评论了  //被交易了
        $order->is_comment = 1;
        $order->user_id_buyer = $this->user_id;
        if ($order->save()) {
            $t->commit();
            return [
                'code' => 0,
                'msg' => '提交成功',
            ];
        } else {
            $t->rollBack();
            return $this->getModelError($order);
        }

    }


    public function JfToHld()
    {
        $this->getBusinessSetting();
        if (!$this->is_jftohld) {
            return json_encode([
                'code' => 1,
                'msg' => '暂未开放'
            ], JSON_UNESCAPED_UNICODE);
        }


        $integral = (int)\Yii::$app->request->post('integral');
        $hld = (int)\Yii::$app->request->post('hld');
        $rechangeType = \Yii::$app->request->post('rechangeType', 2);
        $user = User::findOne(['id' => $this->user_id, 'store_id' => $this->store_id]);
        if (!$user) {
            return json_encode([
                'code' => 1,
                'msg' => '用户不存在，或已删除'
            ], JSON_UNESCAPED_UNICODE);
        }
        if (empty($integral) && empty($hld)) {
            return json_encode([
                'code' => 1,
                'msg' => '数量不正确'
            ], JSON_UNESCAPED_UNICODE);
        }


        if ($rechangeType == '2') {
            //扣除积分

            if ($integral > $user->integral) {
                return json_encode([
                    'code' => 1,
                    'msg' => '积分不足'
                ], JSON_UNESCAPED_UNICODE);
            } elseif ($integral < 1) {
                return json_encode([
                    'code' => 1,
                    'msg' => '不能小于1'
                ], JSON_UNESCAPED_UNICODE);
            }

            $user->integral -= $integral;
            //增加欢乐豆
            $hldJf = $integral * $this->jftohld;
            $user->hld += $hldJf;
            $user->total_hld += $hldJf;


        } elseif ($rechangeType == '1') {
            //充值积分 扣除欢乐豆

            if (!$this->is_jftohld) {
                return json_encode([
                    'code' => 1,
                    'msg' => '暂不支持'
                ], JSON_UNESCAPED_UNICODE);
            }

            $hldJf = $hld / $this->jftohld;

            if ($hld > $user->hld) {
                return json_encode([
                    'code' => 1,
                    'msg' => '欢乐豆不足'
                ], JSON_UNESCAPED_UNICODE);
            } elseif (!is_int($hldJf)) {
                return json_encode([
                    'code' => 1,
                    'msg' => '请输入' . $this->jftohld . '的倍数'
                ], JSON_UNESCAPED_UNICODE);
            } elseif ($hldJf < 1) {
                return json_encode([
                    'code' => 1,
                    'msg' => '不能小于10'
                ], JSON_UNESCAPED_UNICODE);
            }

            $user->integral += $hldJf;
            $user->total_integral += $hldJf;

            //增加欢乐豆
            $user->hld -= $hld;
            $user->total_hld -= $hld;

            $integral = $hldJf;
        }


        $integralLog = new IntegralLog();
        $integralLog->user_id = $user->id;
        if ($rechangeType == '2') {
            $integralLog->content = "管理员（积分兑换欢乐豆） 后台操作账号：" . $user->nickname . " 积分扣除：" . $integral . " 积分" . " 欢乐豆充值：" . $integral * $this->jftohld . " 个";
        } elseif ($rechangeType == '1') {
            $integralLog->content = "管理员（欢乐豆兑换积分） 后台操作账号：" . $user->nickname . " 积分充值：" . $integral . " 积分" . " 欢乐豆扣除：" . $integral * $this->jftohld . " 个";
        }

        $integralLog->integral = $integral;
        $integralLog->addtime = time();
        $integralLog->username = $user->nickname;
        $integralLog->operator = 'admin';
        $integralLog->store_id = $this->store_id;
        $integralLog->operator_id = 0;

        $t = \Yii::$app->db->beginTransaction();

        if ($user->save() && $integralLog->save()) {
            $t->commit();
            $user = User::findOne(['id' => $this->user_id, 'store_id' => $this->store_id]);
            return [
                'code' => 0,
                'msg' => '交易成功',
                'data' => array(
                    'is_exchange' => 1,
                    'user_info' => array(
                        'hld' => $user->hld,
                        'integral' => $user->integral,
                        'coupon' => $user->coupon
                    )
                )
            ];
        } else {
            $t->rollBack();
            return $this->getModelError($user);
        }
    }

}