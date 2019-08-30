<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/24
 * Time: 11:59
 */

namespace app\modules\mch\models;

use app\models\Coupon;
use app\models\UserCoupon;

/**
 * @property Coupon $coupon
 */
class CouponEditForm extends Model
{
    public $store_id;
    public $coupon;

    public $name;
    public $discount_type;
    public $min_price;
    public $sub_price;
    public $discount;
    public $expire_type;
    public $expire_day;
    public $begin_time;
    public $end_time;
    public $total_count;
    public $is_join;
    public $sort;

    public function rules()
    {
        return [
            [['name'], 'trim'],
            [['name', 'discount_type', 'min_price', 'sub_price', 'discount', 'expire_type', 'expire_day', 'begin_time', 'end_time'], 'required'],
            [['expire_day','sort'], 'integer', 'min' => 0],
            [['min_price', 'sub_price'], 'number', 'min' => 0,],
            [['discount',], 'number', 'min' => 0.1, 'max' => 10],
            [['total_count'],'number','min'=>-1],
            [['total_count'],'default','value'=>-1],
            [['is_join'],'in','range'=>[1,2]],
            [['sort'],'default','value'=>100]
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '优惠券名称',
            'discount_type' => '优惠券类型',
            'min_price' => '最低消费金额',
            'sub_price' => '优惠金额',
            'discount' => '折扣率',
            'expire_type' => '到期类型',
            'expire_day' => '有效天数',
            'begin_time' => '有效期开始时间',
            'end_time' => '有效期结束时间',
            'total_count' => '发放总数量',
            'is_join' => '加入领券中心',
            'sort' => '排序',
        ];
    }

    public function save()
    {
        if (!$this->validate())
            return $this->getModelError();
        $this->coupon->name = $this->name;
        $this->coupon->discount_type = $this->discount_type;
        $this->coupon->min_price = $this->min_price;
        $this->coupon->sub_price = $this->sub_price;
        $this->coupon->discount = $this->discount;
        $this->coupon->expire_type = $this->expire_type;
        $this->coupon->expire_day = $this->expire_day;
        $this->coupon->begin_time = strtotime($this->begin_time . ' 00:00:00');
        $this->coupon->end_time = strtotime($this->end_time . ' 23:59:59');
        $this->coupon->total_count = $this->total_count;
        $this->coupon->is_join = $this->is_join;
        $this->coupon->sort = $this->sort;
        if ($this->coupon->isNewRecord) {
            $this->coupon->store_id = $this->store_id;
            $this->coupon->addtime = time();
        }else{
            $coupon_count = UserCoupon::find()->where(['store_id'=>$this->store_id,'is_delete'=>0,'coupon_id'=>$this->coupon->id,'type'=>2])->count();
            if($coupon_count > $this->total_count && $this->total_count != -1){
                return [
                    'code'=>1,
                    'msg'=>'优惠券总数不得小于已领取总数'
                ];
            }
        }
        if ($this->coupon->save()) {
            return [
                'code' => 0,
                'msg' => '保存成功',
            ];
        } else {
            return $this->getModelError($this->coupon);
        }
    }
}