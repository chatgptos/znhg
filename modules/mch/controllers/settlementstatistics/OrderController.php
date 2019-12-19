<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/12/7
 * Time: 20:01
 */

namespace app\modules\mch\controllers\settlementstatistics;
use app\models\Express;
use app\models\PtOrder;
use app\modules\mch\models\settlementstatistics\OrderSendForm;
use app\modules\mch\models\settlementstatistics\PtOrderForm;
use app\modules\mch\models\settlementstatistics\PtPrintForm;

/**
 * Class OrderController
 * @package app\modules\mch\controllers\settlementstatistics
 * 订单列表
 */
class OrderController extends Controller
{
    /**
     * @return string
     * 拼团订单列表
     */
    public function actionIndex()
    {
        $form = new PtOrderForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $arr = $form->getList();

        return $this->render('index',[
            'list'      => $arr['list'],
            'pagination'=> $arr['p'],
            'express_list' => $this->getExpressList(),
            'row_count' => $arr['row_count'],
        ]);
    }

    //订单发货
    public function actionSend()
    {
        $form = new OrderSendForm();
        $post = \Yii::$app->request->post();
        if ($post['is_express'] == 1) {
            $form->scenario = 'EXPRESS';
        }
        $form->attributes = $post;
        $form->store_id = $this->store->id;
        $this->renderJson($form->save());
    }

    // 面单打印
    public function actionPrint()
    {
        $id = \Yii::$app->request->get('id');
        $express = \Yii::$app->request->get('express');
        $post_code = \Yii::$app->request->get('post_code');
        $form = new PtPrintForm();
        $form->store_id = $this->store->id;
        $form->order_id = $id;
        $form->express = $express;
        $form->post_code = $post_code;
        return json_encode($form->send(), JSON_UNESCAPED_UNICODE);
    }

    // 快递列表
    private function getExpressList()
    {
        $store_express_list = PtOrder::find()
            ->select('express')
            ->where([
                'AND',
                ['store_id' => $this->store->id],
                ['is_send' => 1],
                ['!=', 'express', ''],
            ])->settlementstatisticsBy('express')->orderBy('send_time DESC')->limit(5)->asArray()->all();
        $express_list = Express::find()->select('name AS express')->orderBy('sort ASC')->asArray()->all();
        $new_store_express_list = [];
        foreach ($store_express_list as $i => $item)
            $new_store_express_list[] = $item['express'];

        $new_public_express_list = [];
        foreach ($express_list as $i => $item)
            $new_public_express_list[] = $item['express'];
        return [
            'private' => $new_store_express_list,
            'public' => $new_public_express_list,
        ];
    }

    /**
     * @return string
     * 拼团订单
     */
    public function actionsettlementstatistics()
    {
        $form = new PtOrderForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $arr = $form->getsettlementstatisticsList();
        return $this->render('settlementstatistics',[
            'list'      => $arr['list'],
            'pagination'=> $arr['p'],
            'row_count' => $arr['row_count'],
        ]);
    }


    public function actionsettlementstatisticsList()
    {
        $form = new PtOrderForm();
        $form->store_id = $this->store->id;
        $pid = \Yii::$app->request->get('pid');
        $arr = $form->getsettlementstatisticsInfo($pid);

        return $this->render('settlementstatistics-list',[
            'list'      => $arr['list'],
            'pagination'=> $arr['p'],
            'express_list' => $this->getExpressList(),
            'row_count' => $arr['row_count'],
        ]);
    }



}