<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%level}}".
 *
 * @property integer $id
 * @property integer $store_id
 * @property integer $level
 * @property string $name
 * @property string $money
 * @property string $discount
 * @property integer $status
 * @property integer $is_delete
 * @property integer $addtime
 */
class Level extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%level}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id', 'level', 'status', 'is_delete', 'addtime'], 'integer'],
            [['money', 'discount'], 'number'],
            [['name'], 'string', 'max' => 255],
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
            'level' => 'Level',
            'name' => '等级名称',
            'money' => '会员完成订单金额满足则升级',
            'discount' => '折扣',
            'status' => '状态 0--禁用 1--启用',
            'is_delete' => 'Is Delete',
            'addtime' => 'Addtime',
        ];
    }
}
