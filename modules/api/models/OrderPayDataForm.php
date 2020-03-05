<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/7/18
 * Time: 12:11
 */

namespace app\modules\api\models;


use app\extensions\HuoGui;
use app\extensions\WxPayScore;
use app\extensions\WxPayScoreOrder;
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
       $biz_content=array(
        "deviceId"=>100073,//必须要有设备
        "unionid"=>"1353817842",
       );
        $HuoGui = new HuoGui();
        echo '<pre>';


        $res= $HuoGui->replenish($biz_content);//补货开门
        var_dump($res);
        $res= $HuoGui->syncUserInfo($biz_content);
        var_dump($res);
        $res= $HuoGui->getDeviceList($biz_content);
        var_dump($res);
        $res= $HuoGui->getDeviceById($biz_content);
        var_dump($res);

        $res= $HuoGui->completeOrder($biz_content);
        var_dump($res);

//        echo '<hr>openDoor';
        $biz_content=array(
            "deviceId"=>100073,//必须要有设备
            "unionid"=>"1353817842",
        "opendoorRecordId"=>"9516",
        );

        $res= $HuoGui->openDoor($biz_content);
        var_dump($res);
        $res= $HuoGui->getSelectGoods($biz_content);
        var_dump($res);
//
        $res= $HuoGui->getOrdersByOpenDoorId($biz_content);
        var_dump($res);
//
        $res= $HuoGui->getDeviceGoods($biz_content);
        var_dump($res);
        $res= $HuoGui->getDeviceRealTimeGoods($biz_content);
        var_dump($res);
    }



    public function search2()
    {
//        $HuoGui = new WxPayScoreOrder();
//        echo '<pre><code>';
//        $out_order_no="123killaProgramerForSkytoo";
//        $res= $HuoGui->queryOrder($out_order_no);//查询
//        echo "<br/>queryOrder";
//        var_dump(json_decode($res, true));
//        $res= $HuoGui->serviceorder($out_order_no);//创建
//        echo "<br/>serviceorder";
//        var_dump(json_decode($res, true));
//        $res= $HuoGui->cancel($out_order_no);//取消
//        echo "<br/>cancel";
//        var_dump($res);
//        $res= $HuoGui->complete($out_order_no);//完结付钱
//        echo "<br/>complete";
//        var_dump($res);
//        $res= $HuoGui->userServiceState($this->user->wechat_open_id);//授权
//        echo "<br/>userServiceState";
//        var_dump($res);
//        $res= $HuoGui->modify($out_order_no);//修改
//        echo "<br/>modify";
//        var_dump($res);
//        $res= $HuoGui->pay($out_order_no);//支付
//        echo "<br/>pay";
//        var_dump($res);
//        $res= $HuoGui->wxpayScoreEnable($out_order_no);//授权
//        echo "<br/>wxpayScoreEnable";
//        var_dump($res);
//        $res= $HuoGui->wxpayScoreDetail($out_order_no);//订单详情
//        echo "<br/>wxpayScoreDetail";
//        var_dump($res);
    }




    public function search3()
    {
        $HuoGui = new WxPayScore();
        echo '<pre>';
        $out_order_no="234323JKHDFE1243252B";
        $pay_data= $HuoGui->wxpayScoreDetail($out_order_no);//补货开门
        $out_request_noo="234323JKHDFE1243252B";
        $pay_data= $HuoGui->wxpayScoreEnable($out_request_noo);//补货开门
        var_dump($pay_data);
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