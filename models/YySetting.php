<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%yy_setting}}".
 *
 * @property string $store_id
 * @property integer $cat
 * @property string $success_notice
 * @property string $refund_notice
 */
class YySetting extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%yy_setting}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id', 'cat'], 'required'],
            [['store_id', 'cat'], 'integer'],
            [['success_notice', 'refund_notice'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'store_id' => '是否显示分类',
            'cat' => '参数',
            'success_notice' => '预约成功模板通知',
            'refund_notice' => '退款模板id',
        ];
    }
}
