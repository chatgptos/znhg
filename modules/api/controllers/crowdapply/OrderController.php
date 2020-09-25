<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/12/14
 * Time: 11:28
 */

namespace app\modules\api\controllers\crowdapply;


use app\models\IntegralLog;
use app\models\Message;
use app\models\Room;
use app\models\User;
use app\modules\api\models\BusinessCommentForm;
use app\modules\api\models\crowdapply\Goods;
use app\modules\api\models\crowdapply\Order;
use app\modules\api\models\crowdapply\OrderForm;
use app\modules\api\models\crowdapply\OrderClerkForm;
use app\modules\api\models\crowdapply\OrderCommentForm;
use app\modules\api\models\crowdapply\OrderCommentPreview;
use app\modules\api\models\crowdapply\OrderListForm;
use app\modules\api\models\crowdapply\OrderPreviewFrom;
use app\modules\api\models\QrcodeForm;
use app\models\Shop;
use app\models\YyGoods;
use app\models\YyOrder;
use app\models\YyOrderForm;
use app\modules\api\behaviors\LoginBehavior;

class OrderController extends Controller
{
    public function behaviors()
    {
//        return array_merge(parent::behaviors(), [
//            'login' => [
//                'class' => LoginBehavior::className(),
//            ],
//        ]);
    }

    /**
     * 订单预览
     */
    public function actionSubmitPreview()
    {
        $form = new OrderPreviewFrom();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;
        $form->goods_id = \Yii::$app->request->get('gid');
        $this->renderJson($form->search());
    }

    /**
     * 订单提交
     */
    public function actionSubmit()
    {
        $form = new OrderPreviewFrom();
        $model = \Yii::$app->request->post();
        $form->attributes = $model;
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;
        $form->goods_id = $model['gid'];
        $form->form_list = json_decode($model['form_list'],true);
        $form->form_id = $model['form_id'];
        $this->renderJson($form->save());
    }

    /**
     * 订单列表
     */
    public function actionList()
    {
        $form = new OrderListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;
        $this->renderJson($form->search());
    }


    /**
     * @param int $id
     * 用户取消
     */
    public function actionCancel($id = 0)
    {
        $order = Order::find()
            ->andWhere([
                'is_delete' => 0,
                'store_id' => $this->store->id,
                'user_id' => \Yii::$app->user->id,
                'is_cancel' => 0,
                'id' => $id,
            ])->one();

        if (!$order){
            $this->renderJson([
                'code'  => 1,
                'msg'   => '订单不存在，或已取消'
            ]);
        }

        $order->is_cancel = 1;

        //库存
        $goods = Goods::find()
            ->andWhere(['id'=>$order->goods_id,'is_delete'=>0,'status'=>1,'store_id'=>$this->store_id])->one();
        if (!$goods){
            return [
                'code'    => 1,
                'msg'     => '商品不存在',
            ];
        }
        $goods->stock ++;

        if ($goods->save() &&$order->save()){
            $this->renderJson([
                'code'  => 0,
                'msg'   => '取消成功'
            ]);
        }else{
            $this->renderJson([
                'code'  => 1,
                'msg'   => '取消失败'
            ]);
        }
    }

    /**
     * @param int $id
     * 订单列表支付按钮
     */
    public function actionPayData($id = 0){
        $form = new OrderPreviewFrom();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;
        $this->renderJson($form->payData($id));
    }

