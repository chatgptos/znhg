<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/21
 * Time: 9:58
 */

namespace app\models;


use Curl\Curl;
use yii\helpers\VarDumper;

class ExpressDetailForm extends Model
{
    public $express_no;
    public $express;
    public $store_id;

    public $status_text = [
        1 => '?',
        2 => '运输中',
        3 => '已签收',
        4 => '问题件',
    ];

    public function rules()
    {
        return [
            [['express', 'express_no'], 'trim'],
            [['express_no', 'express', 'store_id'], 'required'],
        ];
    }

    public function search()
    {
        if (!$this->validate())
            return $this->getModelError();
        return $this->searchByKdniao();
    }

    //快递鸟
    public function searchByKdniao()
    {
        $cache_key = md5(json_encode($this->attributes));
        $cache_time = 3600;
        $data = \Yii::$app->cache->get($cache_key);
        if ($data)
            return $data;
        $curl = new Curl();
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        $api = "http://api.kdniao.cc/Ebusiness/EbusinessOrderHandle.aspx";
        $store = Store::findOne($this->store_id);
        if (!$store || !$store->kdniao_mch_id || !$store->kdniao_api_key)
            return [
                'code' => 1,
                'msg' => '后台尚未配置物流接口信息，获取物流信息'
            ];
        $mch_id = $store->kdniao_mch_id;
        $api_key = $store->kdniao_api_key;
        $express_data = json_encode([
            'ShipperCode' => $this->getExpressCode($this->express, 'kdniao'),
            'LogisticCode' => $this->express_no,
        ], JSON_UNESCAPED_UNICODE);
        $data = [
            'RequestData' => urlencode($express_data),
            'EBusinessID' => $mch_id,
            'RequestType' => '1002',
            'DataSign' => base64_encode(md5($express_data . $api_key)),
            'DataType' => '2',
        ];
        $curl->post($api, $data);
        $res = $curl->response;
        $res = json_decode($res);
        if (!$res)
            return [
                'code' => 1,
                'msg' => '获取物流信息失败：系统错误',
            ];
        if ($res && $res->Success == false) {
            return [
                'code' => 1,
                'msg' => '获取物流信息失败：' . $res->Reason,
            ];
        }
        $status = [
            '1' => 1,//?
            '2' => 2,//在途中
            '3' => 3,//已签收
            '4' => 4,//问题件
        ];
        $list = [];
        foreach ($res->Traces as $item) {
            $list[] = (object)[
                'datetime' => $item->AcceptTime,
                'detail' => $item->AcceptStation,
            ];
        }
        $list = array_reverse($list);
        $data = [
            'code' => 0,
            'data' => [
                'status' => $status[$res->State],
                'status_text' => $this->status_text[$status[$res->State]],
                'list' => $list,
            ],
        ];
        if ($data['data']['status'] === null) {
            $data['data']['status'] = '';
        }
        if ($data['data']['status_text'] === null) {
            $data['data']['status_text'] = '获取失败';
        }
        \Yii::$app->cache->set($cache_key, $data, $cache_time);
        return $data;
    }

    private function getExpressCode($express, $type)
    {
        $express_afters = [
            '快递',
            '快运',
            '物流',
            '速运',
        ];
        $express_name = Express::find()->orderBy('sort')->where([
            'name' => $express,
            'type' => $type,
        ])->one();
        if ($express_name)
            return $express_name->code;
        foreach ($express_afters as $after) {
            $express = str_replace($after, '', $express);
        }
        $express = Express::find()->orderBy('sort')->where([
            'AND',
            ['LIKE', 'name', $express,],
            ['type' => $type,]
        ])->one();
        if ($express)
            return $express->code;
        return '';
    }
}