<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/12/14
 * Time: 11:32
 */

namespace app\modules\api\models\settlementbonus;


use app\models\IntegralLog;
use app\models\User;
use app\models\UserShareMoney;
use app\models\UserShareMoneyDetail;
use app\modules\api\models\Model;
use app\modules\mch\models\settlementstatistics\AwardFuli;

class OrderPreviewFrom extends Model
{
    public $store_id;
    public $user_id;

    public $goods_id;

    public $form_list;
    public $form_id;

    public $pay_type = 'WECHAT_PAY';

    private $wechat;
    private $order;
    private $user;

    public function search()
    {
        $goods = Goods::find()
            ->andWhere(['id'=>$this->goods_id,'is_delete'=>0,'status'=>1,'store_id'=>$this->store_id])
            ->asArray()
            ->one();

        $formList = Form::find()
            ->andWhere(['goods_id'=>$this->goods_id,'is_delete'=>0,'store_id'=>$this->store_id])
            ->orderBy('sort DESC')
            ->asArray()
            ->all();
        foreach ($formList AS $k => $v){
            if ($v['type'] == 'radio' || $v['type'] == 'checkbox'){
//                $formList[$k]['default'] = explode(',' , $v['default']);
                $defaultArr = explode(',' , trim($v['default'],','));
                foreach ($defaultArr AS $key => $value){
                    $defaultArr2[$key]['name'] = $value;
                    if ($key==0){
                        $defaultArr2[$key]['selected'] = true;
                    }else{
                        $defaultArr2[$key]['selected'] = false;
                    }
                }
                $formList[$k]['default'] = $defaultArr2;
            }
            if ($v['type']=='date'){
                $formList[$k]['default'] = $v['default']?:date('Y-m-d',time());
            }
        }
        return [
            'code'  => 0,
            'msg'   => '成功',
            'data'  => [
                'goods'     => $goods,
                'form_list' => $formList,
            ],
        ];
    }

