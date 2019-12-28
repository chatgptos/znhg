<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/16
 * Time: 10:11
 */

namespace app\modules\mch\models\crontab;

use Yii;

/**
 * This is the model class for table "{{%area}}".
 *
 * @property integer $id
 * @property integer $hld_count
 * @property string  $integral_count
 * @property integer $coupon_count
 * @property integer $order_count
 * @property integer $user_count
 * @property integer $is_delete
 * @property integer $statistics_date
 * @property integer $store_id
 * @property integer $jrintegral_count
 * @property integer $jrhld_count
 * @property integer $jrcoupon_count
 *
 *
 */
class DailyData extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%daily_data}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['coupon_count', 'order_count', 'is_delete', 'hld_count', 'jrhld_count'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'store_id' => 'ID',
            'statistics_date' => 'ID',
            'is_delete' => 'ID',
            'user_count' => 'ID',
            'order_count' => 'ID',
            'coupon_count' => 'ID',
            'integral_count' => 'ID',
            'hld_count' => 'ID',
            'jrintegral_count' => 'ID',
            'jrhld_count' => 'ID',
            'jrcoupon_count' => 'ID',

            'peoplesellcount_huanledou1'=>'所有交易欢乐豆总数量（已经交易+等待交易）',
            'peoplesellcount_huanledou_charge1'=>'所有系统收取欢乐豆手续费（已经交易+等待交易）',
            'peoplesellcount_xtjl1'=>'所有系统奖励优惠券数量（已经交易+等待交易）',
            'peoplesellcount_num1'=>'所有售卖优惠券数量（已经交易+等待交易）',
            'peoplesellcount1'=>'所有活跃卖家数量（已经交易+等待交易）',
            'peoplebuyercount1'=>'所有活跃买家数量（已经交易+等待交易）',
            'peoplesellcount_huanledou2'=>'系统收欢乐豆总数（已经交易）',
            'peoplesellcount_huanledou_charge2'=>'系统收取手续费（已经交易）',
            'peoplesellcount_xtjl2'=>'系统奖励优惠券数量（已经交易）',
            'peoplesellcount_num2'=>'交易优惠券数量（已经交易）',
            'peoplesellcount2'=>'所有活跃卖家数量（已经交易）',
            'peoplebuyercount2'=>'所有活跃买家数量（已经交易）',
            'peoplesellcount_huanledou3'=>'所有交易欢乐豆总数量（等待交易）',
            'peoplesellcount_huanledou_charge3'=>'所有系统收取手续费（等待交易）',
            'peoplesellcount_xtjl3'=>'系统奖励优惠券数量（等待交易）',
            'peoplesellcount_num3'=>'售卖优惠券数量（等待交易）',
            'peoplesellcount3'=>'所有活跃卖家数量（已经交易）',
            'peoplebuyercount3'=>'所有活跃买家数量（已经交易）',
            'addtime'=>'addtime',
        ];
    }
//
//CREATE TABLE `ushop_daily_data` (
//`id` int(11) NOT NULL AUTO_INCREMENT,
//`store_id` int(11) DEFAULT NULL,
//`statistics_date` date NOT NULL COMMENT '开放日期，例：2017-08-21',
//`is_delete` smallint(6) NOT NULL DEFAULT '0',
//`user_count` int(11) DEFAULT 0,
//`goods_count` int(11) DEFAULT 0,
//`order_count` int(11) DEFAULT 0,
//`coupon_count` int(11) DEFAULT 0,
//`integral_count` int(11) DEFAULT 0,
//`hld_count` int(11) DEFAULT 0,
//`jruser_count` int(11) DEFAULT 0,
//`jrintegral_count` int(11) DEFAULT 0,
//`jrhld_count` int(11) DEFAULT 0,
//`jrcoupon_count` int(11) DEFAULT 0,
//`peoplesellcount_huanledou1` int(11) DEFAULT 0,
//`peoplesellcount_huanledou_charge1` int(11) DEFAULT 0,
//`peoplesellcount_xtjl1` int(11) DEFAULT 0,
//`peoplesellcount_num1` int(11) DEFAULT 0,
//`peoplesellcount1` int(11) DEFAULT 0,
//`peoplebuyercount1` int(11) DEFAULT 0,
//`peoplesellcount_huanledou2` int(11) DEFAULT 0,
//`peoplesellcount_huanledou_charge2` int(11) DEFAULT 0,
//`peoplesellcount_xtjl2` int(11) DEFAULT 0,
//`peoplesellcount_num2` int(11) DEFAULT 0,
//`peoplesellcount2` int(11) DEFAULT 0,
//`peoplebuyercount2` int(11) DEFAULT 0,
//`peoplesellcount_huanledou3` int(11) DEFAULT 0,
//`peoplesellcount_huanledou_charge3` int(11) DEFAULT 0,
//`peoplesellcount_xtjl3` int(11) DEFAULT 0,
//`peoplesellcount_num3` int(11) DEFAULT 0,
//`peoplesellcount3` int(11) DEFAULT 0,
//`peoplebuyercount3` int(11) DEFAULT 0,
//PRIMARY KEY (`id`)
//) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC


    //卖
    public function add()
    {

        $DailyData = new DailyData();
        $DailyData->store_id = $this->store_id;
        $DailyData->statistics_date = $this->statistics_date;
        $DailyData->is_delete = $this->is_delete;
        $DailyData->coupon_count = $this->coupon_count;
        $DailyData->user_count = $this->user_count;
        $DailyData->integral_count =$this->integral_count;
        $DailyData->hld_count = $this->hld_count;
        $DailyData->jrintegral_count = $this->jrintegral_count;
        $DailyData->jrhld_count = $this->jrhld_count;
        $DailyData->jrcoupon_count = $this->jrcoupon_count;

        $DailyData->peoplesellcount_huanledou1 = $this->peoplesellcount_huanledou1;
        $DailyData->peoplesellcount_huanledou_charge1 = $this->peoplesellcount_huanledou_charge1;
        $DailyData->peoplesellcount_xtjl1 = $this->peoplesellcount_xtjl1;
        $DailyData->peoplesellcount_num1 = $this->peoplesellcount_num1;
        $DailyData->peoplesellcount1 = $this->peoplesellcount1;
        $DailyData->peoplebuyercount1 = $this->peoplebuyercount1;
        $DailyData->peoplesellcount_huanledou2 = $this->peoplesellcount_huanledou2;
        $DailyData->peoplesellcount_huanledou_charge2 = $this->peoplesellcount_huanledou_charge2;
        $DailyData->peoplesellcount_xtjl2 = $this->peoplesellcount_xtjl2;
        $DailyData->peoplesellcount_num2 = $this->peoplesellcount_num2;
        $DailyData->peoplesellcount2 = $this->peoplesellcount2;
        $DailyData->peoplebuyercount2 = $this->peoplebuyercount2;
        $DailyData->peoplesellcount_huanledou3 = $this->peoplesellcount_huanledou3;
        $DailyData->peoplesellcount_huanledou_charge3 = $this->peoplesellcount_huanledou_charge3;
        $DailyData->peoplesellcount_xtjl3 = $this->peoplesellcount_xtjl3;
        $DailyData->peoplesellcount_num3 = $this->peoplesellcount_num3;
        $DailyData->peoplesellcount3 = $this->peoplesellcount3;
        $DailyData->peoplebuyercount3 = $this->peoplebuyercount3;
        $DailyData->addtime  = $this->addtime;


        $DailyData->save();
        echo '成功'.$this->statistics_date."\n";

    }

}