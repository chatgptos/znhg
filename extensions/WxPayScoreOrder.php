<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/31
 * Time: 14:54
 */

namespace app\extensions;


use app\models\Store;
use app\modules\api\controllers\Controller;
use GuzzleHttp\HandlerStack;
use WechatPay\GuzzleMiddleware\Util\PemUtil;
use WechatPay\GuzzleMiddleware\WechatPayMiddleware;

class WxPayScoreOrder extends Controller
{
    public $key = 'p6GHYMDpkj2BiiIzQMA6RLzgbEYoJkcm';
    public $api = 'https://api.mch.weixin.qq.com';

    public $mch_id = '1555897421';

    public $service_id = '00004000000000158195309791355586';

    public $sign_type = 'HMAC-SHA256';


    public $client;


    public $starttime;
    public $endtime;


    function __construct()
    {
        parent::init();

        $this->starttime = date('YmdHis', time() + 10);;
        $this->endtime = date('YmdHis', time() + 81);;
        $merchantId = $this->wechat->mchId;
        $merchantSerialNumber = '6B7B443DD20BAFEEF9B1583A456FE84F708F8E58';//证书序列号
        $merchantPrivateKey = PemUtil::loadPrivateKey($this->wechat->keyPem);
        $wechatpayCertificate = PemUtil::loadCertificate($this->wechat->certPem);

        $wechatpayMiddleware = WechatPayMiddleware::builder()
            ->withMerchant($merchantId, $merchantSerialNumber, $merchantPrivateKey)
            ->withWechatPay([$wechatpayCertificate])// 可传入多个微信支付平台证书，参数类型为array
            ->build();

        $stack = HandlerStack::create();
        $stack->push($wechatpayMiddleware, 'wechatpay');
        $this->client = new \GuzzleHttp\Client([
            'handler' => $stack,
            'base_uri' => 'https://api.mch.weixin.qq.com',
            'http_errors' => false,//#设置成 false 来禁用HTTP协议抛出的异常(如 4xx 和 5xx 响应)，默认情况下HTPP协议出错时会抛出异常。
        ]);
    }


////        //查询订单
    public function queryOrder($out_order_no)
    {
        $url = '/v3/payscore/serviceorder';
        $res = $this->client->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'query' => [
                    'appid' => $this->wechat->appId,
                    'service_id' => $this->service_id,
                    'out_order_no' => $out_order_no
                ],
            ]
        );

        return $res->getBody()->getContents();

    }

//        //取消订单
    public function cancel($out_order_no,$reason='不想买了')
    {
        $url = '/v3/payscore/serviceorder/' . $out_order_no . '/cancel';
        $res = $this->client->request('POST', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode((array)[
                    'appid'=>$this->wechat_app->app_id,
                    'service_id'=>'00004000000000158195309791355586',
                    'reason'=>$reason,
                ]),
            ]
        );

        return $res->getBody()->getContents();

    }

    //创建订单
    public function serviceorder($out_order_no,$goods='',$address='')
    {
        if($goods){
            $goods_name = $goods['goods_name'];
            $pay_price = $goods['pay_price'];
            $total_price = $goods['total_price'];
            $original_price = $goods['original_price'];
            $integral = $goods['integral'];
            $coupon = $goods['coupon'];
            $num = $goods['num'];
            $goods_list = $goods['goods_list'];
            $address = $goods['address'];
        }
        $url = '/v3/payscore/serviceorder';
        $res = $this->client->request('POST', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode([
                    'out_order_no' => $out_order_no,
                    'appid' => $this->wechat->appId,
                    'service_id' =>  $this->service_id,
                    'service_introduction' => '智慧零售',
//                    'service_introduction' => '购值爽券池独享柜'.$goods_name,
//                    'post_payments' => [[
//                        'name' => '券池独享福利:'.$goods_name,
//                        'amount' =>intval( $pay_price*100),
//                        'description' => '只属于你的:'.$goods_name,
//                        'count' => $num,
//                    ]],
//                    "location" => [
//                        'start_location' => "购值爽券池独享柜，开门一切就是你的-AI BEE",
//                        'end_location' => "购值爽券池,更多的券,更多福利,独享属于你的世界-INCHINA,有券还有现金抢哦",
//                    ],
                    "notify_url" => "https://app.aijiehun.com/paynotify/wechatscorepay",
                    'time_range' => [
                        'start_time' => $this->starttime,
                        'end_time' => $this->endtime,
                    ],
                    'attach' => $out_order_no,
//                    'post_payments' => $goods_list,
                    'risk_fund' => [
                        'name' => "ESTIMATE_ORDER_COST",
                        'amount' => 10000,
//                        'description' => $goods_name,
                    ],
//                    'post_discounts' => [[
//                        'name' => "购值爽券池独享优惠",
//                        'amount' => intval($total_price-$pay_price)*100,
//                        'description' => "券池独享柜，您的独享，进入小程序券池子抢券得到更多",
//                    ]],
                    "location" => [
                        'start_location' => $address,
                        'start_location' => $address,
                    ],
                    'openid' => \Yii::$app->user->identity->wechat_open_id,
                    'need_user_confirm' => false,//提示服务无权限 是因为这个参数
                ]),
            ]
        );
        return $res->getBody()->getContents();
    }