    /**
     * @param int $id
     * 订单详情
     */
    public function actionOrderDetails($id = 0)
    {
        $order = Order::find()
            ->alias('o')
            ->select([
                'o.*',
                'g.name','g.original_price','g.shop_id','g.cover_pic','g.id AS g_id'
            ])
            ->andWhere([
                'o.is_delete' => 0,
                'o.store_id' => 1,
                'o.is_cancel' => 0,
                'o.id' => $id,
            ])
            ->leftJoin(['g'=>Goods::tableName()],'g.id=o.goods_id')
            ->asArray()->one();
        if (!$order){
            $this->renderJson([
                'code'  => 1,
                'msg'   => '订单不存在，或已取消'
            ]);
        }

        $orderForm = OrderForm::find()
            ->andWhere(['store_id'=>$this->store->id,'order_id'=>$order['id']])
            ->select('key,value')
            ->asArray()
            ->all();
        $order['orderForm'] = $orderForm;


        $room_info='';
        $room_info =  Room::findOne(['room_id' => $order['room_id'], 'store_id' => $this->store_id ,'is_delete' =>0]);
        if($room_info){
            $room_info= $room_info->toArray();
        }
        $order['room_info']=$room_info;



        $shopList = [];
        if (!empty($order['shop_id'])){
            $shopId = explode(',',trim($order['shop_id'],','));
            $shopList = Shop::find()
                ->andWhere(['id'=>$shopId[0]])
                ->andWhere(['store_id'=>$this->store_id])
                ->asArray()
                ->all();
            $order['shopListNum'] = count($shopId);
        }else{
            $shopList = Shop::find()
                ->andWhere(['store_id'=>$this->store_id])
                ->asArray()
                ->limit(1)
                ->all();
            $shopListNum = Shop::find()
                ->andWhere(['store_id'=>$this->store_id])
                ->count();
            $order['shopListNum'] = $shopListNum;
        }

        $order['shopList'] = $shopList;
        $order['addtime'] = date('Y-m-d H:i:s',$order['addtime']);
        $this->renderJson([
           'code'   => 0,
           'msg'    => 'success',
           'data'   => $order
        ]);
    }

