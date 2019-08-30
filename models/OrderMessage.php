<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%order_message}}".
 *
 * @property integer $id
 * @property integer $store_id
 * @property integer $order_id
 * @property integer $is_read
 * @property integer $is_sound
 * @property integer $is_delete
 * @property integer $addtime
 */
class OrderMessage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_message}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id', 'order_id', 'is_read', 'is_sound', 'is_delete', 'addtime'], 'integer'],
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
            'order_id' => '类型id 系统消息时为0',
            'is_read' => '消息是否已读 0--未读 1--已读',
            'is_sound' => '是否提示 0--未提示  1--已提示',
            'is_delete' => 'Is Delete',
            'addtime' => 'Addtime',
        ];
    }

    public static function set($order_id,$store_id=0)
    {
        if(empty($order_id)){
            return false;
        }
        $model = OrderMessage::findOne([
            'store_id'=>$store_id,
            'order_id'=>$order_id
        ]);
        if(!$model){
            $model = new OrderMessage();
            $model->order_id = $order_id;
            $model->store_id = $store_id;
            $model->is_delete = 0;
            $model->is_read = 0;
            $model->is_sound = 0;
            $model->addtime = time();
        }
        return $model->save();
    }
}
