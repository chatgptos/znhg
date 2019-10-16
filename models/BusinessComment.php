<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%order_comment}}".
 *
 * @property integer $id
 * @property integer $store_id
 * @property integer $order_id
 * @property integer $order_detail_id
 * @property integer $goods_id
 * @property integer $user_id
 * @property string $score
 * @property string $content
 * @property string $pic_list
 * @property integer $is_hide
 * @property integer $is_delete
 * @property integer $addtime
 */
class BusinessComment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%business_comment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id', 'order_id','user_id'], 'required'],
            [['store_id', 'order_id', 'user_id', 'is_hide', 'is_delete', 'addtime'], 'integer'],
            [['score'], 'number'],
            [['pic_list'], 'string'],
            [['content'], 'string', 'max' => 1000],
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
            'order_id' => 'Order ID',
            'user_id' => 'User ID',
            'score' => '评分：1=差评，2=中评，3=好',
            'content' => '评价内容',
            'pic_list' => '图片',
            'is_hide' => '是否隐藏：0=不隐藏，1=隐藏',
            'is_delete' => 'Is Delete',
            'addtime' => 'Addtime',
        ];
    }
}
