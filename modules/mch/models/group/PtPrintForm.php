<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/31
 * Time: 15:22
 */

namespace app\modules\mch\models\group;
use app\extensions\KdOrder;
use app\models\Delivery;
use app\models\Express;
use app\models\PtOrder;
use app\models\Sender;
use app\modules\mch\models\Model;

class PtPrintForm extends Model
{
    public $order_id;
    public $store_id;
    public $express;
    public $post_code;

    public function send()
    {
        $order = PtOrder::findOne(['id'=>$this->order_id]);
        $express = Express::findOne(['name'=>$this->express]);
        if(!$express){
            return [
                'code'=>1,
                'msg'=>'快递公司不存在'
            ];
        }

//构造电子面单提交信息
        $eorder = [];
        $delivery = Delivery::findOne(['express_id'=>$express->id,'is_delete'=>0,'store_id'=>$this->store_id]);
        if(!$delivery){
            $delivery_id = 0;
            $pay_type = 1;
        }else{
            $delivery_id = $delivery->id;
            $pay_type = 3;
            $eorder['CustomerName'] = $delivery->customer_name;
            $eorder['CustomerPwd'] = $delivery->customer_pwd;
            $eorder['SendSite'] = $delivery->send_site;
        }
        $sender_list = Sender::findOne(['delivery_id'=>$delivery_id,'is_delete'=>0,'store_id'=>$this->store_id]);
        if(!$sender_list){
            return [
                'code'=>1,
                'msg'=>'请先设置发件人信息'
            ];
        }
        $eorder["ShipperCode"] = $express->code;
        $eorder["OrderCode"] = $order->order_no;
        $eorder["PayType"] = $pay_type;
        $eorder["ExpType"] = 1;
        $eorder["IsReturnPrintTemplate"] = 1;

        $sender = [];
        $sender["Company"] = $sender_list->company;
        $sender["Name"] = $sender_list->name;
        $sender["Mobile"] = $sender_list->mobile?$sender_list->mobile:$sender_list->tel;
        $sender["ProvinceName"] = $sender_list->province;
        $sender["CityName"] = $sender_list->city;
        $sender["ExpAreaName"] = $sender_list->exp_area;
        $sender["Address"] = $sender_list->address;
        $sender["PostCode"] = $sender_list->post_code;

        $receiver = [];
        $receiver["Name"] = $order->name;
        $receiver["Mobile"] = $order->mobile;
        $address = ['province'=>'空','city'=>'空','district'=>'空','detail'=>$order->address];
        $receiver_address = $order->address_data?json_decode($order->address_data,true):$address;
        $receiver["ProvinceName"] = $receiver_address['province'];
        $receiver["CityName"] = $receiver_address['city'];
        $receiver["ExpAreaName"] = $receiver_address['district'];
        $receiver["Address"] = $receiver_address['detail'];
        $receiver["PostCode"] = $this->post_code;

        $form = new PtOrderForm();
        $good_list = $form->getOrderGoodsList($order->id);
        $good = $good_list[0];
        $commodityOne = [];
        $commodityOne["GoodsName"] = $good['name'];
        $commodityOne["Goodsquantity"] = $good['num'];
        $commodity = [];
        $commodity[] = $commodityOne;

        $eorder["Sender"] = $sender;
        $eorder["Receiver"] = $receiver;
        $eorder["Commodity"] = $commodity;


//调用电子面单
        $jsonParam = json_encode($eorder, JSON_UNESCAPED_UNICODE);

//$jsonParam = JSON($eorder);//兼容php5.2（含）以下

//        echo "电子面单接口提交内容：<br/>".$jsonParam;
        $jsonResult = KdOrder::submitEOrder($jsonParam,$this->store_id);
//        echo "<br/><br/>电子面单提交结果:<br/>".$jsonResult;

//解析电子面单返回结果
        $result = json_decode($jsonResult, true);
//        echo "<br/><br/>返回码:".$result["ResultCode"];
        if($result["ResultCode"] == "100") {
//            echo "<br/>是否成功:".$result["Success"];
            return [
                'code'=>0,
                'msg'=>'成功',
                'data'=>$result
            ];
        }
        else {
//            echo "<br/>电子面单下单失败";
            return [
                'code'=>1,
                'msg'=>$result['Reason']
            ];
        }
    }
}