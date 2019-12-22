<?php

namespace app\modules\mch\models\settlementstatistics;

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
 * @property integer $chance
 * @property integer $quan
 */
class Award extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%settlementbonus_goods_setting}}';
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
            'money' => '花费券抽取',
            'discount' => '提成比例',
            'quan' => '券',
            'chance' => '商品id',
            'status' => '状态 0--禁用 1--启用',
            'is_delete' => 'Is Delete',
            'addtime' => 'Addtime',
        ];
    }
}
