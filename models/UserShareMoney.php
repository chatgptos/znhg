<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%user_share_money}}".
 *
 * @property integer $id
 * @property integer $store_id
 * @property integer $order_id
 * @property integer $user_id
 * @property integer $type
 * @property integer $source
 * @property string $money
 * @property integer $is_delete
 * @property integer $addtime
 */
class UserShareMoney extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_share_money}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id', 'order_id', 'user_id', 'type', 'source', 'is_delete', 'addtime'], 'integer'],
            [['money'], 'number'],
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
            'order_id' => '订单ID',
            'user_id' => '用户ID',
            'type' => '类型 0--佣金 1--提现',
            'source' => '佣金来源 1--一级分销 2--二级分销 3--三级分销',
            'money' => '金额',
            'is_delete' => 'Is Delete',
            'addtime' => 'Addtime',
        ];
    }

    public static function set($money,$user_id,$order_id,$type,$source=1,$store_id = 0)
    {
        $model = UserShareMoney::findOne([
            'store_id'=>$store_id,
            'user_id'=>$user_id,
            'order_id'=>$order_id,
            'type'=>0,
            'is_delete'=>0
        ]);
        if($model){
            return false;
        }
        $model = new UserShareMoney();
        $model->store_id = $store_id;
        $model->order_id = $order_id;
        $model->user_id = $user_id;
        $model->type = $type;
        $model->source = $source;
        $model->money = $money;
        $model->is_delete = 0;
        $model->addtime = time();
        return $model->save();
    }
}
