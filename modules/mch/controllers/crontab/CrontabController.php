<?php

namespace app\modules\mch\controllers\crontab;

use app\extensions\PinterOrder;
use app\models\Goods;
use app\models\IntegralLog;
use app\models\Level;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\PrinterSetting;
use app\models\Setting;
use app\models\Store;
use app\models\StoreUser;
use app\models\User;
use app\models\UserShareMoney;
use app\modules\mch\models\BusinessListForm;
use app\modules\mch\models\crontab\DailyData;
use app\modules\mch\models\crontab\Stock;
use app\modules\mch\models\StoreDataForm;
use Yii;
use app\modules\mch\models\StoreUserForm;

/**
 * 商城后台账户
 * Class AccountController
 * @package app\modules\mch\controllers
 */
class CrontabController extends Controller
{
    /**
     * 账户设置
     * @return array|bool|string
     * @throws \yii\base\Exception
     */
    public function actionIndex()
    {
        $identity = Yii::$app->store->identity;
        if (Yii::$app->request->isPost) {
            $form = new StoreUserForm;
            $form->user_id = $identity->user_id;
            return $this->renderJson($form->update(Yii::$app->request->post()));
        } else {
            return $this->render('index', ['model' => $identity]);
        }
    }


    /**
     * 账户设置
     * @return array|bool|string
     * @throws \yii\base\Exception
     */
    public function actionAdd()
    {

        $StoreDataForm = new StoreDataForm();
        $StoreDataForm->store_id = $this->store->id;
        $store_data = $StoreDataForm->search();
        $DailyData = new DailyData();
        $DailyData->store_id =$this->store->id;
        $DailyData->statistics_date = date("Y-m-d");
        $DailyData->addtime = time();
        $data = DailyData::findOne(['statistics_date'=>date("Y-m-d")]);
        if($data){
            if($data->addtime && $data->addtime > strtotime(date("Y-m-d"),time())){
                //如果大于时间戳
            echo '已经存在';
            echo ( date('Y-m-d ',$data->addtime ));;
//            echo (strtotime(date("Y-m-d"),time()));
//            echo '<pre/>';
            echo ($data->statistics_date."\n");
//            var_dump($data);
            die;
            }
        }
        $DailyData->is_delete =0;
        $DailyData->user_count = $store_data['data']['panel_1']['user_count'];
        $DailyData->coupon_count = $store_data['data']['panel_1']['coupon_count'];
        $DailyData->integral_count =$store_data['data']['panel_1']['integral_count'];
        $DailyData->hld_count =$store_data['data']['panel_1']['hld_count'];
        $DailyData->jrintegral_count = $store_data['data']['panel_1']['jrintegral_count'];
        $DailyData->jrhld_count = $store_data['data']['panel_1']['jrhld_count'];
        $DailyData->jrcoupon_count =$store_data['data']['panel_1']['jrcoupon_count'];

        $BusinessListForm = new BusinessListForm();
        $BusinessListForm->store_id = $this->store->id;
        $data = $BusinessListForm->searchforcron();

        $DailyData->peoplesellcount_huanledou1 =$data['peoplesellcount_huanledou1'];
        $DailyData->peoplesellcount_huanledou_charge1 =$data['peoplesellcount_huanledou_charge1'];
        $DailyData->peoplesellcount_xtjl1 =$data['peoplesellcount_xtjl1'];
        $DailyData->peoplesellcount_num1 =$data['peoplesellcount_num1'];
        $DailyData->peoplesellcount1 =$data['peoplesellcount1'];
        $DailyData->peoplebuyercount1 =$data['peoplebuyercount1'];
        $DailyData->peoplesellcount_huanledou2 =$data['peoplesellcount_huanledou2'];
        $DailyData->peoplesellcount_huanledou_charge2 =$data['peoplesellcount_huanledou_charge2'];
        $DailyData->peoplesellcount_xtjl2 =$data['peoplesellcount_xtjl2'];
        $DailyData->peoplesellcount_num2 =$data['peoplesellcount_num2'];
        $DailyData->peoplesellcount2 =$data['peoplesellcount2'];
        $DailyData->peoplebuyercount2 =$data['peoplebuyercount2'];
        $DailyData->peoplesellcount_huanledou3 =$data['peoplesellcount_huanledou3'];
        $DailyData->peoplesellcount_huanledou_charge3 =$data['peoplesellcount_huanledou_charge3'];
        $DailyData->peoplesellcount_xtjl3 =$data['peoplesellcount_xtjl3'];
        $DailyData->peoplesellcount_num3 =$data['peoplesellcount_num3'];
        $DailyData->peoplesellcount3 =$data['peoplesellcount3'];
        $DailyData->peoplebuyercount3 =$data['peoplebuyercount3'];

        $this->renderJson($DailyData->add());
    }


