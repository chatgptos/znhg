<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/24
 * Time: 16:41
 */

namespace app\modules\api\controllers\couponmall;

use app\extensions\HuoGui;
use app\modules\api\models\couponmall\Cat;
use app\modules\api\models\couponmall\Setting;
use app\modules\api\models\couponmall\CommentListForm;
use app\modules\api\models\couponmall\GoodsQrcodeForm;
use app\modules\api\models\couponmall\ShopListForm;
use app\modules\api\models\couponmall\GoodsForm;

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
            "deviceId"=>100073,//必须要有设备
            "unionid"=>"1353817841",
        );

        $res= $HuoGui->syncUserInfo($biz_content);
        if ($res['msg']=='用户已注册过了'||$res['success']==true){
            $res= $HuoGui->openDoor($biz_content);
            if ($res['success']){
                return json_encode([
                    'code'  => 0,
                    'msg'   => '成功开门',
                    'success'   => true,
                    'data'  => '成功开门',
                ],JSON_UNESCAPED_UNICODE);
            }else{
                return json_encode([
                    'code'  => 1,
                    'msg'   => '升级中',
                    'success'   => false,
                    'data'  => '升级中',
                ],JSON_UNESCAPED_UNICODE);
            }

            var_dump($res);
            die;
            echo 1;
        }

        die;

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
    public function actionGoodListHg()
    {
        $biz_content=array(
            "deviceId"=>100073,//必须要有设备
            "unionid"=>"1353817842",
        );
        $HuoGui = new HuoGui();
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
        },
        {
            "id":2792,
            "goodsName":"阿萨姆奶茶",
            "imgUrl":"http://images.voidiot.com/FlbB6Rq55G7vihmMImSgHyRLWVTr",
            "price":4,
            "baseWeight":550,
            "count":0,
            "weight":20,
            "valuatType":0,
            "deviceId":100023,
            "date":"20190829",
            "trayNum":3,
            "status":1,
            "merchantId":10015,
            "goodsId":1025,
            "createTime":"2019-08-29 11:57",
            "updateTime":"2019-12-20 18:40",
            "ch1":null,
            "ch2":null,
            "sourPrice":0,
            "discount":0,
            "avgWeight":30,
            "categoryId":null
        },
        {
            "id":2793,
            "goodsName":"瓶装饮料",
            "imgUrl":"http://images.voidiot.com/Foj2Z9bLgpPuueLMnKd5e6RN10oh",
            "price":3,
            "baseWeight":550,
            "count":0,
            "weight":20,
            "valuatType":0,
            "deviceId":100023,
            "date":"20190829",
            "trayNum":4,
            "status":1,
            "merchantId":10015,
            "goodsId":1020,
            "createTime":"2019-08-29 11:57",
            "updateTime":"2019-12-20 18:40",
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


//        echo '<pre>';
//        var_dump(json_decode($goods));
        $yyGoods = new GoodsForm();
        $yyGoods->store_id = $this->store_id;
        $yyGoods->user_id = \Yii::$app->user->id;
        $goods = $yyGoods->getList();
//        var_dump($goods);

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