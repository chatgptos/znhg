<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/10
 * Time: 10:29
 */

namespace app\modules\mch\controllers;


use app\models\Order;
use app\models\OrderMessage;

class GetDataController extends Controller
{
    /**
     * 获取订单提示列表
     */
    public function actionOrder()
    {
        $order_list = OrderMessage::find()->alias('om')->where([
            'om.store_id' => $this->store->id,
            'om.is_read' => 0,
            'om.is_delete' => 0
        ])->leftJoin(Order::tableName() . ' o', 'o.id=om.order_id')->select([
            'om.id', 'om.addtime', 'o.name','om.is_sound'
        ])->orderBy(['om.addtime' => SORT_DESC])->limit(5)->asArray()->all();
        $id = array();
        foreach ($order_list as $index => $value) {
            $time = time() - $value['addtime'];
            if ($time < 60) {
                $order_list[$index]['time'] = $time . '秒前';
            } else if ($time < 3600) {
                $order_list[$index]['time'] = ceil($time / 60) . '分钟前';
            } else if ($time < 86400) {
                $order_list[$index]['time'] = ceil($time / 3600) . '小时前';
            } else {
                $order_list[$index]['time'] = ceil($time / 86400) . '天前';
            }
            $id[] = $value['id'];
        }
        OrderMessage::updateAll(['is_sound'=>1],['in','id',$id]);
        $this->renderJson([
            'code' => 0,
            'msg' => '',
            'data' => $order_list
        ]);
    }

    /**
     * 删除订单提示
     */
    public function actionMessageDel($id = null)
    {
        OrderMessage::updateAll(['is_read'=>1],['id'=>$id]);
    }
}