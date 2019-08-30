<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%user_card}}".
 *
 * @property integer $id
 * @property integer $store_id
 * @property integer $user_id
 * @property integer $card_id
 * @property string $card_name
 * @property string $card_pic_url
 * @property string $card_content
 * @property integer $is_use
 * @property integer $is_delete
 * @property integer $addtime
 * @property integer $clerk_id
 * @property integer $shop_id
 * @property integer $clerk_time
 */
class UserCard extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_card}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id', 'user_id', 'card_id', 'is_use', 'is_delete', 'addtime', 'clerk_id', 'shop_id', 'clerk_time'], 'integer'],
            [['card_pic_url', 'card_content'], 'string'],
            [['card_name'], 'string', 'max' => 255],
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
            'user_id' => '用户ID',
            'card_id' => '卡券ID',
            'card_name' => '卡券名称',
            'card_pic_url' => '卡券图片',
            'card_content' => '卡券描述',
            'is_use' => '是否使用 0--未使用 1--已使用',
            'is_delete' => 'Is Delete',
            'addtime' => 'Addtime',
            'clerk_id' => '核销人id',
            'shop_id' => '门店ID',
            'clerk_time' => ' 核销时间',
        ];
    }
}
