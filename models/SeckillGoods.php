<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%seckill_goods}}".
 *
 * @property integer $id
 * @property integer $store_id
 * @property integer $goods_id
 * @property integer $start_time
 * @property string $open_date
 * @property string $attr
 * @property integer $is_delete
 * @property integer $buy_max
 */
class SeckillGoods extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%seckill_goods}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id', 'goods_id', 'start_time', 'open_date', 'attr'], 'required'],
            [['store_id', 'goods_id', 'start_time', 'is_delete', 'buy_max'], 'integer'],
            [['open_date'], 'safe'],
            [['attr'], 'string'],
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
            'goods_id' => 'Goods ID',
            'start_time' => '开始时间：0~23',
            'open_date' => '开放日期，例：2017-08-21',
            'attr' => '规格秒杀价数量',
            'is_delete' => 'Is Delete',
            'buy_max' => '限购数量，0=不限购',
        ];
    }
}
