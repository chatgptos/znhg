<?php

namespace app\modules\api\models\crowdc;

use Yii;

/**
 * This is the model class for table "{{%seckill}}".
 *
 * @property integer $id
 * @property integer $store_id
 * @property string $open_time
 */
class Seckill extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%crowdc_seckill}}';
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
        ];
    }
}
