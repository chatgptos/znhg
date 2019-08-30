<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%user_coupon}}".
 *
 * @property integer $id
 * @property integer $store_id
 * @property integer $user_id
 * @property integer $coupon_id
 * @property integer $coupon_auto_send_id
 * @property integer $begin_time
 * @property integer $end_time
 * @property integer $is_expire
 * @property integer $is_use
 * @property integer $is_delete
 * @property integer $addtime
 * @property integer $type
 */
class UserCoupon extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_coupon}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id', 'user_id', 'coupon_id'], 'required'],
            [['store_id', 'user_id', 'coupon_id', 'coupon_auto_send_id', 'begin_time', 'end_time', 'is_expire', 'is_use', 'is_delete', 'addtime', 'type'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'store_id' => 'Store ID',
            'user_id' => 'User ID',
            'coupon_id' => 'Coupon ID',
            'coupon_auto_send_id' => '自动发放id',
            'begin_time' => '有效期开始时间',
            'end_time' => '有效期结束时间',
            'is_expire' => '是否已过期：0=未过期，1=已过期',
            'is_use' => '是否已使用：0=未使用，1=已使用',
            'is_delete' => 'Is Delete',
            'addtime' => 'Addtime',
            'type' => '领取类型 0--平台发放 1--自动发放 2--领券中心领取',
        ];
    }
}
