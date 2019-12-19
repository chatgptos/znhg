<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/24
 * Time: 16:41
 */

namespace app\modules\api\controllers\crowdapply;

use app\modules\api\models\crowdapply\Cat;
use app\modules\api\models\crowdapply\Setting;
use app\modules\api\models\crowdapply\CommentListForm;
use app\modules\api\models\crowdapply\GoodsQrcodeForm;
use app\modules\api\models\crowdapply\ShopListForm;
use app\modules\api\models\crowdapply\GoodsForm;

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