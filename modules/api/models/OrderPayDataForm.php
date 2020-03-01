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
use GuzzleHttp\HandlerStack;
use WechatPay\GuzzleMiddleware\Util\PemUtil;
use WechatPay\GuzzleMiddleware\WechatPayMiddleware;
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

            $pay_data = [
                'appId' => $this->wechat->appId,
                'timeStamp' => '' . time(),
                'nonceStr' => md5(uniqid()),
                'package' => 'prepay_id=' . $res['prepay_id'],
                'signType' => 'MD5',
            ];
            $pay_data['paySign'] = $this->wechat->pay->makeSign($pay_data);
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
        $pay_data['sign'] = $this->makeSignHG($pay_data['biz_content']);

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



    public function search2()
    {
        $this->wechat = $this->getWechat();
//// 商户配置
        $merchantId = $this->wechat->mchId;
        $merchantSerialNumber = '6B7B443DD20BAFEEF9B1583A456FE84F708F8E58';//证书序列号
//        $merchantPrivateKey = PemUtil::loadPrivateKey('/path/to/mch/private/key.pem');
//        $wechatpayCertificate = PemUtil::loadCertificate('/path/to/wechatpay/cert.pem');

        $merchantPrivateKey = PemUtil::loadPrivateKey($this->wechat->keyPem);
        $wechatpayCertificate = PemUtil::loadCertificate($this->wechat->certPem);
//
////
//////// 构造一个WechatPayMiddleware
        $wechatpayMiddleware = WechatPayMiddleware::builder()
            ->withMerchant($merchantId, $merchantSerialNumber, $merchantPrivateKey)
            ->withWechatPay([ $wechatpayCertificate ]) // 可传入多个微信支付平台证书，参数类型为array
            ->build();

//
//////
//////// 将WechatPayMiddleware添加到Guzzle的HandlerStack中
        $stack = HandlerStack::create();
        $stack->push($wechatpayMiddleware, 'wechatpay');
//
////
//// 创建Guzzle HTTP Client时，将HandlerStack传入
        $client =  new \GuzzleHttp\Client([
            'handler' => $stack,
            'base_uri' => 'https://api.mch.weixin.qq.com',
            'http_errors' => false,//#设置成 false 来禁用HTTP协议抛出的异常(如 4xx 和 5xx 响应)，默认情况下HTPP协议出错时会抛出异常。
        ]);

        $starttime=date('YmdHis', time()+10);;
        $endtime=date('YmdHis', time()+61);;
//
        $out_order_no="234323JKHDFE1243252B";
////        //查询订单
        $url =  '/v3/payscore/serviceorder';
        $res = $client->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json' ,
                    'Content-Type' => 'application/json' ,
                ],
                'query' => [
                    'appid'=>$this->wechat->appId,
                    'service_id'=>'00004000000000158195309791355586',
                    'out_order_no'=>$out_order_no
                ],
            ]
        );
////
////
        echo '<pre>查询订单';
        var_dump(json_decode($res->getBody()->getContents(), true));
//        var_dump($res);
//        die;

//        //取消订单
        $url =   '/v3/payscore/serviceorder/'.$out_order_no.'/cancel';
        $res = $client->request('POST', $url, [
                'headers' => [
                    'Accept' => 'application/json' ,
                    'Content-Type' => 'application/json' ,
                ],
                'query' => [
                    'appid'=>$this->wechat->appId,
                    'service_id'=>'00004000000000158195309791355586',
                    'reason'=>'不想买了',
                ],
            ]
        );
////
////
        echo '<pre>取消订单';
        var_dump(json_decode($res->getBody()->getContents(), true));
//        var_dump($res);
        die;