    /**
     * 账户设置
     * @return array|bool|string
     * @throws \yii\base\Exception
     */
    public function actionOrder()
    {
        $this->store_id = 1;
        if (!$this->store_id) {
            return true;
        }
        $this->store = Store::findOne($this->store_id);
        $this->share_setting = Setting::findOne(['store_id' => $this->store_id]);
        \Yii::warning('==>' .'begin');
        $time = time();
        if ($this->store->over_day != 0) {
            $over_day = $time - ($this->store->over_day * 3600);
            //订单超过设置的未支付时间，自动取消
            $count_p = Order::updateAll([
                'is_cancel' => 1,
            ], 'is_pay=0 and addtime<=:addtime and store_id=:store_id',
                [':addtime' => $over_day, ':store_id' => $this->store_id]);
        }
        $delivery_time = $time - ($this->store->delivery_time * 86400);
        $sale_time = $time - ($this->store->after_sale_time * 86400);
        //订单超过设置的确认收货时间，自动确认收货
        /*
        $count = Order::updateAll([
            'is_confirm' => 1, 'confirm_time' => time()],
            'is_delete=0 and is_send=1 and send_time <= :send_time and store_id=:store_id and is_confirm=0',
            [':send_time' => $delivery_time, ':store_id' => $this->store_id]);
        */
        $order_confirm = Order::find()->where([
            'is_delete' => 0, 'is_send' => 1, 'store_id' => $this->store_id, 'is_confirm' => 0
        ])->andWhere(['<=', 'send_time', $delivery_time])->asArray()->all();
        foreach ($order_confirm as $k => $v) {
            Order::updateAll(['is_confirm' => 1, 'confirm_time' => time()], ['id' => $v['id']]);
            $printer_setting = PrinterSetting::findOne(['store_id' => $this->store_id, 'is_delete' => 0]);
            $type = json_decode($printer_setting->type, true);
            if ($type['confirm'] && $type['confirm'] == 1) {
                $printer_order = new PinterOrder($this->store_id, $v['id']);
                \Yii::warning('==>>' . '打印');
                $res = $printer_order->print_order();
            }
        }
        //在待收货订单里面如果是积分商品的话
        $auto_checkout_integral_order_list = Order::find()->alias('o')
            ->where([
                'and',
                [
                    'o.name' => '平台积分',
                    'o.is_pay' => 1,
                    'o.is_delete' => 0,
                    'o.store_id' => $this->store_id,
                    'o.is_sale' => 0,
                    'is_send' => 0,
                    'is_confirm' => 0
                ],
            ])
            ->select(['o.*'])->groupBy('o.id')
            ->offset(0)->limit(20)->asArray()->all();
        if(!$auto_checkout_integral_order_list){
            $auto_checkout_integral_order_list = Order::find()->alias('o')
                ->leftJoin(['od' => OrderDetail::tableName()], 'od.order_id=o.id')
                ->leftJoin(['g' => Goods::tableName()], 'od.goods_id=g.id')
                ->Where([
                    'and',
                    [
                        'g.name' => '高效洗衣液',
                        'o.is_pay' => 1,
                        'o.is_delete' => 0,
                        'o.store_id' => $this->store_id,
                        'o.is_sale' => 0,
                        'o.is_send' => 0,
                        'o.is_confirm' => 0
                    ],
                ])
                ->select(['o.*'])->groupBy('o.id')
                ->offset(0)->limit(20)->asArray()->all();
        }
        //修改代发货状态 待收货状态 确认收货状态
        foreach ($auto_checkout_integral_order_list as $index => $value) {
            Order::updateAll(
                ['pay_type' => 1,'is_send' => 1,'is_confirm' => 1,'confirm_time' => 1508793230],
                ['id' => $value['id']]
            );
        }
        //超过设置的售后时间且没有在售后的订单
        $order_list = Order::find()->alias('o')
            ->where([
                'and',
                ['o.is_delete' => 0, 'o.is_send' => 1, 'o.is_confirm' => 1, 'o.store_id' => $this->store_id, 'o.is_sale' => 0],
                ['<=', 'o.confirm_time', $sale_time],
            ])
            ->leftJoin(OrderRefund::tableName() . ' r', "r.order_id = o.id and r.is_delete = 0")
            ->select(['o.*'])->groupBy('o.id')
            ->andWhere([
                'or',
                'isnull(r.id)',
                ['r.type' => 2],
                ['in', 'r.status', [2, 3]]
            ])
            ->offset(0)->limit(20)->asArray()->all();

        foreach ($order_list as $index => $value) {
            \Yii::warning('==>' . $value['id']);
            Order::updateAll(['is_sale' => 1], ['id' => $value['id']]);
            $this->share_money($value['id']);
            $this->give_integral($value['id']);
        }
        $user_id_arr = Order::find()->select('user_id')->where(['is_delete' => 0, 'store_id' => $this->store_id, 'is_confirm' => 1, 'is_send' => 1])
            ->andWhere(['<=', 'confirm_time', $sale_time])
            ->andWhere(['>=', 'addtime', strtotime(date("Y-m-d"),time())]) //当天订单
            ->groupBy('user_id')->asArray()->all();
        foreach ($user_id_arr as $index => $value) {
            $user = User::findOne(['id' => $value, 'store_id' => $this->store_id]);
            $order_money = Order::find()->where(['store_id' => $this->store_id, 'user_id' => $user->id, 'is_delete' => 0])
                ->andWhere(['is_pay' => 1, 'is_confirm' => 1, 'is_send' => 1])->andWhere(['<=', 'confirm_time', $sale_time])->select([
                    'sum(pay_price)'
                ])->scalar();
            if (!$order_money) {
                $order_money = 0;
            }
            $next_level = Level::find()->where(['store_id' => $this->store_id, 'is_delete' => 0, 'status' => 1])
                ->andWhere(['<=', 'money', $order_money])->orderBy(['level' => SORT_DESC, 'id' => SORT_DESC])->asArray()->one();

            if($user){
                if ($user->level < $next_level['level']) {
                    $user->level = $next_level['level'];
                    $user->save();
                }
            }
        }

        \Yii::warning('==>' .'end');
    }


