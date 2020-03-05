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
use app\models\Shop;
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

        $HuoGui = new HuoGui();
        $biz_content=array(
            "deviceId"=>$hg_id,//必须要有设备
            "unionid"=>\Yii::$app->user->identity->wechat_open_id,
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
                $hg_id=$hg_id;

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
                        'msg'   => '升级中',
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
            $WxPayScoreOrder = new WxPayScoreOrder();
            $res= $WxPayScoreOrder->userServiceState(\Yii::$app->user->identity->wechat_open_id);//补货开门
            $res = json_decode($res,true);
            if(isset($res['use_service_state']) && $res['use_service_state'] == 'AVAILABLE'){
                //有授权
                $res= $HuoGui->openDoor($biz_content);
                if ($res['success'] && isset($res['data']['opendoorRecordId'])){
                    return json_encode([
                        'code'  => 0,
                        'msg'   => '成功开门',
                        'success'   => true,
                        'data'  => array(
                            'isOpen'=>true,
                            'isreplenish'=>false,
                            'opendoorRecordId'=>$res['data']['opendoorRecordId'],
                        ),
                    ],JSON_UNESCAPED_UNICODE);
                }else{
                    return json_encode([
                        'code'  => 1,
                        'msg'   => '升级中',
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
     * @return mixed|string
     * 数据加载
     */
    public function actionGoodListhg()
    {

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

        $HuoGui = new HuoGui();
        $biz_content=array(
            "deviceId"=>$hg_id,//必须要有设备
            "unionid"=>\Yii::$app->user->identity->wechat_open_id,
        );
        $goods= $HuoGui->getDeviceGoods($biz_content);
        $goods='{
    "msg":"",
    "code":200,
    "success":true,
    "data":[
        {
            "id":2772,
            "goodsName":"脉动",
            "imgUrl":"http://images.voidiot.com/Fk6gC_mxXKKv-6RWGbFoFP9NzRVi",
            "price":4,
            "baseWeight":644,
            "count":0,
            "weight":20,
            "valuatType":0,
            "deviceId":100023,
            "date":null,
            "trayNum":1,
            "status":1,
            "merchantId":10015,
            "goodsId":1024,
            "createTime":"2019-08-29 11:57",
            "updateTime":"2020-01-08 09:55",
            "ch1":null,
            "ch2":null,
            "sourPrice":0,
            "discount":0,
            "avgWeight":30,
            "categoryId":1000
        },
        {
            "id":2791,
            "goodsName":"景田",
            "imgUrl":"http://images.voidiot.com/FlPGbw1lBclUdP6MU5Orz1OExgff",
            "price":2,
            "baseWeight":600,
            "count":0,
            "weight":20,
            "valuatType":0,
            "deviceId":100023,
            "date":"20190829",
            "trayNum":2,
            "status":1,
            "merchantId":10015,
            "goodsId":1026,
            "createTime":"2019-08-29 11:57",
            "updateTime":"2019-12-26 12:34",
            "ch1":null,
            "ch2":null,
            "sourPrice":0,
            "discount":0,
            "avgWeight":30,
            "categoryId":null
        }
    ],
    "fail":false
}';

        $res =json_decode($goods,true);
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