//        //完结订单
    public function complete($out_order_no ,$goods='')
    {
        $address='';
        if($goods){
            $goods_name = $goods['goods_name'];
            $pay_price = $goods['pay_price'];
            $total_price = $goods['total_price'];
            $original_price = $goods['original_price'];
            $integral = $goods['integral'];
            $coupon = $goods['coupon'];
            $num = $goods['num'];
            $goods_list = $goods['goods_list'];
            $address = $goods['address'];
        }
        $url = '/v3/payscore/serviceorder/' . $out_order_no . '/complete';
        $res = $this->client->request('POST', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode((array)[
                    'appid'=>$this->wechat_app->app_id,
                    'service_id'=>'00004000000000158195309791355586',
                    'service_introduction'=>'智慧零售',
//                    'post_payments' => [[
//                        'name' => '券池独享福利:'.$goods_name,
//                        'amount' =>intval( $pay_price*100),
//                        'description' => '只属于你的:'.$goods_name,
//                        'count' => $num,
//                    ]],
                    'post_payments' => $goods_list,
                    "total_amount"=> intval( $pay_price*100),
                    'risk_fund' => [
                        'name' => "ESTIMATE_ORDER_COST",
                        'amount' => 10000,
                        'description' => $goods_name,
                    ],
                    'post_discounts' => [[
                        'name' => "购值爽券池独享优惠",
                        'amount' => intval($total_price-$pay_price)*100,
                        'description' => "券池独享柜,进入小程序券池子抢券,得更多,有券还有现金",
                    ]],
                    "location" => [
                        'start_location' => $address,
                        'start_location' => $address,
                    ],
//                    "location" => [
//                        'start_location' => "购值爽券池独享柜，开门一切就是你的-AI BEE",
//                        'end_location' => "购值爽券池,更多的券,更多福利,独享属于你的世界-INCHINA,有券还有现金抢哦",
//                    ],
                    'profit_sharing'=>false,
                ]),
            ]
        );
        return $res->getBody()->getContents();

    }

//        //收款订单
    public function pay($out_order_no)
    {
        $url = '/v3/payscore/serviceorder/' . $out_order_no . '/pay';
        $res = $this->client->request('POST', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'body' => [
                    'appid' => $this->wechat->appId,
                    'service_id' => $this->service_id,
                ],
            ]
        );
        return $res->getBody()->getContents();
    }

    public function modify($out_order_no)
    {
//        //修改订单
        $url = '/v3/payscore/serviceorder/' . $out_order_no . '/modify';
        $res = $this->client->request('POST', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode((array)[
                    'appid' => $this->wechat->appId,
                    'service_id' =>  $this->service_id,
                    'service_introduction' => '货柜可乐',
                    "notify_url" => "https://app.aijiehun.com/paynotify/wechatscorepay",
                    'time_range' => [
                        'start_time' => "20200305123510",
                        'end_time' => $this->endtime,
                    ],
                    'risk_fund' => [
                        'name' => "ESTIMATE_ORDER_COST",
                        'amount' => 10000,
                        'description' => "可乐的预估费用",
                    ],
                    'post_payments' => [[
                        'name' => "可乐",
                        'amount' => 100,
                        'description' => "可乐的预估费用",
                        'count' => 2,
                    ]],
                    'post_discounts' => [[
                        'name' => "满2减1元",
                        'amount' => 1,
                        'description' => "不与其他优惠叠加",
                    ]],
                    "location" => [
                        'start_location' => "深圳货柜",
                        'end_location' => "深圳货柜",
                    ],
                    'openid' => \Yii::$app->user->identity->wechat_open_id,
                    'need_user_confirm' => false,//提示服务无权限 是因为这个参数
                ]),
            ]
        );

        return $res->getBody()->getContents();
    }

    //查询授权
    public function userServiceState($openid='ogZOL5XwAe-PVnKuJnAAiSXP5fA0')
    {
        $url = '/v3/payscore/user-service-state';
        $res = $this->client->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'query' => [
                    'appid' => $this->wechat->appId,
                    'service_id' =>  $this->service_id,
                    'openid' => $openid,
                ],
            ]
        );