    /**
     * 账户设置
     * @return array|bool|string
     * @throws \yii\base\Exception
     */
    public function actionOrder1()
    {
        $this->store_id = 1;
        if (!$this->store_id) {
            return true;
        }
        $this->store = Store::findOne($this->store_id);
        $this->share_setting = Setting::findOne(['store_id' => $this->store_id]);
        \Yii::warning('==>' .'begin--order1');
        $time = time();
        if ($this->store->over_day != 0) {
            $over_day = $time - ($this->store->over_day * 3600);
            //订单超过设置的未支付时间，自动取消
            $count_p = Order::updateAll([
                'is_cancel' => 1,
            ], 'is_pay=0 and addtime<=:addtime and store_id=:store_id',
                [':addtime' => $over_day, ':store_id' => $this->store_id]);
        }
        \Yii::warning('==>' .'end-order1');
    }


    /**
     * 账户设置
     * @return array|bool|string
     * @throws \yii\base\Exception delivery_time
     */
    public function actionOrder2()
    {
        $this->store_id = 1;
        if (!$this->store_id) {
            return true;
        }
        $this->store = Store::findOne($this->store_id);
        $this->share_setting = Setting::findOne(['store_id' => $this->store_id]);
        \Yii::warning('==>' .'begin-order2');
        $time = time();
        $delivery_time = $time - ($this->store->delivery_time * 86400);
        $sale_time = $time - ($this->store->after_sale_time * 86400);
        //订单超过设置的确认收货时间，自动确认收货
        $order_confirm = Order::find()->where([
            'is_delete' => 0, 'is_send' => 1, 'store_id' => $this->store_id, 'is_confirm' => 0
        ])->andWhere(['<=', 'send_time', $delivery_time])->asArray()->all();
        foreach ($order_confirm as $k => $v) {
            Order::updateAll(['is_confirm' => 1, 'confirm_time' => time()], ['id' => $v['id']]);
            $printer_setting = PrinterSetting::findOne(['store_id' => $this->store_id, 'is_delete' => 0]);
            $type = json_decode($printer_setting->type, true);
            if ($type['confirm'] && $type['confirm'] == 1) {
                $printer_order = new PinterOrder($this->store_id, $v['id']);
                \Yii::warning('==>>' . '打印');
                $res = $printer_order->print_order();
            }
        }

        \Yii::warning('==>' .'end-order2');
    }


