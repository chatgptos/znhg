<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/7/18
 * Time: 12:11
 */

namespace app\modules\api\models;


use app\models\FormId;
use app\models\Goods;
use app\models\Order;
use app\models\OrderDetail;
use app\models\Setting;
use app\models\User;
use yii\helpers\VarDumper;

/**
 * @property User $user
 * @property Order $order
 */
class OrderPayDataForm extends Model
{
    public $store_id;
    public $order_id;
    public $pay_type;
    public $user;

    private $wechat;
    private $order;

    public function rules()
    {
        return [
            [['order_id', 'pay_type',], 'required'],
            [['pay_type'], 'in', 'range' => ['ALIPAY', 'WECHAT_PAY']],
        ];
    }

    public function search()
    {
        $this->wechat = $this->getWechat();
        if (!$this->validate())
            return $this->getModelError();
        $this->order = Order::findOne([
            'store_id' => $this->store_id,
            'id' => $this->order_id,
        ]);
        if (!$this->order)
            return [
                'code' => 1,
                'msg' => '订单不存在',
            ];

        $goods_names = '';
        $goods_list = OrderDetail::find()->alias('od')->leftJoin(['g' => Goods::tableName()], 'g.id=od.goods_id')->where([
            'od.order_id' => $this->order->id,
            'od.is_delete' => 0,
        ])->select('g.name')->asArray()->all();
        foreach ($goods_list as $goods)
            $goods_names .= $goods['name'] . ';';
        $goods_names = mb_substr($goods_names, 0, 32, 'utf-8');
        if ($this->pay_type == 'WECHAT_PAY') {
            $res = $this->unifiedOrder($goods_names);
            if (isset($res['code']) && $res['code'] == 1) {
                return $res;
            }

            //记录prepay_id发送模板消息用到
            FormId::addFormId([
                'store_id' => $this->store_id,
                'user_id' => $this->user->id,
                'wechat_open_id' => $this->user->wechat_open_id,
                'form_id' => $res['prepay_id'],
                'type' => 'prepay_id',
                'order_no' => $this->order->order_no,
            ]);

//            $pay_data = [
//                'appId' => $this->wechat->appId,
//                'timeStamp' => '' . time(),
//                'nonceStr' => md5(uniqid()),
//                'package' => 'prepay_id=' . $res['prepay_id'],
//                'signType' => 'MD5',
//            ];



//            {
//                            "appId":"201923265",
//                "timestamp":"2014-07-24 03:07:50",
//                "sign":"5666666",
//                "biz_content":{
//                            "deviceId":10000,
//                }
//            }




//            $pay_data = [
//                'appid' => "wxd930ea5d5a258f4f",
//                'device_info' => "1000",
//                'body' => "test",
//                'nonce_str' => "ibuaiVcKdpRxkhJA",
//                'mch_id' => "10000100",
//            ];
//     {
//                "appId":"2019082351351",
//    "timestamp":"2014-07-24 03:07:50",
//    "sign":"DE448FA75DAB07D141343D590BBE679D",
//    "biz_content":{
//                "nickName":"1353817847",
//        "phone":"1353817847",
//        "unionid":"test",
//        "gender":"1",
//        "avatar":"httt://demo.png"
//    }
//}
//            var_dump($pay_data);


//            $pay_data = array(
//                'appId' => "2019082351351",
//                "timestamp"=>"2014-07-24 03:07:50",
//                'biz_content' => array(
////                    "deviceId"=>10000,
//                    "nickName"=>"1353817842",
//                    "phone"=>"1353817842",
//                    "unionid"=>"1353817842",
//                    "gender"=>"1",
//                    "avatar"=>"httt://demo.png"
//                )
//
//            );

            $pay_data = array(
                'appId' => "2019082351351",
                "timestamp"=>"2014-07-24 03:07:50",
                'biz_content' => array(
                    "deviceId"=>100023,//必须要有设备
                    "unionid"=>"1353817842",
                    "opendoorRecordId"=>"9516",
                )

            );
            $pay_data['sign'] = $this->makeSign($pay_data['biz_content']);

//            var_dump($this->wechat);
//            var_dump(json_encode($pay_data));
//            die;

            $url="https://api.voidiot.com/open-api/syncUserInfo";//同步用户数据可以开门
            $url="https://api.voidiot.com/open-api/getDeviceList";//获取货柜列表
            $url="https://api.voidiot.com/open-api/getDeviceById";//获取货柜详情
            $url="https://api.voidiot.com/open-api/completeOrder";//完结订单传入开门id记录
            $url="https://api.voidiot.com/open-api/openDoor";//取货开门
            $url="https://api.voidiot.com/open-api/getSelectGoods";//实时购买商品数据 判断门是否关闭
            $url="https://api.voidiot.com/open-api/getOrdersByOpenDoorId";//获取货柜商品详情
//            $url="https://api.voidiot.com/open-api/getDeviceGoods";//获取货柜商品详情
//            $url="https://api.voidiot.com/open-api/replenish";//补货开门
//            $url="https://api.voidiot.com/open-api/getDeviceRealTimeGoods";//获取补货实时货品











            $pay_data['biz_content'] = json_encode($pay_data['biz_content'],true);
            $data  = json_encode($pay_data,true);

            $headerArray =array("Content-type:application/json;charset='utf-8'","Accept:application/json");
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            curl_setopt($curl,CURLOPT_HTTPHEADER,$headerArray);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($curl);
            curl_close($curl);

            echo '<pre>';
            var_dump($output);
            var_dump(json_decode($output,true));
            die;






//            var_dump($pay_data['sign']);
//            die;
            $this->setReturnData($this->order);
            return [
                'code' => 0,
                'msg' => 'success',
                'data' => (object)$pay_data,
                'res' => $res,
                'body' => $goods_names,
            ];
        }
    }



