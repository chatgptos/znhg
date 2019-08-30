<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/6/19
 * Time: 15:15
 */

namespace app\modules\api\controllers;


use app\models\AppNavbar;
use app\models\Article;
use app\models\Banner;
use app\models\Cat;
use app\models\FormId;
use app\models\Goods;
use app\models\Option;
use app\models\Setting;
use app\models\Store;
use app\models\UploadConfig;
use app\models\UploadForm;
use app\modules\api\behaviors\LoginBehavior;
use app\modules\api\models\CatListForm;
use app\modules\api\models\CommentListForm;
use app\modules\api\models\CouponListForm;
use app\modules\api\models\DistrictForm;
use app\modules\api\models\GoodsAttrInfoForm;
use app\modules\api\models\GoodsForm;
use app\modules\api\models\GoodsListForm;
use app\modules\api\models\GoodsQrcodeForm;
use app\modules\api\models\IndexForm;
use app\modules\api\models\ShopListForm;
use app\modules\api\models\TopicForm;
use app\modules\api\models\TopicListForm;
use app\modules\api\models\VideoForm;
use app\modules\api\models\ShopForm;
use Curl\Curl;
use yii\data\Pagination;
use yii\helpers\VarDumper;

class DefaultController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
        ]);
    }

    /**
     * 首页接口
     */
    public function actionIndex()
    {
        $form = new IndexForm();
        $form->store_id = $this->store->id;
        $this->renderJson($form->search());
    }

    /**
     * 分类列表
     */
    public function actionCatList()
    {
        $form = new CatListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $this->renderJson($form->search());
    }

    /**
     * 商品列表
     */
    public function actionGoodsList()
    {
        $form = new GoodsListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $this->renderJson($form->search());
    }

    /**
     * 商品详情
     */
    public function actionGoods()
    {
        $form = new GoodsForm();
        $form->attributes = \Yii::$app->request->get();
        if (!\Yii::$app->user->isGuest) {
            $form->user_id = \Yii::$app->user->id;
        }
        $form->store_id = $this->store->id;
        $this->renderJson($form->search());
    }

    /**
     * 省市区数据
     */
    public function actionDistrict()
    {
        $form = new DistrictForm();
        $this->renderJson($form->search());
    }


    public function actionGoodsAttrInfo()
    {
        $form = new GoodsAttrInfoForm();
        $form->attributes = \Yii::$app->request->get();
        $this->renderJson($form->search());
    }

    public function actionStore()
    {
        //分销设置
        $share_setting = Setting::find()->alias('s')
            ->where(['s.store_id' => $this->store_id])
            ->leftJoin('{{%qrcode}} q', 'q.store_id=s.store_id and q.is_delete=0')
            ->select(['s.*', 'q.qrcode_bg'])
            ->asArray()->one();
        if (is_null($share_setting)) {
            $share_setting = new Setting();
            $share_setting->store_id = $this->store_id;
            $share_setting->save();
            $share_setting = Setting::find()->alias('s')
                ->where(['s.store_id' => $this->store_id])
                ->leftJoin('{{%qrcode}} q', 'q.store_id=s.store_id and q.is_delete=0')
                ->select(['s.*', 'q.qrcode_bg'])
                ->asArray()->one();
        }
        $this->renderJson([
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'store_name' => $this->store->name,
                'contact_tel' => $this->store->contact_tel,
                'show_customer_service' => $this->store->show_customer_service,
                'share_setting' => $share_setting,
                'store' => (object)[
                    'id' => $this->store->id,
                    'name' => $this->store->name,
                    'copyright' => $this->store->copyright,
                    'copyright_pic_url' => $this->store->copyright_pic_url,
                    'copyright_url' => $this->store->copyright_url,
                    'contact_tel' => $this->store->contact_tel,
                    'show_customer_service' => $this->store->show_customer_service,
                    'cat_style' => $this->store->cat_style,
                    'address' => $this->store->address,
                    'is_offline' => $this->store->is_offline,
                    'is_coupon' => $this->store->is_coupon,
                    'service' => Option::get('service', $this->store->id, 'admin', '/images/icon-service.png'),
                ],
            ],
        ]);
    }

    public function actionUploadImage()
    {
        $form = new UploadForm();
        $upload_config = UploadConfig::findOne(['store_id' => $this->store->id]);
        $form->upload_config = $upload_config;
        $this->renderJson($form->saveImage('image'));
    }

    //商品评价列表
    public function actionCommentList()
    {
        $form = new CommentListForm();
        $form->attributes = \Yii::$app->request->get();
        $this->renderJson($form->search());
    }

    //文章列表
    public function actionArticleList()
    {
        $list = Article::find()->where([
            'store_id' => $this->store->id,
            'is_delete' => 0,
            'article_cat_id' => \Yii::$app->request->get('cat_id'),
        ])->orderBy('sort DESC,addtime DESC')
            ->select('id,title')->asArray()->all();
        $this->renderJson([
            'code' => 0,
            'data' => (object)[
                'list' => $list,
            ],
        ]);
    }

    //文章详情
    public function actionArticleDetail()
    {
        $id = \Yii::$app->request->get('id');
        if ($id == 'about_us') {
            $model = Article::findOne([
                'store_id' => $this->store->id,
                'article_cat_id' => 1,
            ]);
            if (!$model)
                $model = new Article();
            $this->renderJson([
                'code' => 0,
                'data' => (object)[
                    'id' => $model->id,
                    'title' => $model->title,
                    'content' => $model->content,
                ],
            ]);
        } else {
            $model = Article::find()->where([
                'is_delete' => 0,
                'id' => $id,
            ])->select('id,title,content')->asArray()->one();
            if (empty($model))
                $this->renderJson([
                    'code' => 1,
                    'msg' => '内容不存在',
                ]);
            $this->renderJson([
                'code' => 0,
                'data' => (object)$model,
            ]);
        }
    }

    //核销二维码
    public function actionQrcode($path)
    {
        include \Yii::$app->basePath . '/extensions/phpqrcode/phpqrcode.php';
        \QRcode::png($path);
    }

    public function actionVideoList()
    {
        $form = new VideoForm();
        $form->store_id = $this->store_id;
        $form->attributes = \Yii::$app->request->get();
        $form->limit = 10;
        $this->renderJson($form->getList());
    }

    public function actionCouponList()
    {
        $form = new CouponListForm();
        $form->store_id = $this->store_id;
        $form->user_id = \Yii::$app->user->identity->id;
        $list = $form->getList();
        $this->renderJson([
            'code' => 0,
            'data' => [
                'list' => $list
            ]
        ]);
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

    //专题列表
    public function actionTopicList()
    {
        $form = new TopicListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store_id;
        return $this->renderJson($form->search());
    }

    //专题详情
    public function actionTopic()
    {
        $form = new TopicForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store_id;
        if (!\Yii::$app->user->isGuest) {
            $form->user_id = \Yii::$app->user->id;
        }
        return $this->renderJson($form->search());
    }

    //底部导航栏
    public function actionNavbar()
    {
        $navbar = AppNavbar::getNavbar($this->store->id);
        return $this->renderJson([
            'code' => 0,
            'data' => $navbar,
        ]);
    }

    //顶部导航栏颜色
    public function actionNavigationBarColor()
    {
        $navigation_bar_color = Option::get('navigation_bar_color', $this->store->id, 'app', [
            'frontColor' => '#000000',
            'backgroundColor' => '#ffffff',
        ]);
        return $this->renderJson([
            'code' => 0,
            'data' => $navigation_bar_color,
        ]);
    }

    //门店列表
    public function actionShopList()
    {
        $form = new ShopListForm();
        $form->store_id = $this->store->id;
        $form->user = \Yii::$app->user->identity;
        $form->attributes = \Yii::$app->request->get();
        $this->renderJson($form->search());
    }

    //门店详情
    public function actionShopDetail()
    {
        $form = new ShopForm();
        $form->store_id = $this->store->id;
        $form->user = \Yii::$app->user->identity;
        $form->attributes = \Yii::$app->request->get();
        $this->renderJson($form->search());
    }
}