    /**
     * @param int $id
     * 核销订单详情
     */
    public function actionClerkOrderDetails($id = 0)
    {

        $order = Order::find()
            ->alias('o')
            ->select([
                'o.*',
                'g.name','g.original_price','g.shop_id','g.cover_pic','g.id AS g_id'
            ])
            ->andWhere([
                'o.is_delete' => 0,
                'o.store_id' => $this->store->id,
                'o.is_cancel' => 0,
                'o.id' => $id,
            ])
            ->leftJoin(['g'=>Goods::tableName()],'g.id=o.goods_id')
            ->asArray()->one();


        //登入状态
        //新人增加
        $res=User::updateAll(
            ['parent_id' => $order->user_id,'is_distributor' => 1,'time'=>time(),'integral' => \Yii::$app->user->identity->integral+intval($integral)],
            ['id' => \Yii::$app->user->identity->id]
        );
        //本人
        $user=\Yii::$app->user->identity;
        //如果当前用户登入了但是没有上级
        if(!\Yii::$app->user->identity->parent_id && $user){
            //新增功能 来自智能鲜蜂服务点的订单只要注册了判定没有上级 上级的user
            $user_shop = User::findOne(['shop_id' => $order->user_id, 'store_id' => $this->store_id]);
            //修改当前用户的上级
            //修改上级出错不抛出
            //先简单使用//注册成功一个并且开门赠送1积分，没有上级的
            //后期对接到商城
            $integral='1.00';//赠送积分
            $coupon=2;//赠送券

            //新人增加
            $res=User::updateAll(
                ['parent_id' => $user_shop->id,'is_distributor' => 1,'time'=>time(),'coupon'=>\Yii::$app->user->identity->coupon+$coupon,'integral' => \Yii::$app->user->identity->integral+intval($integral)],
                ['id' => \Yii::$app->user->identity->id]
            );
            //因为柜机不能增加所以增加 同样多积分
            $res=User::updateAll(
                ['integral' => $user_shop->integral+intval($integral)],
                ['id' => $user_shop->id]
            );


            //上级
            $user_1=$user_shop;
            //本人
            $user=\Yii::$app->user->identity;

            //积分日志增加
            $Message = new Message();
            $Message->user_id = $user_1->id;
            $Message->content = "智能鲜蜂服务点自动推荐".$user->nickname."成为你用户此次消费奖励：" . $integral . " 积分（已到账）";
            $Message->integral = $integral;
            $Message->addtime = time();
            $Message->username = $user_1->nickname;
            $Message->operator = 'huogui';
            $Message->store_id = $this->store->id;
            $Message->operator_id = 0;
            $Message->save();

            //积分日志增加 新人用户端
            $Message = new Message();
            $Message->user_id = $user->id;
            $Message->content = "开门即富贵".$user->nickname."奖励：" . $integral . " 积分（可提现）". $coupon . "券（可卖出)"."进入券池抢红包奖励100%,10秒过期";
            $Message->integral = $integral;
            $Message->coupon = $coupon;
            $Message->addtime = time();
            $Message->username = $user->nickname;
            $Message->operator = 'huogui';
            $Message->store_id = $this->store->id;
            $Message->operator_id = 0;
            $Message->save();


            //积分日志增加
            $integralLog = new IntegralLog();
            $integralLog->user_id = $user_1->id;
            $integralLog->content = "智能鲜蜂服务点自动推荐".$user->nickname."成为你用户此次消费奖励：" . $integral . " 积分（已到账）";
            $integralLog->integral = intval($integral);
            $integralLog->addtime = time();
            $integralLog->username = $user_1->nickname;
            $integralLog->operator = 'huogui';
            $integralLog->store_id = $this->store->id;
            $integralLog->operator_id = 0;
            $integralLog->save();

            //新人增加积分 //优惠券消息 后台
            $integralLog = new IntegralLog();
            $integralLog->user_id = $user->id;
            $integralLog->content = "开门即富贵".$user->nickname."奖励" . $integral . " 积分（可提现）".$coupon."券（可卖出)";
            $integralLog->integral = intval($integral);
            $integralLog->coupon = intval($coupon);
            $integralLog->addtime = time();
            $integralLog->username = $user->nickname;
            $integralLog->operator = 'huogui';
            $integralLog->store_id = $this->store->id;
            $integralLog->operator_id = 0;
            $integralLog->save();

            //创建券池 2个券
            $form = new BusinessCommentForm();
            $form->store_id = $this->store->id;
            $form->user_id = \Yii::$app->user->id;
            $form->num = 1;
            $form->is_hg = 1;//是智能鲜蜂服务点 智能鲜蜂服务点表象
            $res=$form->add();
            $form = new BusinessCommentForm();
            $form->store_id = $this->store->id;
            $form->user_id = \Yii::$app->user->id;
            $form->num = 1;
            $form->is_hg = 2;//是智能鲜蜂服务点  智能鲜蜂服务点内页
            $res=$form->add();

        }

        $this->renderJson([
            'code'   => 0,
            'msg'    => 'success',
            'data'   => $order
        ]);

        $order = Order::find()
            ->alias('o')
            ->select([
                'o.*',
                'g.name','g.original_price','g.shop_id','g.cover_pic','g.id AS g_id'
            ])
            ->andWhere([
                'o.is_delete' => 0,
                'o.store_id' => $this->store->id,
                'o.is_cancel' => 0,
                'o.id' => $id,
            ])
            ->leftJoin(['g'=>Goods::tableName()],'g.id=o.goods_id')
            ->asArray()->one();
        if (!$order){
            $this->renderJson([
                'code'  => 1,
                'msg'   => '订单不存在，或已取消'
            ]);
        }

        $orderForm = OrderForm::find()
            ->andWhere(['store_id'=>$this->store->id,'order_id'=>$order['id']])
            ->select('key,value')
            ->asArray()
            ->all();
        $order['orderForm'] = $orderForm;
        $shopList = [];
        if (!empty($order['shop_id'])){
            $shopId = explode(',',trim($order['shop_id'],','));
            $shopList = Shop::find()
                ->andWhere(['id'=>$shopId[0]])
                ->andWhere(['store_id'=>$this->store_id])
                ->asArray()
                ->all();
            $order['shopListNum'] = count($shopId);
        }else{
            $shopList = Shop::find()
                ->andWhere(['store_id'=>$this->store_id])
                ->asArray()
                ->limit(1)
                ->all();
            $shopListNum = Shop::find()
                ->andWhere(['store_id'=>$this->store_id])
                ->count();
            $order['shopListNum'] = $shopListNum;
        }

        $order['shopList'] = $shopList;
        $order['addtime'] = date('Y-m-d H:i:s',$order['addtime']);
        $this->renderJson([
            'code'   => 0,
            'msg'    => 'success',
            'data'   => $order
        ]);
    }