    public function save()
    {
        $this->wechat = $this->getWechat();
        $goods = Goods::find()
            ->andWhere(['id'=>$this->goods_id,'is_delete'=>0,'status'=>1,'store_id'=>$this->store_id])->one();
        if (!$goods){
            return [
              'code'    => 1,
              'msg'     => '奖励不存在',
            ];
        }

        $p = \Yii::$app->db->beginTransaction();

        $this->user = User::findOne(['id' => $this->user_id, 'type' => 1, 'is_delete' => 0]);


//        查找当月有没有申请记录


        //当天限制一人
        //会卡死了
        //查询当前用户订单

        $query = Order::find()
            ->alias('o')
            ->select([
                'o.id',
            ])
            ->where([
                'o.is_delete' => 0,
                'o.store_id' => $this->store_id,
                'o.user_id' => $this->user_id,
                'o.is_cancel' => 0,
                'g.id' => $goods->id,
            ])->leftJoin(['g'=>Goods::tableName()],'o.goods_id=g.id');
        $query_num_buy_order = $query->count();

        $query_day = Order::find()
            ->alias('o')
            ->select([
                'o.id',
            ])
            ->where([
                'AND',
                [
                    'o.is_delete' => 0,
                    'o.store_id' => $this->store_id,
                    'o.user_id' => $this->user_id,
                    'o.is_cancel' => 0,
                    'g.id' => $goods->id,
                ],
                ['>', 'o.addtime', strtotime(date('Y-m-d'))],
            ])
            ->leftJoin(['g'=>Goods::tableName()],'o.goods_id=g.id');
        $query_num_buy_order_day = $query_day->count();
        //查找是否订单数量
        if($query_num_buy_order_day > $goods->buy_max_day ){
            return [
                'code' => 1,
                'msg' => "申请数量超过限制！ 奖励“" . $goods->name . '”每日最多允许申请' . $goods->buy_max_day . '件，请返回重新下单申请其他奖励',
            ];
        } elseif ($query_num_buy_order > $goods->buy_max){
            return [
                'code' => 1,
                'msg' => "申请数量超过限制！ 奖励“" . $goods->name . '”最多允许申请' . $goods->buy_max . '件，请返回重新下单申请其他奖励',
            ];
        }




        $order = new Order();
        $order->store_id = $this->store_id;
        $order->goods_id = $goods->id;
        $order->user_id  = $this->user_id;
        $order->order_no = $this->getOrderNo();
        $order->total_price = $goods->price;
        $order->pay_price = $goods->price;
        $order->is_pay = 0;
        $order->is_use = 0;
        $order->is_comment = 0;
        $order->addtime = time();
        $order->is_delete = 0;
        $order->form_id = $this->form_id;
        $order->return_coupon = $goods->return_coupon;
        $order->return_integral = $goods->return_integral;




        $UserShareMoney=0;
        $fuliquan_num=0;
        if ($order->goods_id ==17){

            //查询当月限制
            $query_month = Order::find()
                ->alias('o')
                ->select([
                    'o.id',
                ])->leftJoin(['g'=>Goods::tableName()],'o.goods_id=g.id')
                ->where([
                    'AND',
                    [
                        'o.is_delete' => 0,
                        'o.store_id' => $this->store_id,
                        'o.user_id' => $this->user_id,
                        'o.is_cancel' => 0,
                        'g.id' => $goods->id,
                    ],
                    ['>', 'o.addtime', strtotime(date('Y-m'))],
                ]);

            if($query_month->count()){
                return [
                    'code' => 1,
                    'msg' => '或已经在结算中，当月只能申请一次',
                ];
            }

            //正在结算的状态的订单 当申请当时候全部改为申请中 查询时候就计算申请中的订单
            //把上个月的
            $UserShareMoney =UserShareMoney::updateAll(['status' => 1], [
                'AND',
                ['user_id' => $order->user_id,],
                ['status' => 0,],
                ['is_delete' => 0,],
                ['>=', 'addtime', strtotime(date('Y-m-01', strtotime('-1 month')))],
                ['<=', 'addtime', strtotime(date('Y-m-t', strtotime('-1 month')))],
            ]);

            if(!$UserShareMoney|| $UserShareMoney <1){
                $p->rollBack();
                return [
                    'code'  => 1,
                    'msg'   => '暂时无奖励或小于1',
                ];
            }
            $money = UserShareMoney::find()->alias('usm')
                ->where([
                    'user_id' => $order->user_id,
                    'status' => 1,//已经申请的
                ])
                ->asArray()
                ->sum('money');
            $order->return_integral = $money;
        }elseif($order->goods_id ==18){
            //福利分红
            $awardFuli = AwardFuli::findOne([ 'is_delete' => 0, 'store_id' => $this->store_id, 'status' => 1]);
            if(time()<($awardFuli->end_fulichi_time)){
                return [
                    'code'  => 1,
                    'msg'   => '未到结束时间'.date('Y-m-d',$awardFuli->end_fulichi_time),
                ];
            }
            $fulichi_all_money =  $awardFuli->all_money;;//总价值
            $fulichi_money =  $awardFuli->money;//每份奖励
            $fulichiTime = date('Y-m-d', $awardFuli->end_fulichi_time);//截止时间
            $fulichiNum =  $awardFuli->num;;//份数
            $fuliquan_num = User::find()->where(['store_id' => $this->store_id, 'id' => $order->user_id, 'is_delete' => 0])
                ->select([
                    'sum(fuliquan)'
                ])->scalar();
            $fuliquan_num_all = User::find()->where(['store_id' => $this->store_id, 'is_delete' => 0])
                ->select([
                    'sum(fuliquan)'
                ])->scalar();
            if($fulichi_money){
                $UserShareMoney=intval($fulichi_money*$fuliquan_num);
            }else{
                $UserShareMoney=intval($fulichi_all_money/$fuliquan_num_all*$fuliquan_num);
            }
            if($UserShareMoney && $fuliquan_num){
                $this->user->fuliquan = 0;
                $order->return_integral =$UserShareMoney;
                if(!$this->user->save()){
                    $p->rollBack();
                    return [
                        'code'  => 1,
                        'msg'   => '申请失败',
                    ];
                }
            }else{
                return [
                    'code'  => 1,
                    'msg'   => '暂未查询到结算奖励',
                ];
            }

        }



        if ($order->save()) {
            $goods->sales ++;
            $goods->stock --;
            if($goods->stock < 0){
                $p->rollBack();
                return [
                    'code'  => 1,
                    'msg'   => '库存不够',
                ];
            }
            $goods->save();
            foreach ($this->form_list AS $key => $value)
            {
                if ($value['required'] ==1 && $value['default'] == ''){
                    return [
                        'code'    => 1,
                        'msg'     => $value['name'].'不能为空',
                    ];
                }
                if ($value['type']== 'radio' || $value['type']== 'checkbox'){
                    $default = [];
                    foreach ($value['default'] AS $k => $v){
                        if ($v['selected']==true){
                            $default[$k] = $v['name'];
                        }
                    }
                    $value['default'] = implode($default,',');
                    if ($value['required'] ==1 && empty($value['default'])){
                        return [
                            'code'    => 1,
                            'msg'     => $value['name'].'不能为空',
                        ];
                    }
                }

                $formList = new OrderForm();
                $formList->store_id = $this->store_id;
                $formList->goods_id = $goods->id;
                $formList->user_id  = $this->user_id;
                $formList->order_id = $order->id;
                $formList->key      = $value['name'];
                $formList->value    = $value['default'];
                $formList->is_delete= 0;
                $formList->addtime  = time();

                if (!$formList->save()){
                    $p->rollBack();
                    return [
                        'code'  => 1,
                        'msg'   => '申请提交失败，请稍后重试',
                    ];
                }
            }

            if ($order->pay_price <= 0){
                //暂时不做退款支付的操作 所有 奖励只有付钱和积分两种分开
                //扣除积分
                if ($goods->coupon > 0 || $goods->integral > 0){
                    $this->user->coupon = $this->user->coupon - $goods->coupon;
                    $this->user->integral  = $this->user->integral - $goods->integral;

                    if($this->user->integral >=0 && $this->user->coupon >=0)
                    {
                        $this->user->save();
                    }
                    elseif($this->user->integral <0){
                        $p->rollBack();
                        return [
                            'code'  => 1,
                            'msg'   => '积分不足',
                        ];
                    }elseif($this->user->coupon <0){
                        $p->rollBack();
                        return [
                            'code'  => 1,
                            'msg'   => '优惠券不足',
                        ];
                    }else{
                        $p->rollBack();
                        return [
                            'code'  => 2,
                            'msg'   => '网络问题,请稍后重试',
                        ];
                    }
                }


                //这里下单就是支付
                $order->coupon = $goods->coupon;
                $order->integral = $goods->integral;
                $order->is_pay = 1;
                $order->pay_type = 1;
                $order->pay_time = time();
                //记录日志
                $hld=0;
                $fuliquan=$fuliquan_num ;
                $coupon=$goods->coupon;
                $integral=$goods->integral;

                $integralLog = new IntegralLog();
                $integralLog->user_id = $this->user->id;
                //申请奖励 扣除积分
                $integralLog->content = "(申请奖励)：" . $this->user->nickname . " 欢乐豆".$this->user->hld."已经扣除：" . $hld . " 豆" . " 优惠券".$this->user->coupon."已经扣除：" . $coupon . " 张" . $integral . '个积分）扣除福利券份数'.$fuliquan_num;

                $integralLog->integral = $goods->integral;
                $integralLog->hld = $hld;
                $integralLog->coupon = $coupon;
                $integralLog->addtime = time();
                $integralLog->username = $this->user->nickname;
                $integralLog->operator = 'admin';
                $integralLog->store_id = $this->store_id;
                $integralLog->operator_id = 0;
                $integralLog->save();





                if ($order->save() && $UserShareMoney){
                    $wechat_tpl_meg_sender = new WechatTplMsgSender($order->store_id, $order->id, $this->wechat);
                    $wechat_tpl_meg_sender->payMsg();

                    $p->commit();
                    return [
                        'code'  => 0,
                        'msg'   => '订单提交成功',
                        'type'  => 1,
                    ];
                }else{
                    $p->rollBack();
                    return [
                        'code'  => 1,
                        'msg'   => '申请提交失败，请稍后重试',
                    ];
                }
            }
            $this->order = $order;
            $goods_names = mb_substr($goods->name, 0, 32, 'utf-8');
            $pay_data = [];
            $res = null;
            if ($this->pay_type == 'WECHAT_PAY') {
                $res = $this->unifiedOrder($goods_names);
                if (isset($res['code']) && $res['code'] == 1) {
                    return $res;
                }

                //记录prepay_id发送模板消息用到
//                YyFormId::addFormId([
//                    'store_id' => $this->store_id,
//                    'user_id' => $this->user->id,
//                    'wechat_open_id' => $this->user->wechat_open_id,
//                    'form_id' => $res['prepay_id'],
//                    'type' => 'prepay_id',
//                    'order_no' => $this->order->order_no,
//                ]);
                $order->form_id = $res['prepay_id'];
                $order->save();
                $pay_data = [
                    'appId' => $this->wechat->appId,
                    'timeStamp' => '' . time(),
                    'nonceStr' => md5(uniqid()),
                    'package' => 'prepay_id=' . $res['prepay_id'],
                    'signType' => 'MD5',
                ];
                $pay_data['paySign'] = $this->wechat->pay->makeSign($pay_data);
//                $this->setReturnData($this->order);
//                return [
//                    'code' => 0,
//                    'msg' => 'success',
//                    'data' => (object)$pay_data,
//                    'res' => $res,
//                    'body' => $goods_names,
//                ];
            }



            $p->commit();
            return [
                'code' => 0,
                'msg' => '订单提交成功',
                'data' => (object)$pay_data,
                'res' => $res,
                'body' => $goods_names,
                'type' => 2,
            ];

        }else{
            $p->rollBack();
            return $this->getModelError($order);
        }
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
            $order_no = 'CSR'.date('YmdHis') . rand(10000, 99999);
            $exist_order_no = Order::find()->where(['order_no' => $order_no])->exists();
            if (!$exist_order_no)
                break;
        }
        return $order_no;
    }

    /**
     * @param $goods_names
     * @return array
     * 统一下单
     */
    private function unifiedOrder($goods_names)
    {
        $res = $this->wechat->pay->unifiedOrder([
            'body' => $goods_names,
            'out_trade_no' => $this->order->order_no,
            'total_fee' => $this->order->pay_price * 100,
            'notify_url' => \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/paynotify/index',
            'trade_type' => 'JSAPI',
            'openid' => $this->user->wechat_open_id,
        ]);
        if (!$res)
            return [
                'code' => 1,
                'msg' => '支付失败',
            ];
        if ($res['return_code'] != 'SUCCESS') {
            return [
                'code' => 1,
                'msg' => '支付失败，' . (isset($res['return_msg']) ? $res['return_msg'] : ''),
                'res' => $res,
            ];
        }
        if ($res['result_code'] != 'SUCCESS') {
            if ($res['err_code'] == 'INVALID_REQUEST') {//商户订单号重复
                $this->order->order_no = $this->getOrderNo();
                $this->order->save();
                return $this->unifiedOrder($goods_names);
            } else {
                return [
                    'code' => 1,
                    'msg' => '支付失败，' . (isset($res['err_code_des']) ? $res['err_code_des'] : ''),
                    'res' => $res,
                ];
            }
        }
        return $res;
    }


    public function payData($id)
    {
        $this->wechat = $this->getWechat();
        $this->user = User::findOne(['id' => $this->user_id, 'type' => 1, 'is_delete' => 0]);
        $order = Order::find()
            ->andWhere([
                'is_delete' => 0,
                'store_id' => $this->store_id,
                'user_id' => $this->user_id,
                'is_cancel' => 0,
                'id' => $id,
                'is_pay'    => 0,
            ])->one();
        if (!$order){
            return [
                'code'  => 1,
                'msg'   => '订单不存在，或已支付',
            ];
        }

        $this->order = $order;


        $goods = Goods::findOne(['id'=>$order->goods_id]);

//        if (!$goods){
//            return [
//                'code'  => 1,
//                'msg'   => '订单不存在，或已支付',
//            ];
//        }

        $goods_names = mb_substr($goods->name, 0, 32, 'utf-8');
//        $pay_data = [];
//        $res = null;
        if ($this->pay_type == 'WECHAT_PAY') {
            $res = $this->unifiedOrder($goods_names);
            if (isset($res['code']) && $res['code'] == 1) {
                return $res;
            }

            //记录prepay_id发送模板消息用到
//            YyFormId::addFormId([
//                'store_id' => $this->store_id,
//                'user_id' => $this->user->id,
//                'wechat_open_id' => $this->user->wechat_open_id,
//                'form_id' => $res['prepay_id'],
//                'type' => 'prepay_id',
//                'order_no' => $this->order->order_no,
//            ]);
            $order->form_id = $res['prepay_id'];
            $order->save();

            $pay_data = [
                'appId' => $this->wechat->appId,
                'timeStamp' => '' . time(),
                'nonceStr' => md5(uniqid()),
                'package' => 'prepay_id=' . $res['prepay_id'],
                'signType' => 'MD5',
            ];
            $pay_data['paySign'] = $this->wechat->pay->makeSign($pay_data);
//                $this->setReturnData($this->order);
            return [
                'code' => 0,
                'msg' => 'success',
                'data' => (object)$pay_data,
                'res' => $res,
                'body' => $goods_names,
            ];
        }
    }

}