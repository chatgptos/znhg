<?php

namespace app\modules\mch\controllers\crontab;

use app\extensions\PinterOrder;
use app\models\Business;
use app\models\Goods;
use app\models\IntegralLog;
use app\models\Level;
use app\models\Message;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\PrinterSetting;
use app\models\Room;
use app\models\Setting;
use app\models\Store;
use app\models\StoreUser;
use app\models\Topic;
use app\models\User;
use app\models\UserShareMoney;
use app\models\UserShareMoneyDetail;
use app\models\UserShareMoneyIntegral;
use app\modules\api\models\BusinessCommentForm;
use app\modules\mch\models\BusinessListForm;
use app\modules\mch\models\crontab\DailyData;
use app\modules\mch\models\crontab\Stock;
use app\modules\mch\models\settlementstatistics\Award;
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
//                $res = $printer_order->print_order();
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
//            $this->share_money($value['id']);
            $this->give_integral($value['id']);
//            $this->share_money_new($value['id']);
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
        $user_id_arr = Order::find()->select('user_id,confirm_time,id')->where(['is_level' => 0,'is_pay' => 1,'is_delete' => 0, 'store_id' => $this->store_id, 'is_confirm' => 1, 'is_send' => 1])
            ->andWhere(['<=', 'confirm_time', $sale_time])
