<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/6/26
 * Time: 14:38
 */

namespace app\modules\mch\models;


use app\models\Option;
use app\models\Store;
use app\models\WechatApp;

class StoreSettingForm extends Model
{
    public $store_id;
    public $name;
    public $order_send_tpl;

    public $app_id;
    public $app_secret;
    public $mch_id;
    public $key;
    public $cert_pem;
    public $key_pem;

    public $contact_tel;
    public $show_customer_service;
    public $copyright;
    public $copyright_pic_url;
    public $copyright_url;

    public $delivery_time;
    public $after_sale_time;

    public $kdniao_mch_id;
    public $kdniao_api_key;

    public $cat_style;

    public $address;
    public $cat_goods_cols;

    public $over_day;
    public $is_offline;
    public $is_coupon;

    public $cat_goods_count;
    public $send_type;
    public $nav_count;
    public $service;
    public $integral;
    public $integration;
    public $notice;

    public function rules()
    {
        return [
            [['name', 'app_id', 'app_secret', 'mch_id', 'key', 'order_send_tpl', 'contact_tel', 'copyright', 'copyright_pic_url', 'copyright_url', 'kdniao_mch_id', 'kdniao_api_key', 'address', 'cert_pem', 'key_pem'], 'trim'],
            [['name', 'cat_goods_cols', 'integral',], 'required'],
            [['order_send_tpl', 'contact_tel', 'kdniao_mch_id', 'kdniao_api_key', 'address', 'service', 'integration', 'notice'], 'string'],
            [['show_customer_service', 'delivery_time', 'after_sale_time', 'cat_style', 'cat_goods_cols', 'over_day', 'is_offline', 'is_coupon', 'cat_goods_count', 'send_type', 'nav_count', 'integral'], 'integer'],
            ['cat_goods_count', 'default', 'value' => 6],
            [['cert_pem', 'key_pem'], 'default', 'value' => '0']
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '店铺名称',
            'app_id' => '小程序AppId',
            'app_secret' => '小程序AppSecret',
            'mch_id' => '微信支付商户号',
            'key' => '微信支付key',
            'cert_pem' => '微信支付apiclient_cert.pem',
            'key_pem' => '微信支付apiclient_key.pem',

            'order_send_tpl' => '发货模板消息id',
            'delivery_time' => '收货时间',
            'after_sale_time' => '售后时间',
            'over_day' => '超时时间',
            'cat_goods_count' => '首页分类商品显示个数',
            'nav_count' => '首页导航',
            'service' => '客服图标',
            'integral' => '抵扣积分',
            'integration' => '积分使用规则',
            'notice' => '商城公告',
        ];
    }

    public function save()
    {
        if (!$this->validate())
            return $this->getModelError();
        $store = Store::findOne($this->store_id);
        $store->name = $this->name;
        $store->order_send_tpl = $this->order_send_tpl;
        $store->contact_tel = $this->contact_tel;
        $store->show_customer_service = $this->show_customer_service;
        $store->copyright = $this->copyright;
        $store->copyright_pic_url = $this->copyright_pic_url;
        $store->copyright_url = $this->copyright_url;
        $store->delivery_time = $this->delivery_time;
        $store->after_sale_time = $this->after_sale_time;
        $store->kdniao_mch_id = $this->kdniao_mch_id;
        $store->kdniao_api_key = $this->kdniao_api_key;
        $store->cat_style = $this->cat_style;
        $store->address = $this->address;
        $store->cat_goods_cols = $this->cat_goods_cols;
        $store->over_day = $this->over_day;
        $store->is_offline = $this->is_offline;
        $store->is_coupon = $this->is_coupon;
        $store->cat_goods_count = $this->cat_goods_count;
        $store->send_type = $this->send_type;
        $store->nav_count = $this->nav_count;
        $store->integral = $this->integral ?: 10;
        $store->integration = $this->integration;
        $store->save();

        Option::set('service', $this->service, $this->store_id, 'admin');
        Option::set('notice', $this->notice, $this->store_id, 'admin');

        return [
            'code' => 0,
            'msg' => '保存成功',
            'attr' => $store->attributes
        ];
    }
}