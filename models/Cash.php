<?php

namespace app\models;

use Yii;
use Codeception\PHPUnit\ResultPrinter\HTML;

/**
 * This is the model class for table "{{%cash}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $store_id
 * @property string $price
 * @property integer $status
 * @property integer $is_delete
 * @property integer $addtime
 * @property integer $pay_time
 * @property integer $type
 * @property string $mobile
 * @property string $name
 */
class Cash extends \yii\db\ActiveRecord
{
    public static $status = [
        '待审核',
        '待打款',
        '已打款',
        '无效'
    ];
    public static $type = [
        '微信支付',
        '支付宝支付',
        '手动打款'
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cash}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'store_id', 'pay_time'], 'required'],
            [['user_id', 'store_id', 'status', 'is_delete', 'addtime', 'pay_time', 'type'], 'integer'],
            [['price'], 'number'],
            [['mobile', 'name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'store_id' => 'Store ID',
            'price' => '提现金额',
            'status' => '申请状态 0--申请中 1--确认申请 2--已打款 3--驳回',
            'is_delete' => 'Is Delete',
            'addtime' => 'Addtime',
            'pay_time' => '付款',
            'type' => '支付方式 0--微信支付  1--支付宝',
            'mobile' => '支付宝账号',
            'name' => '支付宝姓名',
        ];
    }

    public function beforeSave($insert)
    {
        $this->name = \yii\helpers\Html::encode($this->name);
        $this->mobile = \yii\helpers\Html::encode($this->mobile);
        return parent::beforeSave($insert);
    }
}
