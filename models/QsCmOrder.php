<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%yy_order}}".
 *
 * @property string $id
 * @property string $goods_id
 * @property string $user_id
 * @property string $order_no
 * @property string $total_price
 * @property string $pay_price
 * @property integer $is_pay
 * @property integer $pay_type
 * @property string $pay_time
 * @property integer $is_use
 * @property string $is_comment
 * @property integer $apply_delete
 * @property integer $addtime
 * @property integer $is_delete
 * @property string $offline_qrcode
 * @property integer $is_cancel
 * @property string $store_id
 * @property string $use_time
 * @property string $clerk_id
 * @property string $shop_id
 * @property integer $is_refund
 * @property string $form_id
 * @property string $coupon
 * @property string $integral
 */
class QsCmOrder extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%qs_order}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['goods_id', 'user_id', 'order_no', 'store_id', 'form_id'], 'required'],
            [['goods_id', 'user_id', 'is_pay', 'pay_type', 'pay_time', 'is_use', 'is_comment', 'apply_delete', 'addtime', 'is_delete', 'is_cancel', 'store_id', 'use_time', 'clerk_id', 'shop_id', 'is_refund'], 'integer'],
            [['total_price', 'pay_price'], 'number'],
            [['offline_qrcode'], 'string'],
            [['order_no', 'form_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_id' => '商品id',
            'user_id' => '用户id',
            'order_no' => '订单号',
            'total_price' => '订单总费用',
            'pay_price' => '实际支付总费用',
            'is_pay' => '支付状态：0=未支付，1=已支付',
            'pay_type' => '支付方式：1=微信支付',
            'pay_time' => '支付时间',
            'is_use' => '是否使用',
            'is_comment' => '是否评论',
            'apply_delete' => '是否申请取消订单：0=否，1=申请取消订单',
            'addtime' => 'Addtime',
            'is_delete' => 'Is Delete',
            'offline_qrcode' => '核销码',
            'is_cancel' => '是否取消',
            'store_id' => 'Store ID',
            'use_time' => '核销时间',
            'clerk_id' => '核销员user_id',
            'shop_id' => '自提门店ID',
            'is_refund' => '是否退款',
            'form_id' => '表单ID',
            'coupon' => '欢乐豆',
            'integral' => '积分',
//            'express_price' => '运费',
//            'name' => '收货人姓名',
//            'mobile' => '收货人手机',
//            'address' => '收货地址',
//            'remark' => '订单备注',
//            'is_send' => '发货状态：0=未发货，1=已发货',
//            'send_time' => '发货时间',
//            'express' => '物流公司',
//            'express_no' => 'Express No',
//            'is_confirm' => '确认收货状态：0=未确认，1=已确认收货',
//            'confirm_time' => '确认收货时间',
//            'is_price' => '是否发放佣金',
//            'parent_id' => '用户上级ID',
//            'first_price' => '一级佣金',
//            'second_price' => '二级佣金',
//            'third_price' => '三级佣金',
//            'coupon_sub_price' => '优惠券抵消金额',
//            'address_data' => '收货地址信息，json格式',
//            'content' => '备注',
//            'is_offline' => '是否到店自提 0--否 1--是',
//            'before_update_price' => '修改前的价格',
        ];
    }
}