//            ->andWhere(['>=', 'addtime', strtotime(date("Y-m"),time())]) //当天订单
            ->groupBy('user_id')
            ->limit(20)
            ->asArray()->all();


        foreach ($user_id_arr as $index => $value) {
            $user = User::findOne(['id' => $value, 'store_id' => $this->store_id]);
            $order_money = Order::find()->where(['store_id' => $this->store_id, 'user_id' => $user->id, 'is_delete' => 0])
                ->andWhere(['is_pay' => 1, 'is_confirm' => 1, 'is_send' => 1])
                ->andWhere(['<=', 'confirm_time', $sale_time])
                ->select([
                    'sum(pay_price)'
                ])->scalar();
            \Yii::warning('==>' .$order_money.'user_id-'.$user->id);
            if (!$order_money) {
                $order_money = 0;
            }
            $next_level = Level::find()->where(['store_id' => $this->store_id, 'is_delete' => 0, 'status' => 1])
                ->andWhere(['<=', 'money', $order_money])->orderBy(['level' => SORT_DESC, 'id' => SORT_DESC])->asArray()->one();
            \Yii::warning('==>' .$user->id.'level-'.$user->level.'nextlevel-'.$next_level['level']);
            if($user){
                if ($user->level < $next_level['level']) {
                    \Yii::warning('==>change' .$user->id.'level-'.$user->level.'nextlevel-'.$next_level['level']);
                    $user->level = $next_level['level'];
                    $user->save();
                }
            }
            Order::updateAll(['is_level' => 1], ['id' => $value['id']]);
        }

        \Yii::warning('==>' .'end-order6');
    }


    /**
     * 账户设置
     * @return array|bool|string
     * @throws \yii\base\Exception 积分商品
     */
    public function actionOrder7()
    {
        $this->store_id = 1;
        if (!$this->store_id) {
            return true;
        }
        $this->store = Store::findOne($this->store_id);
        $this->share_setting = Setting::findOne(['store_id' => $this->store_id]);
        \Yii::warning('==>' .'begin-order7');
        $time = time();
        $delivery_time = $time - ($this->store->delivery_time * 86400);
        $sale_time = $time - ($this->store->after_sale_time * 86400);

        //超过设置的售后时间且没有在售后的订单
        $order_list = Order::find()->alias('o')
            ->where([
                'and',
                ['o.is_delete' => 0,
                    'o.is_send' => 1,
                    'o.is_confirm' => 1,
                    'o.store_id' => $this->store_id,
                    'o.is_price' => 0
                ],
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
            ->offset(0)
            ->limit(20)
            ->asArray()->all();
        foreach ($order_list as $index => $value) {
            \Yii::warning('==>' . $value['id']);
            $this->share_money_new($value['id']);
            $this->share_money_integral($value['id']);
            Order::updateAll(['is_price' => 1], ['id' => $value['id']]);
        }
        \Yii::warning('==>' .'end-order7');
    }


//
//    /**
//     * 账户设置
//     * @return array|bool|string
//     * @throws \yii\base\Exception 积分商品
//     */
//    public function actionOrder8()
//    {
//        $this->store_id = 1;
//        if (!$this->store_id) {
//            return true;
//        }
//        \Yii::warning('==>' .'begin-order8');
//        $query = UserShareMoney::find() ->alias('usm')
//            ->where(['usm.store_id'=>$this->store_id,'usm.is_delete'=>0,'usm.status'=>0]);
//        $list = $query->orderBy(['addtime'=>SORT_ASC])
//            ->select("max_level,id,source as level,max_user_id,order_id,user_id,money")
////            ->groupBy('order_id')
//            ->limit(10)
//            ->asArray()->all();
//        //总付费人数
//        //总人数
//
////        var_dump($list);die;
//        $list_user_haslevel = User::find()->select('id,parent_id')
//            ->andWhere(['>', 'level', 0])
//            ->asArray()->all();
//
//        foreach ($list as $i => $amuser_user) {
//            $allson = $this->getSubs($list_user_haslevel, $amuser_user['max_user_id']);
//            //获取层级和人数
//            //需要分享该层级的奖金的用户
//            $allson_group=$this->array_group_by($allson,'level');
//            $all_share_level_users=$allson_group[$amuser_user['level']]; //层级分佣金的层级
//            $all_share_level_users_num=count($all_share_level_users);//这个层级的数量分享佣金
//            $money =round($amuser_user['money']/$all_share_level_users_num, 2);
//            $money = $money < 0.01 ? 0 : $money;
//            $order_id=$amuser_user['order_id'];
//            $user_id=$amuser_user['user_id'];
//            $level=$amuser_user['level'];
//            $max_level=$amuser_user['max_level'];
//            \Yii::warning('==>' . $order_id .'—'.$level.'—'.$money);
//            //发给顶级用户 拿这个层级总的
//            if($user_id==$amuser_user['max_user_id'] && $money){
//                UserShareMoneyDetail::set($amuser_user['money'], $user_id, $order_id, 1, $level, $this->store_id);
//            }
//            foreach ($allson as $j => $level_user) {
//                //最大层级-自身层级=分佣金层级
////当层级相等时候这个层级有奖金 说明这个层级有奖金  因为数据库取出的所以一定有奖
////      佣金层级  其他自身层级（遍历对象）  配置奖项层级
////a         四级  0  最大层级-自身层级    发放 取出最大层级奖金发给自己
////b b1      三级  1    二级                  取出 这个层级奖金 发给 自身层级等于这个的用户（佣金层级应该 =最大层级-自身层级）
////c c2 c3   二级  2    一级
////d d2 c3   一级  3    消费的层级 不计算
////e e1 e2   0     4
//                $levelnew =  $max_level - $level_user['level'] ;
//                if($levelnew>0 && $level_user['level']==$level){
//                    \Yii::warning('==>' . $order_id .'—'.$level.'—'.$money.'-'.$level_user['level'].'-'.$levelnew);
//                    $all_share_level_users=$allson_group[$level_user['level']]; //层级分佣金的层级
//                    $all_share_level_users_num=count($all_share_level_users);//这个层级的数量分享佣金
//                    $money =round($amuser_user['money']/$all_share_level_users_num, 2);
//                    $money = $money < 0.01 ? 0 : $money;
//                    if($money){
//                        UserShareMoneyDetail::set($money, $level_user['id'], $order_id, 1, $amuser_user['level'], $this->store_id);
//                    }
//                }
//            }
//            UserShareMoney::updateAll(['status' => 1], ['id' => $amuser_user['id']]);
//        }
//        \Yii::warning('==>' .'end-order8');
//    }



    /**
     * @param $parent_id
     * @param $money
     * @return array
     *
     */
    private function money_integral($parent_id, $money,$user='',$user_2='')
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
        $parent->total_integral += $money;
        $parent->integral += $money;
        $this->give_integral_UserShareMoneyIntegral($money,$user_2,$user);
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



    public  function array_group_by($arr, $key)
    {
        $grouped = [];
        foreach ($arr as $value) {
            $grouped[$value[$key]][] = $value;
        }
        // Recursively build a nested grouping if more parameters are supplied
        // Each grouped array value is grouped according to the next sequential key
        if (func_num_args() > 2) {
            $args = func_get_args();
            foreach ($grouped as $key => $value) {
                $parms = array_merge([$value], array_slice($args, 2, func_num_args()));
                $grouped[$key] = call_user_func_array('array_group_by', $parms);
            }
        }
        return $grouped;
    }


    //获取某个分类的所有子分类
    private function getSubs($categorys, $catId = 0, $level = 1)
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


    public function getCharge($goods_id ,$type=1)
    {
        $charge = [];
        $levelinfo = Award::findOne([
            'quan' => $type,
            'chance' => $goods_id,
            'status' => 1,
            'is_delete' => 0,
            'store_id' => $this->store_id]);

        if ($levelinfo) {
            $charge['discount'] = $levelinfo->discount;
            $charge['level'] = $levelinfo->level;
        }
        return $charge;
    }

    public function getChargeOld($level, $goods_id ,$type=1)
    {
        $charge = 0;
        $levelinfo = Award::findOne([
            'level' => $level,
            'quan' => $type,
            'chance' => $goods_id,
            'status' => 1,
            'is_delete' => 0,
            'store_id' => $this->store_id]);

        if ($levelinfo) {
//            $levelinfo = Award::findOne(['level' => $level,'quan' => $type, 'is_delete' => 0, 'store_id' => $this->store_id]);
            $charge = $levelinfo->discount;
        }
        return $charge;
    }


    //获取某个分类的所有父分类
    //方法一，递归
    private function getParents($id, $order_id,$money,$maxlevel = 0,$level=0)
    {
        $user = User::findOne($id);
        //计算佣金
        $level= $level+1;
        $user_1 = User::findOne($user['parent_id']);
        if($user['parent_id'] && $user_1){
//            echo $money."<br/>|maxlevel";
//            echo $maxlevel."<br/>";
//            echo $level."<br/>";
            if($maxlevel>=$level&&$money){
                //有钱时候记录 并且有分享者
                if($money){
                    \Yii::warning('==>' . $order_id .'—'.$level.'—'.$money);
                    UserShareMoney::set($money, $user->parent_id, $order_id, 1, $level, $this->store_id);
                }
                $this->getParents($user['parent_id'],$order_id,$money,$maxlevel,$level);
            }
        }else{
                $money=$money/($level-1)*$maxlevel;
                $money = $money < 0.01 ? 0 : $money;
            //最高级就没有parent_id 修改记录最大的用户 用作计算层级
            UserShareMoney::updateAll(['max_user_id' => $user->id,'max_level'=>$level-1,'money'=>$money], ['order_id' =>$order_id]);
        }
    }


    //获取某个分类的所有父分类
    //方法一，递归
    private function getParentsOld($id, $order_id,$order_first_price,$level = 0)
    {
        $user = User::findOne($id);

        //计算佣金
        $level= $level+1;
        //获取该订单该层级应获得的金额
        $order_detail_list = OrderDetail::find()->alias('od')->leftJoin(['g' => Goods::tableName()], 'od.goods_id=g.id')
            ->where(['od.is_delete' => 0, 'od.order_id' => $order_id])
            ->asArray()
            ->select('od.goods_id as goods_id,od.total_price')
            ->all();
        $share_commission_money=0;
        //根据层级计算出应得的佣金
        foreach ($order_detail_list as $item) {
            $item_price = doubleval($item['total_price']);
            $charge_get = $this->getCharge($level, $item['goods_id']);
            $share_commission_money += $item_price * $charge_get / 100;
        }
        $money = $share_commission_money < 0.01 ? 0 : $share_commission_money;
        if($user['parent_id']){
            if($money){
                //有钱时候记录 并且有分享者
                \Yii::warning('==>' . $order_id .'—'.$level.'—'.$item_price.'—'.$charge_get.'—'.$money);
                UserShareMoney::set($money, $user->parent_id, $order_id, 1, $level, $this->store_id);
            }
            $this->getParents($user['parent_id'],$order_id,$order_first_price,$level);
        }else{
            //最高级就没有parent_id 修改记录最大的用户 用作计算层级
            UserShareMoney::updateAll(['max_user_id' => $user->id,'max_level'=>$level-1], ['order_id' =>$order_id]);
        }
//        if($user['parent_id']){  //当层级为20级时候记录
//            if($money && $level==20){
//                \Yii::warning('==>' . $order_id .'—'.$level.'—'.$item_price.'—'.$charge_get.'—'.$money);
//                UserShareMoney::set($money, $user->parent_id, $order_id, 1, $level, $this->store_id);
//            }else{
//                $this->getParents($user['parent_id'],$order_id,$order_first_price,$level);
//            }
//        }else{
//            //获取到最大的顶级用户 并且层级级小于21级
//            //分钱 暂时不分
////            $res = self::money($user->parent_id, $money);
////            echo $user->id .'|'.$level.'|</br>';
////            echo $order_id .'|'.$money.'|</br>';
//            //增加明细
//            if($money && $level<20){
//                \Yii::warning('==>' . $order_id .'—'.$level.'—'.$item_price.'—'.$charge_get.'—'.$money);
//                UserShareMoney::set($money, $user->id, $order_id, 1, $level, $this->store_id);
//            }
//        }
    }



    /**
     * @param $id
     * 佣金发放
     */
    private function share_money_new($id)
    {
        $order = Order::findOne($id);
        if ($order->is_price != 0) {
            return;
        }
        $user_1 = User::findOne($order->parent_id);
        if (!$user_1) {
            return;
        }
        //获取该订单该层级应获得的金额
        $order_detail_list = OrderDetail::find()->alias('od')->leftJoin(['g' => Goods::tableName()], 'od.goods_id=g.id')
            ->where(['od.is_delete' => 0, 'od.order_id' => $order->id])
            ->asArray()
            ->select('od.goods_id as goods_id,od.total_price')
            ->all();
        $maxlevel = 0;
        $share_commission_money = 0;
        $money = 0;
        //根据层级计算出应得的佣金
        foreach ($order_detail_list as $item) {
            $item_price = doubleval($item['total_price']);
            $charge = $this->getCharge($item['goods_id']);
            if ($charge) {
                $charge_get = $charge['discount'];
                $maxlevel = $charge['level'];
                $share_commission_money += $item_price * $charge_get / 100/$maxlevel ;
            }
            $money = $share_commission_money < 0.01 ? 0 : $share_commission_money;
        }
        //这个订单下的某个用户
        $this->getParents($order->user_id, $order->id, $money,$maxlevel);
    }


    /**
     * 设置佣金
     * @param Order $order
     */
    private function setReturnData($order)
    {
        $setting = Setting::findOne(['store_id' => $order->store_id]);
        if (!$setting || $setting->level == 0)
            return;
        $user = User::findOne($order->user_id);//订单本人
        if (!$user)
            return;
        $order->parent_id = $user->parent_id;
        $parent = User::findOne($user->parent_id);//上级
        if (!empty($parent) && $parent->parent_id) {
            $order->parent_id_1 = $parent->parent_id;
            $parent_1 = User::findOne($parent->parent_id);//上上级
            if ($parent_1->parent_id) {
                $order->parent_id_2 = $parent_1->parent_id;
            } else {
                $order->parent_id_2 = -1;
            }
        } else {
            $order->parent_id_1 = -1;
            $order->parent_id_2 = -1;
        }
        $order_total = doubleval($order->total_price - $order->express_price);
        $pay_price = doubleval($order->pay_price - $order->express_price);

        $order_detail_list = OrderDetail::find()->alias('od')->leftJoin(['g' => Goods::tableName()], 'od.goods_id=g.id')
            ->where(['od.is_delete' => 0, 'od.order_id' => $order->id])
            ->asArray()
            ->select('g.individual_share,g.share_commission_first,g.share_commission_second,g.share_commission_third,od.total_price,od.num,g.share_type')
            ->all();
        $share_commission_money_first = 0;//一级分销总佣金
        $share_commission_money_second = 0;//二级分销总佣金
        $share_commission_money_third = 0;//三级分销总佣金
        foreach ($order_detail_list as $item) {
            $item_price = doubleval($item['total_price']);
            if ($item['individual_share'] == 1) {
                $rate_first = doubleval($item['share_commission_first']);
                $rate_second = doubleval($item['share_commission_second']);
                $rate_third = doubleval($item['share_commission_third']);
                if ($item['share_type'] == 1) {
                    $share_commission_money_first += $rate_first * $item['num'];
                    $share_commission_money_second += $rate_second * $item['num'];
                    $share_commission_money_third += $rate_third * $item['num'];
                } else {
                    $share_commission_money_first += $item_price * $rate_first / 100;
                    $share_commission_money_second += $item_price * $rate_second / 100;
                    $share_commission_money_third += $item_price * $rate_third / 100;
                }
            } else {
                $rate_first = doubleval($setting->first);
                $rate_second = doubleval($setting->second);
                $rate_third = doubleval($setting->third);
                if ($setting->price_type == 1) {
                    $share_commission_money_first += $rate_first * $item['num'];
                    $share_commission_money_second += $rate_second * $item['num'];
                    $share_commission_money_third += $rate_third * $item['num'];
                } else {
                    $share_commission_money_first += $item_price * $rate_first / 100;
                    $share_commission_money_second += $item_price * $rate_second / 100;
                    $share_commission_money_third += $item_price * $rate_third / 100;
                }
            }
        }


        $order->first_price = $share_commission_money_first < 0.01 ? 0 : $share_commission_money_first;
        $order->second_price = $share_commission_money_second < 0.01 ? 0 : $share_commission_money_second;
        $order->third_price = $share_commission_money_third < 0.01 ? 0 : $share_commission_money_third;
        $order->save();
    }


    /**
     * @param $id
     * 佣金发放
     */
    private function share_money_integral($id)
    {
        $order = Order::findOne($id);
        //获取下单人
        $user = User::findOne($order->user_id);

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

            //增加积分记录日志，提示消息
            $this->give_integral_UserShareMoneyIntegral($order->first_price,$user_1,$user);
//            \Yii::warning('==>'.$id.'5');
            $user_1->total_integral += $order->first_price;
            $user_1->integral += $order->first_price;
            $user_1->total_price += $order->first_price;
            $user_1->price += $order->first_price;
            $user_1->save();
//            \Yii::warning('==>'.$id.'6');
            UserShareMoneyIntegral::set($order->first_price, $user_1->id, $order->id, 0, 1, $this->store_id);
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
                    $res = self::money_integral($user_1->parent_id, $order->second_price,$user,$user_2);
                    //日志记录
                    $this->give_integral_UserShareMoneyIntegral($order->second_price,$user_2,$user);
                    UserShareMoneyIntegral::set($order->second_price, $user_1->parent_id, $order->id, 0, 2, $this->store_id);
                    if ($res['parent_id'] != 0 && $this->share_setting->level == 3) {
                        $res = self::money_integral($res['parent_id'], $order->third_price);
                        UserShareMoneyIntegral::set($order->third_price, $res['parent_id'], $order->id, 0, 3, $this->store_id);
                    }
                }
                return;
            }
            $user_2->total_price += $order->second_price;
            $user_2->price += $order->second_price;
            $user_2->total_integral += $order->second_price;
            $user_2->integral += $order->second_price;
            $user_2->save();
            UserShareMoneyIntegral::set($order->second_price, $user_2->id, $order->id, 0, $this->store_id);
        }
        //三级佣金发放
        if ($this->share_setting->level >= 3) {
            $user_3 = User::findOne($order->parent_id_2);
            if (!$user_3) {
                if ($user_2->parent_id != 0 && $order->parent_id_2 == 0) {
                    self::money_integral($user_2->parent_id, $order->third_price,$user,$user_2);
                    UserShareMoneyIntegral::set($order->third_price, $user_2->parent_id, $order->id, 0, 3, $this->store_id);
                }
                return;
            }
            $user_3->total_integral += $order->third_price;
            $user_3->integral += $order->third_price;
            $user_2->total_price += $order->third_price;
            $user_2->price += $order->third_price;
            $user_3->save();
            UserShareMoneyIntegral::set($order->third_price, $user_3->id, $order->id, 0, $this->store_id);
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
    private function give_integral_UserShareMoneyIntegral($integral,$user_1,$user)
    {
        if(!$integral){
            return ;
        }
        //积分日志增加
        $Message = new Message();
        $Message->user_id = $user_1->id;
        $Message->content = "推荐".$user->nickname."用户，购值爽系统奖励：" . $integral . " 积分（已到账）";
        $Message->integral = $integral;
        $Message->addtime = time();
        $Message->username = $user_1->nickname;
        $Message->operator = 'admin';
        $Message->store_id = $this->store->id;
        $Message->operator_id = 0;
        $Message->save();


        //积分日志增加
        $integralLog = new IntegralLog();
        $integralLog->user_id = $user_1->id;
        $integralLog->content = "推荐".$user->nickname."用户，购值爽系统奖励：" . $integral . " 积分（已到账）";
        $integralLog->integral = intval($integral);
        $integralLog->addtime = time();
        $integralLog->username = $user_1->nickname;
        $integralLog->operator = 'admin';
        $integralLog->store_id = $this->store->id;
        $integralLog->operator_id = 0;
        $integralLog->save();
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





    /**
     * 账户设置
     * @return array|bool|string
     * @throws \yii\base\Exception 积分商品
     */
    public function actionBusiness1()
    {
        //腾讯大大id
        $this_user_id = 5241;
        \Yii::warning('==>' .'begin-bussiness1-'.$this_user_id);
        //查询优惠券是否有
        $Business = new Business();
        $count_business = $Business::find()->where([ 'user_id' => $this_user_id,'is_exchange' => 0, 'is_delete' => 0])
            ->count();
        if ($count_business>3) {
            return;
        }

        $form = new BusinessCommentForm();
        $form->store_id = $this->store->id;
        $form->user_id = $this_user_id;
        $form->num = 1;
        $form->room_id = 0;//是货柜 货柜表象
        $form->good_id = 0;//是货柜 货柜表象
        $form->article_id = 0;//是货柜 货柜表象

        //从数据库里取用
        //取出来券池文章
        //如果没有文章券
        $exist_business = $Business::find()->where([ 'user_id' => $this_user_id,'is_exchange' => 0, 'is_delete' => 0])
            ->andWhere(['>', 'article_id', 0])
            ->exists();
        if (!$exist_business) {
            $topic_query = Topic::find()->where(['store_id' => $this->store->id, 'is_delete' => 0])->asArray()->all();
            $topic_query_id = $topic_query[array_rand($topic_query)]['id'];
            if($topic_query_id) {
                //如果没有就创建新闻券
                //创建券池 1个券
                $form->article_id = $topic_query_id;//是货柜 货柜表象
                $res=$form->add();
                return;
            }
        }


        $exist_business = $Business::find()->where([ 'user_id' => $this_user_id,'is_exchange' => 0, 'is_delete' => 0])
            ->andWhere(['>', 'room_id', 0])
            ->exists();
        if (!$exist_business) {
            $room_query = Room::find()->where(['store_id' => $this->store->id, 'is_delete' => 0])->asArray()->all();
            $room_query_id = $room_query[array_rand($room_query)]['id'];
            if($room_query_id) {
                //如果没有就创建新闻券
                //创建券池 1个券
                $form->room_id = $room_query_id;//是货柜 货柜表象
                $res=$form->add();
                return;
            }
        }


        $exist_business = $Business::find()->where([ 'user_id' => $this_user_id,'is_exchange' => 0, 'is_delete' => 0])
            ->andWhere(['>', 'good_id', 0])
            ->exists();
        if (!$exist_business) {
            $good_query = Goods::find()->where(['store_id' => $this->store->id, 'is_delete' => 0, 'status' => 1])->asArray()->all();
            $good_query_id = $good_query[array_rand($good_query)]['id'];
            if($good_query_id) {
                //如果没有就创建新闻券
                //创建券池 1个券
                $form->good_id = $good_query_id;//是货柜 货柜表象
                $res=$form->add();
                return;
            }
        }
        $res=$form->add();

    }
}