    public function search1()
    {

        $pay_data = array(
            'appId' => "2019082351351",
            "timestamp"=>"2014-07-24 03:07:50",
            'biz_content' => array(
                "deviceId"=>100023,//必须要有设备
                "unionid"=>"1353817842",
                "opendoorRecordId"=>"9516",
            )

        );
        $pay_data['sign'] = $this->makeSign($pay_data['biz_content']);

        $url="https://api.voidiot.com/open-api/syncUserInfo";//同步用户数据可以开门
        $url="https://api.voidiot.com/open-api/getDeviceList";//获取货柜列表
        $url="https://api.voidiot.com/open-api/getDeviceById";//获取货柜详情
        $url="https://api.voidiot.com/open-api/completeOrder";//完结订单传入开门id记录
        $url="https://api.voidiot.com/open-api/openDoor";//取货开门
        $url="https://api.voidiot.com/open-api/getSelectGoods";//实时购买商品数据 判断门是否关闭
        $url="https://api.voidiot.com/open-api/getOrdersByOpenDoorId";//获取货柜商品详情
//            $url="https://api.voidiot.com/open-api/getDeviceGoods";//获取货柜商品详情
//            $url="https://api.voidiot.com/open-api/replenish";//补货开门
//            $url="https://api.voidiot.com/open-api/getDeviceRealTimeGoods";//获取补货实时货品

        $pay_data['biz_content'] = json_encode($pay_data['biz_content'],true);
        $data  = json_encode($pay_data,true);

        $headerArray =array("Content-type:application/json;charset='utf-8'","Accept:application/json");
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl,CURLOPT_HTTPHEADER,$headerArray);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);

        echo '<pre>';
        var_dump($output);
        var_dump(json_decode($output,true));
        die;
    }

    /**
     * MD5签名
     */
    public function makeSign($args)
    {
        if (isset($args['sign']))
            unset($args['sign']);
        ksort($args);
        foreach ($args as $i => $arg) {
            if ($args === null || $arg === '')
                unset($args[$i]);
        }
        $string = $this->arrayToUrlParam($args, false);
        $string = $string . "&key=DE448FA75DAB07D141343D590BBE679D";
        $string = md5($string);
        $result = strtoupper($string);
//        $string = $string . "&key=192006250b4c09247ec02edce69f6a2d";
        //string(227) "appId=2019082351351&biz_content[nickName]=1353817847&
        //biz_content[phone]=1353817847&biz_content[unionid]=test&
        //biz_content[gender]=1&biz_content[avatar]=demo.png&×tamp=2014-07-24 03:07:50&key=DE448FA75DAB07D141343D590BBE679D"
//        var_dump($string);
//        die;
//        var_dump($string);
//        $string1='gender=1&nickName=1353817847&phone=1353817847&unionid=test&key=DE448FA75DAB07D141343D590BBE679D';
//        $string1='deviceId=10000&key=DE448FA75DAB07D141343D590BBE679D';
//       var_dump($string1);
//       die;

//        $string1=$string;
//        $string='timestamp=2019082351351&appId=2019082351351&key=DE448FA75DAB07D141343D590BBE679D';

//
//        var_dump($string);
//
//        var_dump(md5($string));
//        var_dump( md5($string1));



//        var_dump($result);
//        die;
        return $result;
    }



    public static function arrayToUrlParam($array, $url_encode = true)
    {
        $url_param = "";
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $list_url_param = "";
                foreach ($value as $list_key => $list_value) {
                    if (!is_array($list_value))
                        $url_param .= $key . "[" . $list_key . "]=" . ($url_encode ? urlencode($list_value) : $list_value) . "&";
                }
                $url_param .= trim($list_url_param, "&") . "&";
            }else {
                $url_param .= $key . "=" . ($url_encode ? urlencode($value) : $value) . "&";
            }
        }
        return trim($url_param, "&");
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
                $this->order->order_no = (new OrderSubmitForm())->getOrderNo();
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
}