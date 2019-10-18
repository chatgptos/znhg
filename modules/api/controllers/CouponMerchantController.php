<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/8
 * Time: 14:15
 */

namespace app\modules\api\controllers;

use app\extensions\CreateQrcode;
use app\models\Cash;
use app\models\Color;
use app\models\Option;
use app\models\Qrcode;
use app\models\Setting;
use app\models\Share;
use app\models\Store;
use app\models\UploadConfig;
use app\models\UploadForm;
use app\models\User;
use app\modules\api\behaviors\LoginBehavior;
use app\modules\api\models\BindForm;
use app\modules\api\models\CashForm;
use app\modules\api\models\CashListForm;
use app\modules\api\models\QrcodeForm;
use app\modules\api\models\ShareForm;
use app\modules\api\models\TeamForm;
use yii\helpers\VarDumper;

class CouponMerchantController extends Controller
{

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginBehavior::className(),
            ],
        ]);
    }

    /**
     * @return mixed|string
     * 券商分类
     */
    public function actionGetQsInfo()
    {

        $id = \Yii::$app->request->get('qsId');
        $store_id = $this->store->id;
        $list = Setting::findOne(['store_id' => $store_id]);
        $user = User::findOne(['id' => \Yii::$app->user->identity->id, 'store_id' => $this->store->id]);

        //推荐人数
        $tuijianNum = User::find()->where(['parent_id' => $user->id, 'store_id' => $this->store->id])->count();
        $card_count = $user->coupon;
        $hld = $user->hld;

        $buttonClicked=false;
        $buttonName='立即申请';
        $roleName='普通用户';
        $team_count_require=0;
        $card_count_require=0;
        $award=[];

        if($user->is_agency){
            $roleName='经销商';
        }

        if($user->is_distributors){
            $roleName='渠道商';
        }

        if ($id == 0) {
            //经销商
            $team_count_require = $list->agency_team_count_require;
            $card_count_require = $list->agency_card_count_require;
            if($user->is_agency){
                $buttonClicked=true;
                $buttonName='已经拥有';
            }
        } elseif ($id == 1) {
            //渠道商
            $team_count_require = $list->distributors_card_count_require;
            $card_count_require = $list->distributors_team_count_require;
            if($user->is_distributors){
                $buttonClicked=true;
            }
        } elseif ($id == 2) {
            //服务权
            $team_count_require = 0;
            $card_count_require = 0;
            $buttonName='暂未开放';
            $buttonClicked=true;
        } elseif ($id == 3) {
            //分红权
            $team_count_require = $list->dividend_sharing_right_team_count_require;
            $card_count_require = $list->dividend_sharing_right_card_count_require;
            if($user-> dividend_sharing_right){
                $buttonClicked=true;
                $buttonName='已经拥有';
            }
        } elseif ($id == 4) {
            //福利
            $team_count_require = $list->agency_team_count_require;
            $card_count_require = $list->agency_card_count_require;
            $buttonName='暂未开放';
            $buttonClicked=true;
        } elseif ($id == 5) {
            //抽奖
            $team_count_require = 0;
            $card_count_require = 0;

            if($card_count<1){
                $buttonName='优惠券不够';
                $buttonClicked=true;
            }else{
                $buttonName='1张/次';
            }
            //奖品列表
            $awardsList=['1张券',  '2张券', '3元张券', '5张券','10张券', '谢谢惠顾'];
            $award=array(
                'awardsList'=>$awardsList,
            );
        } elseif ($id == 6) {
            //赠送
            $team_count_require = 0;
            $card_count_require = 0;
            $buttonName='暂未开放';
            $buttonClicked=true;
        }else {
            //初始化
            //经销商
            $team_count_require = $list->agency_team_count_require;
            $card_count_require = $list->agency_card_count_require;
            if($user->is_agency){
                $buttonClicked=true;
                $buttonName='已经拥有';
            }


        }


        $userlist=array(
            'roleName'=>$roleName,
            'coupon'=>$card_count,
            'people'=>$tuijianNum,
            'hld'=>$hld,
        );

        return json_encode([
            'code' => 0,
            'msg' => 'success',
            'data' => array(
                'team_count_require' => $team_count_require,
                'card_count_require' => $card_count_require,
                'buttonClicked'=>$buttonClicked,
                'buttonName'=>$buttonName,
                'userlist'=>$userlist,
                'award'=>$award,
            )
        ], JSON_UNESCAPED_UNICODE);

    }



    /**
     * @return mixed|string
     * 券商分类
     */
    public function actionChoujiang()
    {

        $num = \Yii::$app->request->get('num');
        $store_id = $this->store->id;
        $list = Setting::findOne(['store_id' => $store_id]);
        $user = User::findOne(['id' => \Yii::$app->user->identity->id, 'store_id' => $this->store->id]);

        //推荐人数
        $tuijianNum = User::find()->where(['parent_id' => $user->id, 'store_id' => $this->store->id])->count();
        $card_count = $user->coupon;
        $hld = $user->hld;

        $buttonClicked=false;

        $buttonName='开始抽奖';
        $buttonClicked=false;
        $awardsList=['1张券',  '2张券', '3元张券', '5张券','10张券', '谢谢惠顾'];
        $awardsListQuan=[1, 2, 3, 4, 5,10, 0];
        //加工人工随机概率
        //获得抽奖结果 序列号
        $awardIndex=array_rand($awardsList,1);

        //抽奖奖品
        $awardName=$awardsList[$awardIndex];
        //券个数
        $awardsListQuanNum=$awardsListQuan[$awardIndex];




        $award=array(
            'awardsList'=>$awardsList,
            'awardIndex'=>$awardIndex,
            'awardName'=>$awardName,
            'duration'=>4000,
            'runNum'=>5,
        );

        $buttonClicked=true;

        $user->coupon= $user->coupon-$num;//失去N张券 抽奖花费

        if($awardsListQuanNum){
            //增加券
            $user->coupon = $user->coupon + $awardsListQuanNum;

        }
        $res = $user->save();

        if ($res['code'] != 0) {
            return json_encode([
                'code' => 0,
                'msg' => '抽奖失败',
                'data' =>  array(
                    'buttonClicked'=>$buttonClicked,
                    'buttonName'=>$buttonName,
                    'award'=>$award,
                    'coupon'=>$user->coupon,
                ),
            ], JSON_UNESCAPED_UNICODE);
        }

        return json_encode([
            'code' => 0,
            'msg' => 'success',
            'data' => array(
                'buttonClicked'=>$buttonClicked,
                'buttonName'=>$buttonName,
                'award'=>$award,
                'coupon'=>$user->coupon,
            )
        ], JSON_UNESCAPED_UNICODE);

    }

    /**
     * @return mixed|string
     * 申请成为分销商
     */
    public function actionJoin()
    {
        $user = User::findOne(['id' => \Yii::$app->user->identity->id, 'store_id' => $this->store->id]);
        if (!$user) {
            return json_encode([
                'code' => 1,
                'msg' => '用户不存在，或已删除'
            ], JSON_UNESCAPED_UNICODE);
        }

        $store_id = $this->store->id;
        $list = Setting::findOne(['store_id' => $store_id]);

        //获取我的团队
        $team = new TeamForm();
        $team->user_id = \Yii::$app->user->id;
        $team->store_id = $this->store_id;
        $team->status = -1;
        $get_team = $team->getList();
        $team_count = $get_team['data']['first'] + $get_team['data']['second'] + $get_team['data']['third'];

        $team_count_require = $list->agency_team_count_require;;
        //检查人数
        if ($team_count < $team_count_require) {
            return json_encode([
                'code' => 1,
                'msg' => '推荐人数不够'
            ], JSON_UNESCAPED_UNICODE);

        }

        $card_count = User::getCardCount($user->id);
        $card_count_require = $list->agency_card_count_require;

        //检查优惠券数量
        if ($card_count < $card_count_require) {
            return json_encode([
                'code' => 1,
                'msg' => '优惠券数量不够'
            ], JSON_UNESCAPED_UNICODE);
        }

        //增加用户相关权限
//        is_agency
//        is_distributors
//        is_servicing_right
//        dividend_sharing_right

        $user->is_agency = 1;
        if (!$user->save()) {
            return json_encode([
                'code' => 1,
                'msg' => '申请失败！请重试'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode([
                'code' => 0,
                'msg' => '申请成功'
            ], JSON_UNESCAPED_UNICODE);
        }


    }

    /**
     * @return mixed|string
     * 获取用户的审核状态
     */
    public function actionCheck()
    {
        return json_encode([
            'code' => 0,
            'msg' => 'success',
            'data' => \Yii::$app->user->identity->is_distributor,
            'level' => \Yii::$app->user->identity->level
        ], JSON_UNESCAPED_UNICODE);
        $setting = Setting::findOne(['store_id' => $this->store_id]);
        if ($setting->share_condition == 0) {
            $share = Share::findOne(['user_id' => \Yii::$app->user->identity->id, 'store_id' => $this->store->id, 'is_delete' => 0]);
            if (!$share) {
                $share = new Share();
            }
            $form = new ShareForm();
            $form->share = $share;
            $form->store_id = $this->store_id;
            $form->agree = 1;
//            $form->scenario = "NONE_CONDITION";
            $form->attributes = \Yii::$app->request->post();
            $res = $form->save();
            if ($res['code'] == 0) {
                return json_encode([
                    'code' => 0,
                    'msg' => 'success',
                    'data' => 2,
                    'level' => \Yii::$app->user->identity->level
                ], JSON_UNESCAPED_UNICODE);
            }
        } else {
            return json_encode([
                'code' => 0,
                'msg' => 'success',
                'data' => \Yii::$app->user->identity->is_distributor,
                'level' => \Yii::$app->user->identity->level
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * @return mixed|string
     * 获取分销中心数据
     */
    public function actionGetInfo()
    {
        $res = [
            'code' => 0,
            'msg' => 'success',
        ];
        //获取分销佣金及提现
        $form = new ShareForm();
        $form->store_id = $this->store_id;
        $form->user_id = \Yii::$app->user->identity->id;
        $res['data']['price'] = $form->getPrice();
        //获取我的团队
        $team = new TeamForm();
        $team->user_id = \Yii::$app->user->id;
        $team->store_id = $this->store_id;
        $team->status = -1;
        $get_team = $team->getList();
        $res['data']['team_count'] = $get_team['data']['first'] + $get_team['data']['second'] + $get_team['data']['third'];
        //获取分销订单总额
        $order = $team->GetOrder();
        $money = 0;
        foreach ($order['data'] as $index => $value) {
            $money += doubleval($value['share_money']);
        }
        $res['data']['order_money'] = doubleval(sprintf('%.2f', $money));

        return json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return mixed|string
     * 获取佣金相关
     */
    public function actionGetPrice()
    {
        $form = new ShareForm();
        $form->store_id = $this->store_id;
        $form->user_id = \Yii::$app->user->identity->id;
        $res['data']['price'] = $form->getPrice();
        $setting = Setting::findOne(['store_id' => $this->store->id]);
        $res['data']['pay_type'] = $setting->pay_type;
        $res['data']['bank'] = $setting->bank;

        $cash_last = Cash::find()->where(['store_id' => $this->store->id, 'user_id' => \Yii::$app->user->identity->id, 'is_delete' => 0])
            ->orderBy(['id' => SORT_DESC])->select(['name', 'mobile', 'type'])->asArray()->one();
        $res['data']['cash_last'] = $cash_last;

        $cash_max_day = floatval(Option::get('cash_max_day', $this->store_id, 'share', 0));
        if ($cash_max_day) {
            $cash_sum = Cash::find()->where([
                'store_id' => $this->store->id,
                'is_delete' => 0,
                'status' => [0, 1, 2],
            ])->andWhere([
                'AND',
                ['>=', 'addtime', strtotime(date('Y-m-d 00:00:00'))],
                ['<=', 'addtime', strtotime(date('Y-m-d 23:59:59'))],
            ])->sum('price');
            $cash_max_day = $cash_max_day - ($cash_sum ? $cash_sum : 0);
            $res['data']['cash_max_day'] = max(0, floatval(sprintf('%.2f', $cash_max_day)));
        } else {
            $res['data']['cash_max_day'] = -1;
        }
        return $this->renderJson($res);
    }

    /**
     * @return mixed|string
     * 申请提现
     */
    public function actionApply()
    {
        $form = new CashForm();
        $form->user_id = \Yii::$app->user->identity->id;
        $form->store_id = $this->store_id;
        $form->attributes = \Yii::$app->request->post();


        return json_encode($form->save(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return mixed|string
     * 申请充值
     */
    public function actionRecharge()
    {
        $form = new CashForm();
        $form->user_id = \Yii::$app->user->identity->id;
        $form->store_id = $this->store_id;
        $form->attributes = \Yii::$app->request->post();


        return json_encode($form->save(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 提现明细列表
     */
    public function actionCashDetail()
    {
        $form = new CashListForm();
        $get = \Yii::$app->request->get();
//        $form->scenario = $get['scene'];
        $form->attributes = $get;
        $form->store_id = $this->store->id;
        $form->user_id = \Yii::$app->user->id;
        $this->renderJson($form->getList());
    }

    /**
     * @return mixed|string
     * 获取推广海报
     */
    public function actionGetQrcode()
    {
        //获取用户信息
        $user_id = \Yii::$app->user->id;
        $store_id = $this->store_id;
        $user = User::findOne(['id' => $user_id, 'store_id' => $store_id]);
        $avatar = $user->avatar_url;
        $name = $user->nickname;
        $save_name = md5("v=1.9.6&store_id={$this->store->id}&store_name={$this->store->name}&user_id={$user_id}") . '.jpg';
        $pic_url = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/temp/' . $save_name;

        $save_root = \Yii::$app->basePath . '/web/temp/';
        if (file_exists($save_root . $save_name)) {
            return json_encode([
                'code' => 0,
                'msg' => 'success',
                'data' => $pic_url . '?v=' . time()
            ], JSON_UNESCAPED_UNICODE);
        }

        //获取商户海报设置  默认为1
        $store_qrcode = Qrcode::findOne(['store_id' => $store_id, 'is_delete' => 0]);
        if (!$store_qrcode) {
//            $store_qrcode = Qrcode::findOne(1);
            return json_encode([
                'code' => 1,
                'msg' => '请先在后台设置分销海报'
            ]);
        }
        $font_position = json_decode($store_qrcode->font_position, true);
        $qrcode_position = json_decode($store_qrcode->qrcode_position, true);
        $avatar_position = json_decode($store_qrcode->avatar_position, true);
        $avatar_size = json_decode($store_qrcode->avatar_size, true);
        $qrcode_size = json_decode($store_qrcode->qrcode_size, true);
        $font_size = json_decode($store_qrcode->font, true);
        list($qrcode_bg_w, $qrcode_bg_h) = getimagesize($store_qrcode->qrcode_bg);

        $percent = $qrcode_bg_w / 300;
//        $percent = 0.5;
        //获取微信小程序码
        $access_token = $this->wechat->getAccessToken();
        $api = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token={$access_token}";
        $data = json_encode([
            'scene' => "{$user_id}",
            'page' => "pages/user/user",
            'width' => (int)($qrcode_size['w'] * $percent)
        ], JSON_UNESCAPED_UNICODE);
        $this->wechat->curl->post($api, $data);
        if ($this->wechat->curl->error) {
            return json_encode([
                'code' => 1,
                'msg' => '小程序码获取失败',
            ]);
        }
        $res = $this->wechat->curl->response;

        //保存到本地
        $saveRoot = \Yii::$app->basePath . '/web/temp/';
        $saveDir = '/';
        if (!is_dir($saveRoot . $saveDir)) {
            mkdir($saveRoot . $saveDir);
            file_put_contents($saveRoot . $saveDir . '.gitignore', "*\r\n!.gitignore");
        }
        $webRoot = \Yii::$app->request->baseUrl . '/';
        $saveName = md5(uniqid()) . '.png';
        file_put_contents($saveRoot . $saveDir . $saveName, $res);
        $form = new CreateQrcode();
        $form->qrcode = $saveRoot . $saveDir . $saveName;
        $form->avatar = $avatar;
        $form->name = $name;
        $form->store_qrcode = $store_qrcode;
        $font_file = \Yii::$app->basePath . '/web/statics/font/AaBanSong.ttf';//字体
        $font_array = imagettfbbox((int)$font_size['size'] * $percent * 0.74, 0, $font_file, $name);
        $form->font_x = (int)$font_position['x'] * $percent;
        $form->font_y = (int)$font_position['y'] * $percent + (int)($font_array[3] - $font_array[5]);
        $color = Color::find()->andWhere(['id' => (int)$font_size['color']])->asArray()->one();
        $form->font_size = (int)$font_size['size'] * $percent * 0.74;
        $form->font_color = json_decode($color['rgb'], true);

        $form->qrcode_x = (int)$qrcode_position['x'] * $percent;
        $form->qrcode_w = (int)$qrcode_size['w'] * $percent;
        $form->qrcode_y = (int)$qrcode_position['y'] * $percent;
        $form->qrcode_true = isset($qrcode_size['c']) ? $qrcode_size['c'] : true;

        $form->avatar_x = (int)$avatar_position['x'] * $percent;
        $form->avatar_y = (int)$avatar_position['y'] * $percent;
        $form->avatar_w = (int)$avatar_size['w'] * $percent;
        $form->avatar_h = (int)$avatar_size['h'] * $percent;

        $form->qrcode_bg = $store_qrcode->qrcode_bg;
        $form->save_name = $save_name;
        $res = $form->getQrcode();
//        $upload_form = new UploadForm();
//        $upload_config = UploadConfig::findOne(['store_id' => 0]);
//        $upload_form->upload_config = $upload_config;
//        $upload_form->store = $this->store;
//        $file_res = $upload_form->saveQrcode($saveRoot . $saveDir . $res, $res);
//        $file_res_new = [];
//        $file_res_new['code'] = 0;
//        $file_res_new['msg'] = "success";
//        $file_res_new['data'] = $file_res['data']['url'];
        return json_encode([
            'code' => 0,
            'msg' => 'success',
            'data' => $pic_url . '?v=' . time()
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return mixed|string
     * 商店分销设置信息
     */
    public function actionShopShare()
    {
        $list = Setting::find()->alias('s')
            ->where(['s.store_id' => $this->store_id])
            ->leftJoin('{{%qrcode}} q', 'q.store_id=s.store_id and q.is_delete=0')
            ->select(['s.level', 'q.qrcode_bg'])
            ->asArray()->one();
        return json_encode([
            'code' => 0,
            'msg' => '',
            'data' => $list
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return mixed|string
     * 绑定上下级关系
     */
    public function actionBindParent()
    {
        $form = new BindForm();
        $form->user_id = \Yii::$app->user->id;
        $form->store_id = $this->store_id;
        return json_encode($form->save(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return mixed|string
     * 获取团队详情
     */
    public function actionGetTeam()
    {
        $form = new TeamForm();
        $form->user_id = \Yii::$app->user->id;
        $form->store_id = $this->store_id;
        $form->scenario = "TEAM";
        $form->attributes = \Yii::$app->request->get();
        return json_encode($form->getList(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return mixed|string
     * 获取分销订单
     */
    public function actionGetOrder()
    {
        $form = new TeamForm();
        $form->user_id = \Yii::$app->user->id;
        $form->store_id = $this->store_id;
        $form->scenario = "ORDER";
        $form->attributes = \Yii::$app->request->get();
        return json_encode($form->getOrder(), JSON_UNESCAPED_UNICODE);
    }
}