    /**
     * 账户设置
     * @return array|bool|string
     * @throws \yii\base\Exception 积分商品
     */
    public function actionOrder3()
    {
        $this->store_id = 1;
        if (!$this->store_id) {
            return true;
        }
        $this->store = Store::findOne($this->store_id);
        $this->share_setting = Setting::findOne(['store_id' => $this->store_id]);
        \Yii::warning('==>' .'begin-order3');
        $time = time();
        //在待收货订单里面如果是积分商品的话
        $auto_checkout_integral_order_list = Order::find()->alias('o')
            ->where([
                'and',
                [
                    'o.name' => '平台积分',
                    'o.is_pay' => 1,
                    'o.is_delete' => 0,
                    'o.store_id' => $this->store_id,
                    'o.is_sale' => 0,
                    'is_send' => 0,
                    'is_confirm' => 0
                ],
            ])
            ->select(['o.*'])->groupBy('o.id')
            ->offset(0)->limit(20)->asArray()->all();
        if(!$auto_checkout_integral_order_list){
            $auto_checkout_integral_order_list = Order::find()->alias('o')
                ->leftJoin(['od' => OrderDetail::tableName()], 'od.order_id=o.id')
                ->leftJoin(['g' => Goods::tableName()], 'od.goods_id=g.id')
                ->Where([
                    'and',
                    [
                        'g.name' => '高效洗衣液',
                        'o.is_pay' => 1,
                        'o.is_delete' => 0,
                        'o.store_id' => $this->store_id,
                        'o.is_sale' => 0,
                        'o.is_send' => 0,
                        'o.is_confirm' => 0
                    ],
                ])
                ->select(['o.*'])->groupBy('o.id')
                ->offset(0)->limit(20)->asArray()->all();
        }
        //修改代发货状态 待收货状态 确认收货状态
        foreach ($auto_checkout_integral_order_list as $index => $value) {
            Order::updateAll(
                ['pay_type' => 1,'is_send' => 1,'is_confirm' => 1,'confirm_time' => 1508793230],
                ['id' => $value['id']]
            );
        }

        \Yii::warning('==>' .'end-order3');
    }