//
        return $res->getBody()->getContents();
    }




    public function wxpayScoreEnable($out_request_no)
    {
        $pay_data = array(
            'timestamp' => '1583037425',
            'mch_id' => $this->wechat->mchId,
            'service_id' => $this->service_id,
            'out_request_no' => $out_request_no,
            'nonce_str' => $this->getNonce(),
            'sign_type' => "HMAC-SHA256",
        );
        $pay_data['sign'] = $this->makeSign($pay_data, $this->wechat->apiKey);
        return $pay_data;
    }


    public function wxpayScoreDetail($out_order_no)
    {
        $pay_data = array(
            'timestamp' => '1583037425',
            'mch_id' => $this->wechat->mchId,
            'service_id' => $this->service_id,
            'out_order_no' => $out_order_no,
            'nonce_str' => $this->getNonce(),
            'sign_type' => "HMAC-SHA256",
        );
        $pay_data['sign'] = $this->makeSign($pay_data,$this->wechat->apiKey);
        return $pay_data;
    }





    /**
     * @return null|string
     * 生成订单号
     */
    public function getOrderNoWxQY()
    {
        $order_no = null;
        $order_no = 'WxScoreQY'.date('YmdHis') . rand(10000, 99999);
//        while (true) {
//        $store_id = empty($this->store_id) ? 0 : $this->store_id;
//            $order_no = 'Y'.date('YmdHis') . rand(10000, 99999);
////            //$exist_order_no = YyOrder::find()->where(['order_no' => $order_no])->exists();
////            if (!$exist_order_no)
////                break;
//        }
        return $order_no;
    }


    /**
     * @return null|string
     * 生成签约号
     */
    public function getOrderNoWx()
    {
        $order_no = null;
        $order_no = 'WxScore'.date('YmdHis') . rand(10000, 99999);
        return $order_no;
    }

    /**
     * Json方式 调用电子面单接口
     *
     */
    public static function post($url, $data)
    {
        $headerArray = array("Content-type:application/json;charset='utf-8'", "Accept:application/json");
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headerArray);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }


    /**
     *  post提交数据
     * @param  string $url 请求Url
     * @param  array $datas 提交的数据
     * @return url响应返回的html
     */
    public function sendPost($url, $datas)
    {
        $temps = array();
        foreach ($datas as $key => $value) {
            $temps[] = sprintf('%s=%s', $key, $value);
        }
        $post_data = implode('&', $temps);
        $url_info = parse_url($url);
        if (empty($url_info['port'])) {
            $url_info['port'] = 80;
        }
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader .= "Host:" . $url_info['host'] . "\r\n";
        $httpheader .= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader .= "Content-Length:" . strlen($post_data) . "\r\n";
        $httpheader .= "Connection:close\r\n\r\n";
        $httpheader .= $post_data;
        $fd = fsockopen($url_info['host'], $url_info['port']);
        fwrite($fd, $httpheader);
        $gets = "";
        $headerFlag = true;
        while (!feof($fd)) {
            if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
                break;
            }
        }
        while (!feof($fd)) {
            $gets .= fread($fd, 128);
        }
        fclose($fd);

        return $gets;
    }

    /**
     * 电商Sign签名生成
     * @param data 内容
     * @param appkey Appkey
     * @return DataSign签名
     */
    function encrypt($data, $appkey)
    {
        return urlencode(base64_encode(md5($data . $appkey)));
    }

    /**************************************************************
     *
     *  将数组转换为JSON字符串（兼容中文）
     * @param  array $array 要转换的数组
     * @return string      转换得到的json字符串
     * @access public
     *
     *************************************************************/
    function JSON($array)
    {
        self::arrayRecursive($array, 'urlencode', true);
        $json = json_encode($array);
        return urldecode($json);
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
        $string = $string . "&key=" . $this->key;
        $string = md5($string);
        $result = strtoupper($string);
        return $result;
    }


    /**
     * MD5签名
     */
    public function makeSign($args, $merchantPrivateKey = 'qwertyuiopyang111111111111111111')
    {
        if (isset($args['sign']))
            unset($args['sign']);
        ksort($args);
        foreach ($args as $i => $arg) {
            if ($args === null || $arg === '')
                unset($args[$i]);
        }
        $string = $this->arrayToUrlParam($args, false);
        $string = $string . "&key=" . $merchantPrivateKey;
        $raw_sign = hash_hmac('sha256', $string, $merchantPrivateKey);
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
            } else {
                $url_param .= $key . "=" . ($url_encode ? urlencode($value) : $value) . "&";
            }
        }
        return trim($url_param, "&");
    }

}