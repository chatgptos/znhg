<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%home_block}}".
 *
 * @property integer $id
 * @property integer $store_id
 * @property string $name
 * @property string $data
 * @property integer $addtime
 * @property integer $is_delete
 */
class HomeBlock extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%home_block}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id', 'name'], 'required'],
            [['store_id', 'addtime', 'is_delete'], 'integer'],
            [['data'], 'string'],
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
            'name' => 'Name',
            'data' => 'Data',
            'addtime' => 'Addtime',
            'is_delete' => 'Is Delete',
        ];
    }
}
