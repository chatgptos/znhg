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
 * @property integer $status
 * @property integer $is_delete
 * @property integer $addtime
 * @property integer $chance
 * @property integer $quan
 */
class AwardFuli extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%settlementbonus_fuli_setting}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['require_level','store_id', 'level', 'status', 'is_delete', 'addtime'], 'integer'],
            [['money'], 'number'],
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
            'level' => '期数',
            'name' => '福利分红名称',
            'status' => '状态 0--禁用 1--启用',
            'money' => '奖励积分',
            'all_money' => '券池总奖励',
            'num' => '总份数',
            'coupon_require' => '每份需要优惠券数量',
            'end_fulichi_time' => '券池结束时间',
            'is_delete' => 'Is Delete',
            'addtime' => 'Addtime',
            'require_level'=>'require_level',
        ];

//  CREATE TABLE `ushop_settlementbonus_fuli_setting` (
//  `id` int(11) NOT NULL AUTO_INCREMENT,
//  `store_id` int(11) DEFAULT NULL,
//  `level` int(11) DEFAULT NULL,
//  `name` varchar(255) DEFAULT NULL COMMENT '等级名称',
//  `money` int(10) DEFAULT NULL COMMENT '每份奖励积分',
//  `status` int(11) DEFAULT '0' COMMENT '状态 0--禁用 1--启用',
//  `is_delete` int(11) DEFAULT NULL,
//  `addtime` int(11) DEFAULT NULL,
//  `all_money` decimal(10,0) DEFAULT '0' COMMENT '总券池',
//  `end_fulichi_time` int(11) DEFAULT '0',
//  `num` int(11) DEFAULT '0' COMMENT '总份数',
//  `coupon_require` int(11) DEFAULT '0' COMMENT '需要的优惠券数',
//  PRIMARY KEY (`id`)
//) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='会员等级'

    }
}