//        //完结订单
//        $url =  '/v3/payscore/serviceorder/'.$out_order_no.'/complete';
//
//        $res = $client->request('POST', $url, [
//                'headers' => [
//                    'Accept' => 'application/json' ,
//                    'Content-Type' => 'application/json' ,
//                ],
//                'query' => [
//                    'appid'=>$this->wechat->appId,
//                    'service_id'=>'00004000000000158195309791355586',
//                    'service_introduction'=>'货柜可乐',
//                    "notify_url"=> "https://app.aijiehun.com/paynotify/wechatscorepay",
//                    'risk_fund'=>json_encode([
//                        'name'=>"ESTIMATE_ORDER_COST",
//                        'amount'=>10000,
//                        'description'=>"可乐的预估费用",
//                    ]),
//                    'post_payments'=>json_encode([
//                        'name'=>"可乐",
//                        'amount'=>1,
//                        'description'=>"可乐的预估费用",
//                        'count'=>2,
//                    ]),
////                    'post_discounts'=>json_encode([
////                         'name'=>"满2减1元",
////                        'amount'=>1,
////                        'description'=>"不与其他优惠叠加",
////                    ]),
//                    "location"=>json_encode([
//                        'start_location'=>"深圳货柜",
//                        'end_location'=>"深圳货柜",
//                    ]),
//                    "total_amount"=> 1,
//                    'profit_sharing'=>false,
//
//                ],
//            ]
//        );
//
//
//        echo '<pre>完结订单';
//        var_dump(json_decode($res->getBody()->getContents(), true));
//        die;



//        //收款订单
//        $url =  '/v3/payscore/serviceorder/'.$out_order_no.'/pay';
//
//        $res = $client->request('POST', $url, [
//                'headers' => [
//                    'Accept' => 'application/json' ,
//                    'Content-Type' => 'application/json' ,
//                ],
//                'query' => [
//                    'appid'=>$this->wechat->appId,
//                    'service_id'=>'00004000000000158195309791355586',
//                ],
//            ]
//        );
//
//
//        echo '<pre>收款订单';
//
//        var_dump($url);
////        die;
//        var_dump(json_decode($res->getBody()->getContents(), true));
//        die;



//        //修改订单
//        $url =  '/v3/payscore/serviceorder/'.$out_order_no.'/modify';
//
//        $res = $client->request('POST', $url, [
//                'headers' => [
//                    'Accept' => 'application/json' ,
//                    'Content-Type' => 'application/json' ,
//                ],
//                'query' => [
//                    'appid'=>$this->wechat->appId,
//                    'service_id'=>'00004000000000158195309791355586',
//                    'service_introduction'=>'货柜可乐',
//                    "notify_url"=> "https://app.aijiehun.com/paynotify/wechatscorepay",
//                    'risk_fund'=>json_encode([
//                        'name'=>"ESTIMATE_ORDER_COST",
//                        'amount'=>10000,
//                        'description'=>"可乐的预估费用",
//                    ]),
//                    'post_payments'=>json_encode([
//                        'name'=>"可乐",
//                        'amount'=>100,
//                        'description'=>"可乐的预估费用",
//                        'count'=>1,
//                    ]),
////                    'post_discounts'=>json_encode([
////                         'name'=>"满2减1元",
////                        'amount'=>1,
////                        'description'=>"不与其他优惠叠加",
////                    ]),
//                    "location"=>json_encode([
//                        'start_location'=>"深圳货柜",
//                        'end_location'=>"深圳货柜",
//                    ]),
//                    "total_amount"=> 100,
//                    'profit_sharing'=>false,
//                    'reason'=>'买多了',
//                ],
//            ]
//        );
//
//
//        echo '<pre>修改订单';
//        var_dump(json_decode($res->getBody()->getContents(), true));
//        die;


        //查询授权
//
//        $url =  '/v3/payscore/user-service-state';
//        $res = $client->request('GET', $url, [
//                'headers' => [
//                    'Accept' => 'application/json' ,
//                    'Content-Type' => 'application/json' ,
//                ],
//                'query' => [
//                    'appid'=>$this->wechat->appId,
//                    'service_id'=>'00004000000000158195309791355586',
//                    'openid'=>'ogZOL5XwAe-PVnKuJnAAiSXP5fA0',
//                ],
//            ]
//        );
//
//
//        echo '<pre>';
////        var_dump($client);
////        var_dump($this->wechat->appId);
//        var_dump(json_decode($res->getBody()->getContents(), true));
//        die;
//        die;


        //创建订单
