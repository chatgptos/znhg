<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/25
 * Time: 17:01
 */

namespace app\behaviors;


use app\models\UserCoupon;
use yii\base\Behavior;
use yii\web\Controller;

/**
 * 处理过期优惠券
 */
class CouponBehavior extends Behavior
{
    public $only_routes = [
        'mch/coupon/*',
        'api/order/submit-preview',
        'api/order/submit',
        'api/order/submit',
        'api/coupon/*',
    ];

    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction',
        ];
    }

    /**
     * @param \yii\base\ActionEvent $e
     */
    public function beforeAction($e)
    {
        if (DoNotDoIt::doNotDoIt($this->only_routes))
            return;
        \Yii::warning('----COUPON BEHAVIOR----');
        if (empty($e->action->controller->store))
            return;
        $store_id = $e->action->controller->store->id;
        UserCoupon::updateAll(['is_expire' => 1], [
            'AND',
            ['store_id' => $store_id,],
            ['is_use' => 0,],
            ['is_expire' => 0,],
            ['is_delete' => 0,],
            ['<', 'end_time', time()],
        ]);
    }
}