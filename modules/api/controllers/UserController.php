<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/7/18
 * Time: 11:28
 */

namespace app\modules\api\controllers;


use app\models\Address;
use app\models\FormId;
use app\models\Level;
use app\models\Option;
use app\models\Order;
use app\models\Setting;
use app\models\Share;
use app\models\Store;
use app\models\User;
use app\models\UserCard;
use app\models\UserCenterMenu;
use app\modules\api\behaviors\LoginBehavior;
use app\modules\api\models\AddressDeleteForm;
use app\modules\api\models\AddressSaveForm;
use app\modules\api\models\AddressSetDefaultForm;
use app\modules\api\models\AddWechatAddressForm;
use app\modules\api\models\CardListForm;
use app\modules\api\models\FavoriteAddForm;
use app\modules\api\models\FavoriteListForm;
use app\modules\api\models\FavoriteRemoveForm;
use app\modules\api\models\OrderListForm;
use app\modules\api\models\TopicFavoriteForm;
use app\modules\api\models\TopicFavoriteListForm;
use app\modules\api\models\WechatDistrictForm;
use app\modules\api\models\QrcodeForm;

class UserController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginBehavior::className(),
            ],
        ]);
    }

    //个人中心
    public function actionIndex()
    {
        $order_count = OrderListForm::getCountData($this->store->id, \Yii::$app->user->id);
        $share_setting = Setting::find()->where(['store_id' => $this->store->id])->asArray()->one();
        $parent = User::findOne(\Yii::$app->user->identity->parent_id);
        $share = Share::findOne(['user_id' => \Yii::$app->user->identity->parent_id]);

        $user = User::findOne(['id' => \Yii::$app->user->identity->id]);
        $level = $user->level;


        $now_level = Level::findOne(['store_id' => $this->store->id, 'level' => $level, 'is_delete' => 0]);
        $user_info = [
            'nickname' => \Yii::$app->user->identity->nickname,
            'avatar_url' => \Yii::$app->user->identity->avatar_url,
            'is_distributor' => \Yii::$app->user->identity->is_distributor,
//            'parent' => $share ? $share->name : ($parent ? $parent->nickname : '总店'),
            'parent' => $share ? ($share->name ? $share->name : $parent->nickname) : "总店",
            'id' => \Yii::$app->user->identity->id,
            'is_clerk' => \Yii::$app->user->identity->is_clerk,
            'level' => $level,
            'level_name' => $now_level ? $now_level->name : "普通用户",
            'integral' => \Yii::$app->user->identity->integral,
        ];
        $next_level = Level::find()->where(['store_id' => $this->store->id, 'is_delete' => 0, 'status' => 1])
            ->andWhere(['>', 'level', $level])->orderBy(['level' => SORT_ASC, 'id' => SORT_DESC])->asArray()->one();

        $user_center_menu = new UserCenterMenu();
        $user_center_menu->store_id = $this->store->id;
        $user_center_bg = Option::get('user_center_bg', $this->store->id, 'app');
        if ($user_center_bg && stripos($user_center_bg, 'http') === false) {
            $user_center_bg = \Yii::$app->request->hostInfo . $user_center_bg;
        }
        if (!$user_center_bg)
            $user_center_bg = '/images/img-user-bg.png';
        return $this->renderJson([
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'order_count' => $order_count,
                'show_customer_service' => $this->store->show_customer_service,
                'contact_tel' => $this->store->contact_tel,
                'share_setting' => $share_setting,
                'user_info' => $user_info,
                'next_level' => $next_level,
                'menu_list' => $user_center_menu->getMenuList(),
                'user_center_bg' => $user_center_bg,
            ],
        ]);
    }

    //收货地址列表
    public function actionAddressList()
    {
        $list = Address::find()->select('id,name,mobile,province_id,province,city_id,city,district_id,district,detail,is_default')->where([
            'store_id' => $this->store->id,
            'user_id' => \Yii::$app->user->id,
            'is_delete' => 0,
        ])->orderBy('is_default DESC,addtime DESC')->asArray()->all();
        foreach ($list as $i => $item) {
            $list[$i]['address'] = $item['province'] . $item['city'] . $item['district'] . $item['detail'];
        }
        $this->renderJson((object)[
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'list' => $list,
            ],
        ]);
    }

    //收货地址详情
    public function actionAddressDetail()
    {
        $address = Address::find()->select('id,name,mobile,province_id,province,city_id,city,district_id,district,detail,is_default')->where([
            'store_id' => $this->store->id,
            'user_id' => \Yii::$app->user->id,
            'is_delete' => 0,
            'id' => \Yii::$app->request->get('id'),
        ])->one();
        if (!$address) {
            $this->renderJson([
                'code' => 1,
                'msg' => '收货地址不存在',
            ]);
        }
        $this->renderJson((object)[
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'address_id' => $address->id,
                'name' => $address->name,
                'mobile' => $address->mobile,
                'district' => [
                    'province' => [
                        'id' => $address->province_id,
                        'name' => $address->province,
                    ],
                    'city' => [
                        'id' => $address->city_id,
                        'name' => $address->city,
                    ],
                    'district' => [
                        'id' => $address->district_id,
                        'name' => $address->district,
                    ],
                ],
                'detail' => $address->detail,
            ],
        ]);
    }

    //保存收货地址
    public function actionAddressSave()
    {
        $form = new AddressSaveForm();
        $form->attributes = \Yii::$app->request->post();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;
        $this->renderJson($form->save());
    }

    //设为默认收货地址
    public function actionAddressSetDefault()
    {
        $form = new AddressSetDefaultForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;
        $this->renderJson($form->save());
    }

    //删除收货地址
    public function actionAddressDelete()
    {
        $form = new AddressDeleteForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;
        $this->renderJson($form->save());
    }


    //保存用户的form id
    public function actionSaveFormId()
    {
        if (!\Yii::$app->user->isGuest) {
            FormId::addFormId([
                'store_id' => $this->store->id,
                'user_id' => \Yii::$app->user->id,
                'wechat_open_id' => \Yii::$app->user->identity->wechat_open_id,
                'form_id' => \Yii::$app->request->get('form_id'),
                'type' => 'form_id',
            ]);
        }
    }

    //添加商品到我的喜欢
    public function actionFavoriteAdd()
    {
        $form = new FavoriteAddForm();
        $form->attributes = \Yii::$app->request->post();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;
        $this->renderJson($form->save());
    }

    //取消喜欢商品
    public function actionFavoriteRemove()
    {
        $form = new FavoriteRemoveForm();
        $form->attributes = \Yii::$app->request->post();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;
        $this->renderJson($form->save());
    }

    //喜欢的商品列表
    public function actionFavoriteList()
    {
        $form = new FavoriteListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->user_id = \Yii::$app->user->id;
        $this->renderJson($form->search());
    }

    //根据微信地址获取数据库省市区数据
    public function actionWechatDistrict()
    {
        $form = new WechatDistrictForm();
        $form->attributes = \Yii::$app->request->get();
        $this->renderJson($form->search());
    }

    //添加微信获取的地址
    public function actionAddWechatAddress()
    {
        $form = new AddWechatAddressForm();
        $form->attributes = \Yii::$app->request->post();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;
        $this->renderJson($form->save());
    }

    //收藏|取消收藏专题
    public function actionTopicFavorite()
    {
        $form = new TopicFavoriteForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;
        $this->renderJson($form->save());
    }

    //收藏专题列表
    public function actionTopicFavoriteList()
    {
        $form = new TopicFavoriteListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;
        $this->renderJson($form->search());
    }

    //会员权益
    public function actionMember()
    {
        $level = \Yii::$app->user->identity->level;
        $now_level = Level::find()->where(['store_id' => $this->store->id, 'level' => $level, 'is_delete' => 0])->asArray()->one();
        $user_info = [
            'nickname' => \Yii::$app->user->identity->nickname,
            'avatar_url' => \Yii::$app->user->identity->avatar_url,
            'id' => \Yii::$app->user->identity->id,
            'level' => $level,
            'level_name' => $now_level ? $now_level['name'] : "普通用户"
        ];
        $time = time();
        $store = Store::findOne(['id' => $this->store_id]);
        $sale_time = $time - ($store->after_sale_time * 86400);
        $next_level = Level::find()->where(['store_id' => $this->store->id, 'is_delete' => 0, 'status' => 1])
            ->andWhere(['>', 'level', $level])->orderBy(['level' => SORT_ASC, 'id' => SORT_DESC])->asArray()->one();
        $order_money = Order::find()->where(['store_id' => $this->store->id, 'user_id' => \Yii::$app->user->identity->id, 'is_delete' => 0])
            ->andWhere(['is_pay' => 1, 'is_confirm' => 1])->andWhere(['<=', 'confirm_time', $sale_time])->select([
                'sum(pay_price)'
            ])->scalar();
        $percent = 100;
        $s_money = 0;
        if ($next_level) {
            $percent = round($order_money / $next_level['money'] * 100, 2);
            $s_money = round($next_level['money'] - $order_money, 2);
        }
        $content = Store::find()->where(['id' => $this->store->id])->select('member_content')->scalar();
        $order_money = $order_money ? $order_money : 0;
        return $this->renderJson([
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'user_info' => $user_info,
                'next_level' => $next_level,
                'now_level' => $now_level,
                'order_money' => $order_money,
                'percent' => $percent,
                's_money' => $s_money,
                'content' => $content
            ],
        ]);
    }

    /**
     * 用户卡券
     */
    public function actionCard()
    {
        $form = new CardListForm();
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->identity->id;
        $form->attributes = \Yii::$app->request->get();
        $this->renderJson($form->search());
    }

    /**
     * 卡券二维码
     */
    public function actionCardQrcode()
    {
        $user_card_id = \Yii::$app->request->get('user_card_id');
        $user_card = UserCard::findOne(['id' => $user_card_id]);
        $form = new QrcodeForm();
        $form->data = [
            'scene' => "{$user_card_id}",
            'page' => 'pages/card-clerk/card-clerk',
            'width' => 100
        ];
        $form->store = $this->store;
        $res = $form->getQrcode();
        $this->renderJson($res);
    }

    /**
     * 卡券核销
     */
    public function actionCardClerk()
    {
        $user_card_id = \Yii::$app->request->get('user_card_id');
        if (\Yii::$app->cache->get('card_id_' . $user_card_id)) {
            $this->renderJson([
                'code' => 1,
                'msg' => '卡券核销中，请稍后重试'
            ]);
        }
        \Yii::$app->cache->set('card_id_' . $user_card_id, true);
        $user_card = UserCard::findOne(['id' => $user_card_id]);
        if ($user_card->is_use != 0) {
            $this->renderJson([
                'code' => 1,
                'msg' => '卡券已核销'
            ]);
        }
        $user = \Yii::$app->user->identity;
        if ($user->is_clerk != 1) {
            $this->renderJson([
                'code' => 1,
                'msg' => '不是核销员禁止核销'
            ]);
        }
        $user_card->clerk_id = $user->id;
        $user_card->shop_id = $user->shop_id;
        $user_card->clerk_time = time();
        $user_card->is_use = 1;
        if ($user_card->save()) {
            \Yii::$app->cache->set('card_id_' . $user_card_id, false);
            $this->renderJson([
                'code' => 0,
                'msg' => '核销成功'
            ]);
        } else {
            $this->renderJson([
                'code' => 1,
                'msg' => '核销失败'
            ]);
        }
    }
}