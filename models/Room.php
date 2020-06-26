<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%room}}".
 *
 * @property integer $id
 * @property integer $store_id
 * @property string $name
 * @property string $pic_url
 * @property string $content
 * @property integer $is_delete
 * @property integer $room_id
 * @property integer $addtime
 * @property integer $live_status
 * @property integer $goods
 * @property integer $user_id
 *
 *
 */
class Room extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%room}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id','store_id', 'is_delete', 'addtime','room_id','live_status'], 'integer'],
            [['pic_url', 'content','goods'], 'string'],
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
            'room_id' => 'room_id',
            'store_id' => 'Store ID',
            'name' => '直播间名称',
            'pic_url' => '直播间图片',
            'content' => '直播间描述',
            'is_delete' => 'Is Delete',
            'addtime' => 'Addtime',
            'live_status'=>'直播状态',
            'goods'=>'直播商品',
            'user_id'=>'user_id',
        ];
    }
    public function beforeSave($insert)
    {
        $this->name = \yii\helpers\Html::encode($this->name);
        $this->content = \yii\helpers\Html::encode($this->content);
        return parent::beforeSave($insert);
    }
}
