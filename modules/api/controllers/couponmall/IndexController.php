<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/24
 * Time: 16:41
 */

namespace app\modules\api\controllers\couponmall;

use app\extensions\HuoGui;
use app\extensions\WxPayScore;
use app\extensions\WxPayScoreOrder;
use app\models\IntegralLog;
use app\models\Message;
use app\models\Shop;
use app\models\User;
use app\modules\api\models\BusinessCommentForm;
use app\modules\api\models\couponmall\Cat;
use app\modules\api\models\couponmall\Setting;
use app\modules\api\models\couponmall\CommentListForm;
use app\modules\api\models\couponmall\GoodsQrcodeForm;
use app\modules\api\models\couponmall\ShopListForm;
use app\modules\api\models\couponmall\GoodsForm;
use app\modules\api\models\ShopForm;

/**
 * Class IndexController
 * @package app\modules\api\controller\group
 * 预约首页模块
 */
class IndexController extends Controller
{
    /**
     * @return mixed|string
     * 预约首页
     */
    public function actionIndex()
    {
        // 获取导航分类
        $cat = Cat::find()
            ->select('id,name')
            ->andWhere(['is_delete'=>0,'store_id'=>$this->store_id])
            ->orderBy('sort ASC')
            ->asArray()
            ->all();
//        $ad = Option::get('pt_ad', $this->store_id);
        $yyGoods = new GoodsForm();
        $yyGoods->store_id = $this->store_id;
        $yyGoods->user_id = \Yii::$app->user->id;
        $goods = $yyGoods->getList();
        $catShow = Setting::findOne(['store_id'=>$this->store_id]);
        return json_encode([
            'code'  => 0,
            'msg'   => 'success',
            'data'  => [
                'cat'     => $cat,
                'goods'   => $goods,
                'cat_show'   => $catShow->cat,
            ],
        ],JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return mixed|string
     * 数据加载
     */
    public function actionGoodList()
    {
        $yyGoods = new GoodsForm();
        $yyGoods->store_id = $this->store_id;
        $yyGoods->user_id = \Yii::$app->user->id;
        $goods = $yyGoods->getList();
        return json_encode([
            'code'  => 0,
            'msg'   => 'success',
            'data'  => $goods,
        ],JSON_UNESCAPED_UNICODE);
    }



    /**
     * @return mixed|string
     * 数据加载
     */
    public function actionOpendoor()
    {

//
//            $WxPayScoreOrder = new WxPayScoreOrder();
//            $res= $WxPayScoreOrder->cancel('WxScorePay2020031901194396762');//补货开门
//        var_dump($res);die;
//die;
//        echo '<hr>openDoor';
//        $biz_content=array(
//            "deviceId"=>100023,//必须要有设备
//            "unionid"=>\Yii::$app->user->identity->wechat_open_id,
//            "unionid"=>'ogZOL5WMC-2U32i-S5AfnzrGuM5k',
//            "orderNo"=>"2020032550610481",
//        );
//        $HuoGui = new HuoGui();
//
//        $res= $HuoGui->completeOrder($biz_content);
//        var_dump($res);
//die;

        $yyGoods = new GoodsForm();
        $yyGoods->store_id = $this->store_id;
        $yyGoods->user_id = \Yii::$app->user->id;
        $goods = $yyGoods->getList();


        $hg_id = \Yii::$app->request->post('hg_id');
        $isreplenish = \Yii::$app->request->post('isreplenish');
        if(!$hg_id){
            return json_encode([
                'code'  => '1',
                'msg'   => '货柜不存在',
                'success'   => false,
                'data'  => '货柜不存在',
            ],JSON_UNESCAPED_UNICODE);
        }

        if (!\Yii::$app->user->id){
            return json_encode([
                'code'  => '3',
                'msg'   => '请登入',
                'success'   => false,
                'data'  => '请登入',
            ],JSON_UNESCAPED_UNICODE);
        }


        //查看是否配置
        $shop = Shop::findOne(['hg_id' => $hg_id, 'store_id' => $this->store->id, 'is_delete' => 0]);
        if (!$shop) {
            return json_encode([
                'code'  => '1',
                'msg'   => '该货柜没有还未人抢购，未配置',
                'success'   => false,
                'data'  => '该货柜没有还未人抢购，未配置',
            ],JSON_UNESCAPED_UNICODE);
        }

        $HuoGui = new HuoGui();
        $biz_content=array(
            "deviceId"=>$hg_id,//必须要有设备
            "unionid"=>\Yii::$app->user->identity->wechat_open_id,
            "nickName"=>\Yii::$app->user->identity->nickname,
            "avatar"=>\Yii::$app->user->identity->avatar_url,
        );
        //如果是补货人员 并且是补货申请
        if(\Yii::$app->user->identity->is_clerk && !empty($isreplenish)){
            //如果是当前货柜
            $shop_id = \Yii::$app->user->identity->shop_id;
            $form = new ShopForm();
            $form->store_id = $this->store->id;
            $form->user = \Yii::$app->user->identity;
            $form->shop_id = $shop_id;
            $shop=$form->search();

            //如果柜子存在
            if(empty($shop['code']) && $shop['success']){
                //如果货柜编号相等//调用补货接口开门
                if(!isset($shop['data']['shop']['hg_id']) || $hg_id != $shop['data']['shop']['hg_id']){
                    return json_encode([
                        'code'  => '1',
                        'msg'   => '不是您的货柜',
                        'success'   => false,
                        'data'  => '不是您的货柜',
                    ],JSON_UNESCAPED_UNICODE);
                }
                //开始调用货柜补货
                unset($biz_content['unionid']);
                //不用同步用户信息
                $res= $HuoGui->replenish($biz_content);
                if ($res['success'] && isset($res['data']['opendoorRecordId'])){
                    return json_encode([
                        'code'  => 0,
                        'msg'   => '成功开门',
                        'success'   => true,
                        'data'  => array(
                            'isOpen'=>true,
                            'isreplenish'=>true,
                            'opendoorRecordId'=>$res['data']['opendoorRecordId'],
                        ),
                    ],JSON_UNESCAPED_UNICODE);
                }else{
                    return json_encode([
                        'code'  => 1,
                        'msg'   => '升级中或有未完结订单',
                        'success'   => false,
                        'data'  =>array(
                            'isOpen'=>false,
                        ),
                    ],JSON_UNESCAPED_UNICODE);
                }
            }else{
                return json_encode([
                    'code'  => '1',
                    'msg'   => '不是您的货柜',
                    'success'   => false,
                    'data'  => '不是您的货柜',
                ],JSON_UNESCAPED_UNICODE);
            }
        }

        $res= $HuoGui->syncUserInfo($biz_content);
        if ($res['msg']=='用户已注册过了' || $res['success']==true){ //同步用户信息给用户开门权限


            //新增功能 来自货柜的订单只要注册了判定没有上级 上级的user
            $user_shop = User::findOne(['shop_id' => $shop->id, 'store_id' => $this->store_id]);
            //修改当前用户的上级

            if(!\Yii::$app->user->identity->parent_id){
                //修改上级出错不抛出
                //先简单使用//注册成功一个并且开门赠送1积分，没有上级的
                //后期对接到商城
                $integral='1.00';//赠送积分
                $coupon=2;//赠送券

                $res=User::updateAll(
                    ['parent_id' => $user_shop->id,'is_distributor' => 1,'time'=>time(),'coupon'=>\Yii::$app->user->identity->coupon+$coupon,'integral' => \Yii::$app->user->identity->integral+intval($integral)],
                    ['id' => \Yii::$app->user->identity->id]
                );
                //上级  本人
                $user_1=$user_shop;
                $user=\Yii::$app->user->identity;

                //积分日志增加
                $Message = new Message();
                $Message->user_id = $user_1->id;
                $Message->content = "货柜自动推荐".$user->nickname."成为你用户此次消费奖励：" . $integral . " 积分（已到账）";
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
                $integralLog->content = "货柜自动推荐".$user->nickname."成为你用户此次消费奖励：" . $integral . " 积分（已到账）";
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
                $form->is_hg = 1;//是货柜 货柜表象
                $res=$form->add();
                $form = new BusinessCommentForm();
                $form->store_id = $this->store->id;
                $form->user_id = \Yii::$app->user->id;
                $form->num = 1;
                $form->is_hg = 2;//是货柜  货柜内页
                $res=$form->add();

            }

//            $res=User::updateAll(
//                ['parent_id' => 0,'is_distributor' => 1,'time'=>time(),
////                    'integral' => \Yii::$app->user->identity->integral+1
//                ],
//                ['id' => \Yii::$app->user->identity->id]
//            );
//            var_dump( \Yii::$app->user->identity->parent_id);
//            var_dump( \Yii::$app->user->identity->integral);
//            var_dump($res);
//            die;

            $WxPayScoreOrder = new WxPayScoreOrder();
            $res= $WxPayScoreOrder->userServiceState(\Yii::$app->user->identity->wechat_open_id);//购买开门
            $res = json_decode($res,true);
            if(isset($res['use_service_state']) && $res['use_service_state'] == 'AVAILABLE'){
                //有授权
                $res= $HuoGui->openDoor($biz_content);
                if ($res['success'] && isset($res['data']['opendoorRecordId'])){
                    //立即生成订单阻止 恶意取消授权的货损
                    $order_no=$this->getOrderNoWxPay();
                    //创建订单
                    $WxPayScoreOrder = new WxPayScoreOrder();
                    $out_order_no=$order_no;
                    $resScoreOrder= $WxPayScoreOrder->queryOrder($out_order_no);//下单
                    $resScoreOrder =json_decode($resScoreOrder,true);
                    //如果没有订单 继续创建订单
                    if(!isset($resScoreOrder['out_order_no']) || !$resScoreOrder['out_order_no']){
                        //不存在订单创建
                        $resScoreOrder= $WxPayScoreOrder->serviceorder($out_order_no,'',$shop->address);//补货开门
                        $resScoreOrder =json_decode($resScoreOrder,true);
                        if(!isset($resScoreOrder['out_order_no']) || !$resScoreOrder['out_order_no']){
                            // 存在直接返回订单号
                            return json_encode([
                                'code'  => 1,
                                'msg'   => '开门失败未创建订单',
                                'success'   => false,
                                'data'  =>array(
                                    'isOpen'=>false,
                                    'isreplenish'=>false,
                                    'out_order_no'=>$out_order_no,
                                    'opendoorRecordId'=>$res['data']['opendoorRecordId'],
                                ),
                            ],JSON_UNESCAPED_UNICODE);
                        }
                    }
                    return json_encode([
                        'code'  => 0,
                        'msg'   => '成功开门',
                        'success'   => true,
                        'data'  => array(
                            'isOpen'=>true,
                            'isreplenish'=>false,
                            'out_order_no'=>$out_order_no,
                            'opendoorRecordId'=>$res['data']['opendoorRecordId'],
                        ),
                    ],JSON_UNESCAPED_UNICODE);
                }else{
                    return json_encode([
                        'code'  => 1,
                        'msg'   => '升级中或有未完结订单',
                        'success'   => false,
                        'data'  =>array(
                            'isOpen'=>false,
                        ),
                    ],JSON_UNESCAPED_UNICODE);
                }
            }else{
                //没有授权，开始授权
                $out_request_no=$WxPayScoreOrder->getOrderNoWx();
                $pay_data= $WxPayScoreOrder->wxpayScoreEnable($out_request_no);//获得微信分授权参数
                return json_encode([
                    'code' => 0,
                    'msg' => 'success',
                    'success'   => true,
                    'data' => array(
                        'isOpen'=>false,
                        'isreplenish'=>false,
                        'data'=>$pay_data,
                    ),
                ],JSON_UNESCAPED_UNICODE);
            }

        }else{
            return json_encode([
                'code'  => 1,
                'msg'   => '同步用户数据中',
                'success'   => false,
                'data'  =>array(
                    'isOpen'=>false,
                ),
            ],JSON_UNESCAPED_UNICODE);

        }
    }

    /**
     * @return null|string
     * 生成订单号
     */
    public function getOrderNoWxPay()
    {
        $order_no = null;
        $order_no = 'WxScorePay'.date('YmdHis') . rand(10000, 99999);
        return $order_no;
    }


    /**
     * @return mixed|string
     * 数据加载
     */
    public function actionGoodListhg()
    {

        $hg_id = \Yii::$app->request->post('hg_id');
        $isreplenish = \Yii::$app->request->post('isreplenish');
        if(!$hg_id){
            $new_list=[];
        }else{
            $HuoGui = new HuoGui();
            $biz_content=array(
                "deviceId"=>$hg_id,//必须要有设备
                "unionid"=>\Yii::$app->user->identity->wechat_open_id,
            );
            $res= $HuoGui->getDeviceGoods($biz_content);
            $goods=[];
            if ($res['success']==true){
                $data=$res['data'];
                $goods=$data;
            }

            $new_list = [];
            foreach ($goods as $item) {
                $attr_list[] = [
                    'attr_group_name'=>'来源',
                    'attr_name'=>'智能货柜',
                ];
                $attr_num = 99;
                $num =$item['count'];
                $goods_id =$item['id'];
                $goods_pic =$item['imgUrl'];
                $goods_name =$item['goodsName'];
                $new_item = (object)[
                    'cart_id' => $item['categoryId'],
                    'goods_id' =>$item['goodsId'],
                    'goods_pic' => $goods_pic,
                    'num' =>$num,
                    'attr_list' => $attr_list,
                    'price' =>$item['price'],
                    'max_num' => $attr_num,
                    'disabled' => ($num > $attr_num) ? true : false,
                    'cover_pic'=>$goods_pic,
                    'goods_id'=>$goods_id,
                    'id'=>$goods_id,
                    'goods_name'=>$goods_name,
                    'integral'=>$item['price'],
                    'coupon'=>$item['discount'],
                ];

                $new_list[] = $new_item;
            }
        }

        $yyGoods = new GoodsForm();
        $yyGoods->store_id = $this->store_id;
        $yyGoods->user_id = \Yii::$app->user->id;
        $goods = $yyGoods->getList();
        $goods['list'] = array_merge_recursive($goods['list'],$new_list);
        $goods['row_count']=count($goods);

        return json_encode([
            'code'  => 0,
            'msg'   => 'success',
            'data'  => $goods,
        ],JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param int $gid
     * @return mixed|string
     * 商品详情
     */
    public function actionGoodDetails($gid = 0)
    {
        $ptGoods = new GoodsForm();
        $ptGoods->store_id = $this->store_id;
        $ptGoods->gid = $gid;
        $ptGoods->user_id = \Yii::$app->user->id;
        return json_encode($ptGoods->getInfo(),JSON_UNESCAPED_UNICODE);
    }

    /**
     * 货柜列表
     */
    public function actionShopList()
    {
        $ids = \Yii::$app->request->get('ids');
        $form = new ShopListForm();
        $form->store_id = $this->store->id;
        $form->user = \Yii::$app->user->identity;
        $form->ids = $ids;
        $form->attributes = \Yii::$app->request->get();
        $this->renderJson($form->search());
    }

    /**
     * 商品评价
     */
    public function actionGoodsComment()
    {
        $form = new CommentListForm();
        $form->attributes = \Yii::$app->request->get();
        $this->renderJson($form->search());
    }

    //获取商品二维码海报
    public function actionGoodsQrcode()
    {
        $form = new GoodsQrcodeForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store_id;
        if (!\Yii::$app->user->isGuest) {
            $form->user_id = \Yii::$app->user->id;
        }
        return $this->renderJson($form->search());
    }




//    public function actionGoodsAttrInfo()
//    {
//        $form = new PtGoodsAttrInfoForm();
//        $form->attributes = \Yii::$app->request->get();
//        $this->renderJson($form->search());
//    }

}