    /**
     * 账户设置
     * @return array|bool|string
     * @throws \yii\base\Exception 积分商品
     */
    public function actionOrder5()
    {
        $this->store_id = 1;
        if (!$this->store_id) {
            return true;
        }
        $this->store = Store::findOne($this->store_id);
        $this->share_setting = Setting::findOne(['store_id' => $this->store_id]);
        \Yii::warning('==>' .'begin-order5');
        $time = time();
        if ($this->store->over_day != 0) {
            $over_day = $time - ($this->store->over_day * 3600);
            //订单超过设置的未支付时间，自动取消
            $count_p = Order::updateAll([
                'is_cancel' => 1,
            ], 'is_pay=0 and addtime<=:addtime and store_id=:store_id',
                [':addtime' => $over_day, ':store_id' => $this->store_id]);
        }
        $delivery_time = $time - ($this->store->delivery_time * 86400);
        $sale_time = $time - ($this->store->after_sale_time * 86400);

        //超过设置的售后时间且没有在售后的订单
        $order_list = Order::find()->alias('o')
            ->where([
                'and',
                ['o.is_delete' => 0, 'o.is_send' => 1, 'o.is_confirm' => 1, 'o.store_id' => $this->store_id, 'o.is_sale' => 0],
                ['<=', 'o.confirm_time', $sale_time],
            ])
            ->leftJoin(OrderRefund::tableName() . ' r', "r.order_id = o.id and r.is_delete = 0")
            ->select(['o.*'])->groupBy('o.id')
            ->andWhere([
                'or',
                'isnull(r.id)',
                ['r.type' => 2],
                ['in', 'r.status', [2, 3]]
            ])
            ->offset(0)->limit(20)->asArray()->all();

        foreach ($order_list as $index => $value) {
            \Yii::warning('==>' . $value['id']);
            Order::updateAll(['is_sale' => 1], ['id' => $value['id']]);
            $this->share_money($value['id']);
            $this->give_integral($value['id']);
        }

        \Yii::warning('==>' .'end-order5');
    }


    /**
     * 账户设置
     * @return array|bool|string
     * @throws \yii\base\Exception 修改等级
     */
    public function actionOrder6()
    {
        $this->store_id = 1;
        if (!$this->store_id) {
            return true;
        }
        $this->store = Store::findOne($this->store_id);
        $this->share_setting = Setting::findOne(['store_id' => $this->store_id]);
        \Yii::warning('==>' .'begin-order6');
        $time = time();
        $sale_time = $time - ($this->store->after_sale_time * 86400);
        //查询有当天订单的用户
        $user_id_arr = Order::find()->select('user_id','confirm_time')->where(['is_delete' => 0, 'store_id' => $this->store_id, 'is_confirm' => 1, 'is_send' => 1])
            ->andWhere(['<=', 'confirm_time', $sale_time])
            ->andWhere(['>=', 'addtime', strtotime(date("Y-m-d"),time())]) //当天订单
            ->groupBy('user_id')->asArray()->all();
        foreach ($user_id_arr as $index => $value) {
            $user = User::findOne(['id' => $value, 'store_id' => $this->store_id]);
            $order_money = Order::find()->where(['store_id' => $this->store_id, 'user_id' => $user->id, 'is_delete' => 0])
                ->andWhere(['is_pay' => 1, 'is_confirm' => 1, 'is_send' => 1])->andWhere(['<=', 'confirm_time', $sale_time])->select([
                    'sum(pay_price)'
                ])->scalar();
            if (!$order_money) {
                $order_money = 0;
            }
            $next_level = Level::find()->where(['store_id' => $this->store_id, 'is_delete' => 0, 'status' => 1])
                ->andWhere(['<=', 'money', $order_money])->orderBy(['level' => SORT_DESC, 'id' => SORT_DESC])->asArray()->one();

            if($user){
                if ($user->level < $next_level['level']) {
                    $user->level = $next_level['level'];
                    $user->save();
                }
            }
        }

        \Yii::warning('==>' .'end-order6');
    }


    /**
     * @param $parent_id
     * @param $money
     * @return array
     *
     */
    private function money($parent_id, $money)
    {
        if ($parent_id == 0) {
            return ['code' => 1, 'parent_id' => 0];
        }
        $parent = User::findOne(['id' => $parent_id]);
        if (!$parent) {
            return ['code' => 1, 'parent_id' => 0];
        }
        $parent->total_price += $money;
        $parent->price += $money;
        if ($parent->save()) {
            return [
                'code' => 0,
                'parent_id' => $parent->parent_id
            ];
        } else {
            return [
                'code' => 1,
                'parent_id' => 0
            ];
        }
    }

