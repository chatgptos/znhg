<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/25
 * Time: 14:39
 */

namespace app\modules\api\models;


use app\models\Coupon;
use app\models\CouponAutoSend;
use app\models\UserCoupon;

class CouponIndexForm extends Model
{
    public $store_id;
    public $user_id;
    public $status;

    public function rules()
    {
        return [
            [['status'], 'integer'],
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            return $this->getModelError();
        }
        $query = UserCoupon::find()->alias('uc')->leftJoin(['c' => Coupon::tableName()], 'uc.coupon_id=c.id')->leftJoin(['cas' => CouponAutoSend::tableName()], 'cas.id=uc.coupon_auto_send_id')
            ->where(['uc.user_id' => $this->user_id]);
        if ($this->status == 0) {
            $query->andWhere([
                'uc.is_delete' => 0,
                'uc.is_use' => 0,
                'uc.is_expire' => 0,
            ]);
        }
        if ($this->status == 1) {
            $query->andWhere([
                'uc.is_delete' => 0,
                'uc.is_use' => 1,
                'uc.is_expire' => 0,
            ]);
        }
        if ($this->status == 2) {
            $query->andWhere([
                'uc.is_delete' => 0,
                'uc.is_use' => 0,
                'uc.is_expire' => 1,
            ]);
        }
        $list = $query->orderBy('uc.addtime DESC')
            ->limit(200)
            ->select('uc.id user_coupon_id,c.sub_price,c.min_price,uc.begin_time,uc.end_time,uc.is_use,uc.is_expire,cas.event,uc.type')->asArray()->all();

        $events = [
            0 => '平台发放',
            1 => '分享红包',
            2 => '购物返券',
            3 => '领券中心'
        ];
        foreach ($list as $i => $item) {
            $list[$i]['status'] = 0;
            if (isset($item['is_use']))
                $list[$i]['status'] = 1;
            if (isset($item['is_expire']))
                $list[$i]['status'] = 2;
            $list[$i]['min_price_desc'] = $item['min_price'] == 0 ? '无门槛' : '满' . $item['min_price'] . '元可用';
            $list[$i]['begin_time'] = date('Y.m.d H:i', $item['begin_time']);
            $list[$i]['end_time'] = date('Y.m.d H:i', $item['end_time']);
            if (!$item['event']) {
                if ($item['type'] == 2) {
                    $list[$i]['event'] = $item['event'] = 3;
                } else {
                    $list[$i]['event'] = $item['event'] = 0;
                }
            }
            $list[$i]['event_desc'] = $events[$item['event']];
        }
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'list' => $list,
            ],
        ];

    }
}