<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/31
 * Time: 14:54
 */

namespace app\extensions;


use app\models\Store;
use app\modules\api\controllers\Controller;

class WxPayScore  extends Controller
{
    public $mch_id='1555897421';

    public $service_id='00004000000000158195309791355586';

    public $sign_type='HMAC-SHA256';




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
        return([
            'code' => 0,
            'msg' => 'success',
            'data' => $pay_data,
        ]);
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
        $pay_data['sign'] = $this->makeSign($pay_data,$this->wechat->apiKey);
        return([
            'code' => 0,
            'msg' => 'success',
            'data' => $pay_data,
        ]);
    }


    /**
     * Json方式 调用电子面单接口
     *
     */
    public static function post($url,$data){
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
        return $output;
    }


    /**
     *  post提交数据
     * @param  string $url 请求Url
     * @param  array $datas 提交的数据
     * @return url响应返回的html
     */
    public function sendPost($url, $datas) {
        $temps = array();
        foreach ($datas as $key => $value) {
            $temps[] = sprintf('%s=%s', $key, $value);
        }
        $post_data = implode('&', $temps);
        $url_info = parse_url($url);
        if(empty($url_info['port']))
        {
            $url_info['port']=80;
        }
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader.= "Host:" . $url_info['host'] . "\r\n";
        $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
        $httpheader.= "Connection:close\r\n\r\n";
        $httpheader.= $post_data;
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
            $gets.= fread($fd, 128);
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
    function encrypt($data, $appkey) {
        return urlencode(base64_encode(md5($data.$appkey)));
    }

    /**************************************************************
     *
     *  将数组转换为JSON字符串（兼容中文）
     *  @param  array   $array      要转换的数组
     *  @return string      转换得到的json字符串
     *  @access public
     *
     *************************************************************/
    function JSON($array) {
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
        $string = $string . "&key=".$this->key;
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

}