    /**
     * @param $id
     * 佣金发放
     */
    private function share_money($id)
    {
        $order = Order::findOne($id);
        \Yii::warning('==>' . $id . '1');
        if ($this->share_setting->level == 0) {
            return;
        }
        \Yii::warning('==>' . $id . '2');
        if ($order->is_price != 0) {
            return;
        }
//        \Yii::warning('==>'.$id.'3');
        //一级佣金发放
        if ($this->share_setting->level >= 1) {
//            \Yii::warning('==>'.$id.'4');
            $user_1 = User::findOne($order->parent_id);
            if (!$user_1) {
                return;
            }
//            \Yii::warning('==>'.$id.'5');
            $user_1->total_price += $order->first_price;
            $user_1->price += $order->first_price;
            $user_1->save();
//            \Yii::warning('==>'.$id.'6');
            UserShareMoney::set($order->first_price, $user_1->id, $order->id, 0, 1, $this->store_id);
//            \Yii::warning('==>'.$id.'7');
            $order->is_price = 1;
            $order->save();
        }
//        \Yii::warning('==>'.$id.'8');
//        \Yii::warning('==>'.$id.'9');
        //二级佣金发放
        if ($this->share_setting->level >= 2) {
            $user_2 = User::findOne($order->parent_id_1);
            if (!$user_2) {
                if ($user_1->parent_id != 0 && $order->parent_id_1 == 0) {
                    $res = self::money($user_1->parent_id, $order->second_price);
                    UserShareMoney::set($order->second_price, $user_1->parent_id, $order->id, 0, 2, $this->store_id);
                    if ($res['parent_id'] != 0 && $this->share_setting->level == 3) {
                        $res = self::money($res['parent_id'], $order->third_price);
                        UserShareMoney::set($order->third_price, $res['parent_id'], $order->id, 0, 3, $this->store_id);
                    }
                }
                return;
            }
            $user_2->total_price += $order->second_price;
            $user_2->price += $order->second_price;
            $user_2->save();
            UserShareMoney::set($order->second_price, $user_2->id, $order->id, 0, $this->store_id);
        }
        //三级佣金发放
        if ($this->share_setting->level >= 3) {
            $user_3 = User::findOne($order->parent_id_2);
            if (!$user_3) {
                if ($user_2->parent_id != 0 && $order->parent_id_2 == 0) {
                    self::money($user_2->parent_id, $order->third_price);
                    UserShareMoney::set($order->third_price, $user_2->parent_id, $order->id, 0, 3, $this->store_id);
                }
                return;
            }
            $user_3->total_price += $order->third_price;
            $user_3->price += $order->third_price;
            $user_3->save();
            UserShareMoney::set($order->third_price, $user_3->id, $order->id, 0, $this->store_id);
        }
    }

    /**
     * 积分发放
     */
    private function give_integral($id)
    {
        $give = Order::findOne($id);
        if ($give['give_integral'] != 0) {
            return;
        }
        $integral = OrderDetail::find()
            ->andWhere(['order_id' => $give['id'], 'is_delete' => 0])
            ->select([
                'sum(integral)'
            ])->scalar();
        $giveUser = User::findOne(['id' => $give['user_id']]);
        $giveUser->integral += $integral;
        $giveUser->total_integral += $integral;
        $giveUser->save();
        $give->give_integral = 1;
        $give->save();


        //积分日志增加
        $integralLog = new IntegralLog();
        $integralLog->user_id = $giveUser->id;
        $integralLog->content = "管理员 后台操作账号：" . $giveUser->nickname . " 积分充值：" . $integral . " 积分";
        $integralLog->integral = $integral;
        $integralLog->addtime = time();
        $integralLog->username = $giveUser->nickname;
        $integralLog->operator = 'admin';
        $integralLog->store_id = $this->store->id;
        $integralLog->operator_id = 0;
        $integralLog->save();

    }
}