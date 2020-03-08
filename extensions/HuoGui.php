<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/31
 * Time: 14:54
 */

namespace app\extensions;


use app\models\Store;

class HuoGui
{

    public $app_id='2019082351351';
    public $order_id;
    public $key='DE448FA75DAB07D141343D590BBE679D';
    public $api='https://api.voidiot.com/open-api';
    public $user;


//        $url="https://api.voidiot.com/open-api/syncUserInfo";//同步用户数据可以开门
//        $url="https://api.voidiot.com/open-api/getDeviceList";//获取货柜列表
//        $url="https://api.voidiot.com/open-api/getDeviceById";//获取货柜详情
//        $url="https://api.voidiot.com/open-api/completeOrder";//完结订单传入开门id记录
//        $url="https://api.voidiot.com/open-api/openDoor";//取货开门
//        $url="https://api.voidiot.com/open-api/getSelectGoods";//实时购买商品数据 判断门是否关闭
//        $url="https://api.voidiot.com/open-api/getOrdersByOpenDoorId";//获取货柜商品详情
//        $url="https://api.voidiot.com/open-api/getDeviceGoods";//获取货柜商品详情
//        $url="https://api.voidiot.com/open-api/replenish";//补货开门
//        $url="https://api.voidiot.com/open-api/getDeviceRealTimeGoods";//获取补货实时货品


    public function search1()
    {
        $pay_data = array(
            'appId' =>$this->app_id,
            "timestamp"=>date('Y-m-d His', time()),
            'biz_content' => array(
                "deviceId"=>100023,//必须要有设备
                "unionid"=>"1353817842",
                "opendoorRecordId"=>"9516",
            )

        );
        $pay_data['sign'] = $this->makeSignHG($pay_data['biz_content']);
//        $url="https://api.voidiot.com/open-api/syncUserInfo";//同步用户数据可以开门
        $url="https://api.voidiot.com/open-api/getDeviceList";//获取货柜列表
//        $url="https://api.voidiot.com/open-api/getDeviceById";//获取货柜详情
//        $url="https://api.voidiot.com/open-api/completeOrder";//完结订单传入开门id记录
//        $url="https://api.voidiot.com/open-api/openDoor";//取货开门
//        $url="https://api.voidiot.com/open-api/getSelectGoods";//实时购买商品数据 判断门是否关闭
//        $url="https://api.voidiot.com/open-api/getOrdersByOpenDoorId";//获取货柜商品详情
//        $url="https://api.voidiot.com/open-api/getDeviceGoods";//获取货柜商品详情
//        $url="https://api.voidiot.com/open-api/replenish";//补货开门
//        $url="https://api.voidiot.com/open-api/getDeviceRealTimeGoods";//获取补货实时货品

        $pay_data['biz_content'] = json_encode($pay_data['biz_content'],true);
        $data  = json_encode($pay_data,true);
        $res= self::post($url,$data);
        return $res;
    }


    public function syncUserInfo($biz_content)
    {
        $res = $this->setPostBodyCommon($biz_content,'syncUserInfo');
        return $res;
    }

    public function getDeviceList($biz_content)
    {
        if(!$biz_content){
            $biz_content=$biz_content=array(
                "deviceId"=>100023,//必须要有设备
            );
        }
        $res = $this->setPostBodyCommon($biz_content,'getDeviceList');
        return $res;
    }

    public function getDeviceById($biz_content)
    {
        $res = $this->setPostBodyCommon($biz_content,'getDeviceById');
        return $res;
    }

    public function completeOrder($biz_content)
    {
        $res = $this->setPostBodyCommon($biz_content,'completeOrder');
        return $res;
    }


    public function openDoor($biz_content)
    {
        $res = $this->setPostBodyCommon($biz_content,'openDoor');
        return $res;
    }


    public function getSelectGoods($biz_content)
    {
        $res = $this->setPostBodyCommon($biz_content,'getSelectGoods');
        return $res;
    }


    public function getOrdersByOpenDoorId($biz_content)
    {
        $res = $this->setPostBodyCommon($biz_content,'getOrdersByOpenDoorId');
        return $res;
    }


    public function getDeviceGoods($biz_content)
    {
        $res = $this->setPostBodyCommon($biz_content,'getDeviceGoods');
        return $res;
    }


    public function replenish($biz_content)
    {
        $res = $this->setPostBodyCommon($biz_content,'replenish');
        return $res;
    }


    public function getDeviceRealTimeGoods($biz_content)
    {
        $res = $this->setPostBodyCommon($biz_content,'getDeviceRealTimeGoods');
        return $res;
    }



    //传入模块
    function setPostBodyCommon($biz_content,$uri)
    {
        $pay_data = array(
            'appId' =>$this->app_id,
            "timestamp"=>date('Y-m-d H:i:s', time()),
            'biz_content' =>$biz_content
        );


        $url=$this->api.'/'.$uri;//同步用户数据可以开门
        if($biz_content){
            $pay_data['sign'] = $this->makeSignHG($pay_data['biz_content']);
            $pay_data['biz_content'] = json_encode($pay_data['biz_content'],true);
        }else{
            unset($pay_data['biz_content']);
        }
        $data  = json_encode($pay_data,true);
        $res= self::post($url,$data);
        $res = json_decode($res,true);
        return  $res;
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