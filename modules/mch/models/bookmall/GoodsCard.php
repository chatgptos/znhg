<?php

namespace app\modules\mch\models\bookmall;

use Yii;

/**
 * This is the model class for table "{{%goods_card}}".
 *
 * @property integer $id
 * @property integer $goods_id
 * @property integer $card_id
 * @property integer $is_delete
 * @property integer $addtime
 */
class GoodsCard extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bookmall_goods_card}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['goods_id', 'card_id', 'is_delete', 'addtime'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_id' => 'Goods ID',
            'card_id' => '卡券id',
            'is_delete' => 'Is Delete',
            'addtime' => 'Addtime',
        ];
    }
}
