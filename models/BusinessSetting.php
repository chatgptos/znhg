<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%seckill}}".
 *
 * @property integer $id
 * @property integer $store_id
 * @property string $open_time
 */
class BusinessSetting extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%business_setting}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id'], 'required'],
            [['store_id'], 'integer'],
            [['open_time'], 'string'],
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
            'open_time' => '开放时间，JSON格式',
            'hldtoyhq' => '7欢乐豆一张',
            'xtjl' => '系统赠送张数',
            'xtjlsell' => '系统赠送张数卖方',
            'jftohld' => '积分对欢乐豆',
            'hldtojf' => '欢乐豆对积分',
            'charge' => '百分比手续费',
            'chargeNum' => '百分比手续费',
            'charge1' => '百分比手续费2级',
            'chargeNum1' => '百分比手续费',
            'charge2' => '百分比手续费3级',
            'chargeNum2' => '百分比手续费',
            'charge3' => '百分比手续费级',
            'chargeNum3' => '百分比手续费',
            'chargeNum5' => '其他手续费',
            'charge5' => '其他手续费',
            'is_hldtoyhq' => '欢乐豆对优惠券是否打开 买优惠券',
            'is_yhqtohld' => '优惠券对欢乐豆是否打开 卖优惠券',
            'is_jftohld' => '积分对欢乐豆是否打开',
            'is_hldtojf' => '欢乐豆对积分是否打开',
        ];
    }
}
