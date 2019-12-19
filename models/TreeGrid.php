<?php

namespace app\models;

use Yii; 

/**
 * This is the model class for table "{{%address}}".
 *
 * @property integer $id
 * @property integer $store_id
 * @property integer $user_id
 * @property string $name
 * @property string $mobile
 * @property integer $province_id
 * @property string $province
 * @property integer $city_id
 * @property string $city
 * @property integer $district_id
 * @property string $district
 * @property string $detail
 * @property integer $is_default
 * @property integer $addtime
 * @property integer $is_delete
 */
class TreeGrid extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'addtime', 'is_delete', 'store_id', 'is_distributor', 'parent_id', 'time', 'is_clerk', 'shop_id', 'level'], 'integer'],
            [['username', 'password', 'auth_key', 'access_token', 'avatar_url'], 'required'],
            [['avatar_url'], 'string'],
            [['total_price', 'price', 'integral', 'total_integral','total_hld','hld'], 'number'],
            [['username', 'password', 'auth_key', 'access_token', 'wechat_open_id', 'wechat_union_id', 'nickname'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '用户类型：0=管理员，1=普通用户',
            'username' => 'Username',
            'password' => 'Password',
            'auth_key' => 'Auth Key',
            'access_token' => 'Access Token',
            'addtime' => 'Addtime',
            'is_delete' => 'Is Delete',
            'wechat_open_id' => '微信openid',
            'wechat_union_id' => '微信用户union id',
            'nickname' => '昵称',
            'avatar_url' => '头像url',
            'store_id' => '商城id',
            'is_distributor' => '是否是分销商 0--不是 1--是 2--申请中',
            'parent_id' => '父级ID',
            'time' => '成为分销商的时间',
            'total_price' => '累计佣金',
            'price' => '可提现佣金',
            'is_clerk' => '是否是核销员 0--不是 1--是',
            'shop_id' => 'Shop ID',
            'level' => '会员等级',
            'integral' => '用户当前积分',
            'total_integral' => '用户总积分',
            'hld' => '用户当前欢乐豆',
            'total_hld' => '用户总欢乐豆',
            'coupon' => '用户优惠券',
            'coupon_total' => '用户总优惠券',
            'fuliquan' => '福利权份数',
            'crowdstockright' => '福利权份数',
        ];
    }
}
