<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/9/21
 * Time: 11:06
 */

namespace app\modules\api\models;


use app\models\Coupon;
use app\models\UserCoupon;

class CouponListForm extends Model
{
    public $store_id;
    public $user_id;

    public function getList()
    {
        if(!$this->user_id){
            $this->user_id = 0;
        }
        $coupon_list = Coupon::find()->alias('c')->where([
            'c.is_delete' => 0, 'c.is_join' => 2, 'c.store_id' => $this->store_id
        ])
            ->andWhere(['!=', 'c.total_count', 0])
            ->leftJoin(UserCoupon::tableName() . ' uc', "uc.coupon_id=c.id and uc.user_id ={$this->user_id} and uc.type = 2 and uc.is_delete=0")->select([
                'c.*', '(case when isnull(uc.id) then 0 else 1 end) as is_receive'
            ])->orderBy('is_receive ASC,sort ASC')->asArray()->all();
        $new_list = [];
        foreach($coupon_list as $index=>$value){
            if($value['min_price'] >= 100){
                $coupon_list[$index]['min_price'] = (int)$value['min_price'];
            }
            if($value['sub_price'] >= 100){
                $coupon_list[$index]['sub_price'] = (int)$value['sub_price'];
            }
            $coupon_list[$index]['begintime'] = date('Y.m.d',$value['begin_time']);
            $coupon_list[$index]['endtime'] = date('Y.m.d',$value['end_time']);
            $coupon_count = UserCoupon::find()->where([
                'store_id'=>$this->store_id,'is_delete'=>0,'coupon_id'=>$value['id'],'type'=>2
            ])->count();
            if($value['total_count'] > $coupon_count || $value['total_count'] == -1){
                if($value['expire_type'] == 2){
                    if($value['end_time'] >= time()){
                        $new_list[] = $coupon_list[$index];
                    }
                }else{
                    $new_list[] = $coupon_list[$index];
                }
            }
        }
        return $new_list;
    }
}