//        $url =  '/v3/payscore/serviceorder';
//        $res = $client->request('POST', $url, [
//            'headers' => [
//                'Accept' => 'application/json' ,
//                'Content-Type' => 'application/json' ,
//            ],
//                'query' => [
//                    'out_order_no'=>$out_order_no,
//                    'appid'=>$this->wechat->appId,
//                    'service_id'=>'00004000000000158195309791355586',
//                    'service_introduction'=>'货柜可乐',
//                    "notify_url"=> "https://app.aijiehun.com/paynotify/wechatscorepay",
//                    'time_range'=>json_encode([
//                        'start_time'=>$starttime,
//                        'end_time'=>$endtime,
//                    ]),
//                    'risk_fund'=>json_encode([
//                        'name'=>"ESTIMATE_ORDER_COST",
//                        'amount'=>10000,
//                        'description'=>"可乐的预估费用",
//                    ]),
//                    'openid'=>'ogZOL5XwAe-PVnKuJnAAiSXP5fA0',
//                    'post_payments'=>json_encode([
//                        'name'=>"可乐",
//                        'amount'=>100,
//                        'description'=>"可乐的预估费用",
//                        'count'=>2,
//                    ]),
//                    'post_discounts'=>json_encode([
//                        'name'=>"满2减1元",
//                        'amount'=>1,
//                        'description'=>"不与其他优惠叠加",
//                    ]),
//                    "location"=>json_encode([
//                        'start_location'=>"深圳货柜",
//                        'end_location'=>"深圳货柜",
//                    ]),
//                    'openid'=>'ogZOL5XwAe-PVnKuJnAAiSXP5fA0',
//                    'need_user_confirm'=>false,//提示服务无权限 是因为这个参数
//                ],
//             ]
//        );
//        //返回状态码
//        echo '<pre>创建订单';
//        var_dump(json_decode($res->getBody()->getContents(), true));
//        die;
    }




    public function search3()
    {
        $this->wechat = $this->getWechat();
        $out_order_no="234323JKHDFE1243252B";
        $pay_data = array(
            'mch_id' => "1555897421",
            'service_id' => "00004000000000158195309791355586",
            'out_order_no' => $out_order_no,
            'timestamp' => '1583037425',
            'nonce_str' => $this->getNonce(),
            'sign_type' => "HMAC-SHA256",
        );

        $pay_data = array(
            'mch_id' => "1555897421",
            'service_id' => "00004000000000158195309791355586",
            'out_request_no' => $out_order_no,
            'timestamp' => '1583037425',
            'nonce_str' => $this->getNonce(),
            'sign_type' => "HMAC-SHA256",
        );
        $pay_data['sign'] = $this->makeSign($pay_data,$this->wechat->apiKey);
        return([
            'code' => 0,
            'msg' => 'success',
            'data' => $pay_data,
        ]);
    }


    protected function getNonce()
    {
        static $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 32; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * MD5签名
     */
    public function makeSignHG($args)
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
        return $result;
    }




    /**
     * MD5签名
     */
    public function makeSign($args,$merchantPrivateKey='qwertyuiopyang111111111111111111')
    {
        if (isset($args['sign']))
            unset($args['sign']);
        ksort($args);
        foreach ($args as $i => $arg) {
            if ($args === null || $arg === '')
                unset($args[$i]);
        }
        $string = $this->arrayToUrlParam($args, false);
        $string = $string . "&key=".$merchantPrivateKey;
        $raw_sign =  hash_hmac('sha256', $string, $merchantPrivateKey);
        $result = strtoupper($raw_sign);
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