    /**
     * @param int $id
     * 核销订单详情
     */
    public function actionClerkOrderDetails1($id = 0)
    {
        $order = Order::find()
            ->alias('o')
            ->select([
                'o.*',
                'g.name','g.original_price','g.shop_id','g.cover_pic','g.id AS g_id'
            ])
            ->andWhere([
                'o.is_delete' => 0,
                'o.store_id' => $this->store->id,
                'o.is_cancel' => 0,
                'o.id' => $id,
            ])
            ->leftJoin(['g'=>Goods::tableName()],'g.id=o.goods_id')
            ->asArray()->one();
        if (!$order){
            $this->renderJson([
                'code'  => 1,
                'msg'   => '订单不存在，或已取消'
            ]);
        }

        $orderForm = OrderForm::find()
            ->andWhere(['store_id'=>$this->store->id,'order_id'=>$order['id']])
            ->select('key,value')
            ->asArray()
            ->all();
        $order['orderForm'] = $orderForm;
        $shopList = [];
        if (!empty($order['shop_id'])){
            $shopId = explode(',',trim($order['shop_id'],','));
            $shopList = Shop::find()
                ->andWhere(['id'=>$shopId[0]])
                ->andWhere(['store_id'=>$this->store_id])
                ->asArray()
                ->all();
            $order['shopListNum'] = count($shopId);
        }else{
            $shopList = Shop::find()
                ->andWhere(['store_id'=>$this->store_id])
                ->asArray()
                ->limit(1)
                ->all();
            $shopListNum = Shop::find()
                ->andWhere(['store_id'=>$this->store_id])
                ->count();
            $order['shopListNum'] = $shopListNum;
        }

        $order['shopList'] = $shopList;
        $order['addtime'] = date('Y-m-d H:i:s',$order['addtime']);
        $this->renderJson([
            'code'   => 0,
            'msg'    => 'success',
            'data'   => $order
        ]);
    }


    /**
     * @return mixed|string
     * 核销订单二维码
     */
    public function actionGetQrcode()
    {

        $haibao = \Yii::$app->request->get('haibao');

        if(empty($haibao)){
            $this->renderJson([
                'code' => 0,
                'msg' => 'success',
                'data' => [
                    'url' => 'http://airent-hospital.oss-cn-beijing.aliyuncs.com/uploads/image/2d/2de65aa459924541f436dfdf510b5b2f.png',
                ],
            ]);
        }

        $order_no = \Yii::$app->request->get('order_no');
        $order = Order::findOne(['order_no'=>$order_no,'store_id'=>$this->store->id]);

        $form = new QrcodeForm();
        $form->data = [
            'scene'=>"{$order->id}",
            'page'=>"pages/crowdapply/order/details",
            'width'=>100
        ];
        $form->store = $this->store;
        $res = $form->getQrcode();

        return json_encode($res,JSON_UNESCAPED_UNICODE);
    }

    /**
     * 核销订单
     */
    public function actionClerk()
    {
        $form = new OrderClerkForm();
        $form->order_id = \Yii::$app->request->get('order_id');
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;
        $this->renderJson($form->save());
    }

    /**
     * 用户申请退款
     */
    public function actionApplyRefund()
    {
        $order_id = \Yii::$app->request->get('order_id');
        $order = Order::find()
            ->andWhere([
                'id'            => $order_id,
                'is_delete'     => 0,
                'store_id'      => $this->store->id,
                'user_id'       => \Yii::$app->user->id,
                'is_pay'        => 1,
                'is_refund'     => 0,
                'apply_delete'     => 0,
            ])
            ->one();
        if (!$order){
            $this->renderJson([
                'code'  => 1,
                'msg'   => '订单错误'
            ]);
        }
        if ($order->pay_price >= 0.01){
            $order->apply_delete = 1;
        }else{
            $order->apply_delete = 1;
            $order->is_refund = 1;
        }
        if ($order->save()){
            $this->renderJson([
                'code'  => 0,
                'msg'   => '退款申请成功',
            ]);
        }else{
            $this->renderJson([
                'code'  => 1,
                'msg'   => '退款申请失败,请重试'
            ]);
        }

    }

    /**
     * 评论预览页面
     */
    public function actionCommentPreview()
    {
        $form = new OrderCommentPreview();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;
        $this->renderJson($form->search());
    }

    /**
     * 订单评论提交
     */
    public function actionComment()
    {
        $form = new OrderCommentForm();
        $form->attributes = \Yii::$app->request->post();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;
        $this->renderJson($form->save());
    }




}