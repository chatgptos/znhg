<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/24
 * Time: 16:41
 */

namespace app\modules\api\controllers\group;

use app\models\Banner;
use app\models\Option;
use app\models\PtCat;
use app\models\PtGoods;
use app\modules\api\models\group\GoodsQrcodeForm;
use app\modules\api\models\group\PtGoodsAttrInfoForm;
use app\modules\api\models\group\PtGoodsForm;

/**
 * Class IndexController
 * @package app\modules\api\controller\group
 * 拼团首页模块
 */
class IndexController extends Controller
{
    /**
     * @return mixed|string
     * 拼团首页
     */
    public function actionIndex()
    {
        // 获取导航分类
        $cat = PtCat::find()
            ->select('id,name')
            ->andWhere(['is_delete'=>0,'store_id'=>$this->store_id])
            ->orderBy('sort ASC')
            ->asArray()
            ->all();
        // 获取首页轮播
        $banner = Banner::find()
            ->andWhere(['is_delete' => 0, 'store_id' => $this->store_id, 'type' => 2])
            ->orderBy('sort ASC')
            ->asArray()
            ->all();
//        // 热销商品
//        $goods = PtGoods::find()
//            ->andWhere(['is_delete' => 0, 'store_id' => $this->store_id, 'status' => 1,'is_hot'=>1])
//            ->orderBy('sort ASC')
//            ->asArray()
//            ->all();

        $ad = Option::get('pt_ad', $this->store_id);

        $ptGoods = new PtGoodsForm();
        $ptGoods->store_id = $this->store_id;
        $ptGoods->user_id = \Yii::$app->user->id;
        $goods = $ptGoods->getList();

        return json_encode([
            'code'  => 0,
            'msg'   => 'success',
            'data'  => [
                'cat'     => $cat,
                'banner'  => $banner,
                'ad'  => $ad,
                'goods'   => $goods,
            ],
        ],JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return mixed|string
     * 数据加载
     */
    public function actionGoodList()
    {
        $ptGoods = new PtGoodsForm();
        $ptGoods->store_id = $this->store_id;
        $ptGoods->user_id = \Yii::$app->user->id;
        $goods = $ptGoods->getList();
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
        $ptGoods = new PtGoodsForm();
        $ptGoods->store_id = $this->store_id;
        $ptGoods->gid = $gid;
        $ptGoods->user_id = \Yii::$app->user->id;
        return json_encode($ptGoods->getInfo(),JSON_UNESCAPED_UNICODE);
    }
    /**
     * @param int $gid
     * @return mixed|string
     * 商品评价
     */
    public function actionGoodsComment($gid = 0,$page = 0)
    {
        $ptGoods = new PtGoodsForm();
        $ptGoods->store_id = $this->store_id;
        $ptGoods->gid = $gid;
        $ptGoods->page = $page;
        $ptGoods->user_id = \Yii::$app->user->id;
        return json_encode($ptGoods->comment(),JSON_UNESCAPED_UNICODE);
    }

    public function actionGoodsAttrInfo()
    {
        $form = new PtGoodsAttrInfoForm();
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


}