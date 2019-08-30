<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%store}}".
 *
 * @property integer $id
 * @property integer $is_delete
 * @property integer $user_id
 * @property integer $wechat_app_id
 * @property string $name
 * @property string $order_send_tpl
 * @property string $contact_tel
 * @property integer $show_customer_service
 * @property string $copyright
 * @property string $copyright_pic_url
 * @property string $copyright_url
 * @property integer $delivery_time
 * @property integer $after_sale_time
 * @property string $kdniao_mch_id
 * @property string $kdniao_api_key
 * @property integer $cat_style
 * @property string $home_page_module
 * @property string $address
 * @property integer $cat_goods_cols
 * @property integer $over_day
 * @property integer $is_offline
 * @property integer $is_coupon
 * @property integer $cat_goods_count
 * @property integer $send_type
 * @property string $member_content
 * @property integer $nav_count
 * @property integer $integral
 * @property integer $integration
 */
class Store extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%store}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_delete', 'user_id', 'wechat_app_id', 'show_customer_service', 'delivery_time', 'after_sale_time', 'cat_style', 'cat_goods_cols', 'over_day', 'is_offline', 'is_coupon', 'cat_goods_count', 'send_type', 'nav_count', 'integral'], 'integer'],
            [['user_id', 'name'], 'required'],
            [['home_page_module', 'address', 'member_content', 'integration'], 'string'],
            [['name', 'order_send_tpl', 'contact_tel', 'copyright', 'kdniao_mch_id', 'kdniao_api_key'], 'string', 'max' => 255],
            [['copyright_pic_url', 'copyright_url'], 'string', 'max' => 1000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'is_delete' => 'Is Delete',
            'user_id' => '用户id',
            'wechat_app_id' => '微信小程序id',
            'name' => '店铺名称',
            'order_send_tpl' => '发货通知模板消息id',
            'contact_tel' => '联系电话',
            'show_customer_service' => '是否显示在线客服：0=否，1=是',
            'copyright' => 'Copyright',
            'copyright_pic_url' => 'Copyright Pic Url',
            'copyright_url' => '版权的超链接',
            'delivery_time' => '收货时间',
            'after_sale_time' => '售后时间',
            'kdniao_mch_id' => '快递鸟商户号',
            'kdniao_api_key' => '快递鸟api key',
            'cat_style' => '分类页面样式：1=无侧栏，2=有侧栏',
            'home_page_module' => '首页模块布局',
            'address' => '店铺地址',
            'cat_goods_cols' => '首页分类商品列数',
            'over_day' => '未支付订单超时时间',
            'is_offline' => '是否开启自提',
            'is_coupon' => '是否开启优惠券',
            'cat_goods_count' => '首页分类的商品个数',
            'send_type' => '发货方式：0=快递或自提，1=仅快递，2=仅自提',
            'member_content' => '会员等级说明',
            'nav_count' => '首页导航栏个数 0--4个 1--5个',
            'integral' => '抵扣积分',
            'integration' => '积分使用规则',
        ];
    }
}
