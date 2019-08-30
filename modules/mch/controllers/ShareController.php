<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/8
 * Time: 14:53
 */

namespace app\modules\mch\controllers;


use app\extensions\Sms;
use app\models\Cash;
use app\models\CashWechatTplSender;
use app\models\Color;
use app\models\Option;
use app\models\Qrcode;
use app\models\Setting;
use app\models\Share;
use app\models\User;
use app\models\WechatTemplateMessage;
use app\models\WechatTplMsgSender;
use app\modules\mch\models\CashForm;
use app\modules\mch\models\OrderListForm;
use app\modules\mch\models\QrcodeForm;
use app\modules\mch\models\ShareBasicForm;
use app\modules\mch\models\ShareListForm;
use app\modules\mch\models\ShareOrderForm;
use app\modules\mch\models\ShareSettingForm;
use app\modules\mch\models\StoreDataForm;
use yii\helpers\VarDumper;

class ShareController extends Controller
{
    /**
     * @return string
     * 分销商列表
     */
    public function actionIndex()
    {
        $form = new ShareListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $form->limit = 10;
        $arr = $form->getList();
        $list = $form->getTeam();
        $count = $form->getCount();
        $setting = Setting::findOne(['store_id' => $this->store->id]);
        return $this->render('index', [
            'list' => $arr[0],
            'pagination' => $arr[1],
            'setting' => $setting,
            'team' => json_encode($list, JSON_UNESCAPED_UNICODE),
            'count' => $count
        ]);
    }

    /**
     * @return mixed|string
     * 佣金设置
     */
    public function actionSetting()
    {
        $store_id = $this->store->id;
        $list = Setting::findOne(['store_id' => $store_id]);
        if (!$list)
            $list = new Setting();
        if (\Yii::$app->request->isPost) {
            $form = new ShareSettingForm();
            $model = \Yii::$app->request->post('model');
            $form->list = $list;
            $form->store_id = $store_id;
            $form->attributes = $model;
            return json_encode($form->save(), JSON_UNESCAPED_UNICODE);
        }
        return $this->render('setting', [
            'list' => $list
        ]);
    }

    /**
     * @return mixed|string
     * 基础设置
     */
    public function actionBasic()
    {
        $store_id = $this->store->id;
        $list = Setting::findOne(['store_id' => $store_id]);
        $qrcode = Qrcode::findOne(['store_id' => $store_id, 'is_delete' => 0]);
        if (!$list)
            $list = new Setting();
        if (!$qrcode) {
            $qrcode = new Qrcode();
        }
        if (\Yii::$app->request->isPost) {
            $model = \Yii::$app->request->post('model');
            $form = new ShareBasicForm();
            $form->list = $list;
            $form->store_id = $store_id;
            $form->attributes = $model;
            return json_encode($form->save(), JSON_UNESCAPED_UNICODE);
        }
        $option = Option::getList('cash_max_day,auto_share_val', $this->store->id, 'share', 0.00);
        $tpl_msg = Option::getList('cash_success_tpl,cash_fail_tpl', $this->store->id, 'share', '');
        return $this->render('basic', [
            'list' => $list,
            'qrcode' => $qrcode,
            'option' => $option,
            'tpl_msg' => $tpl_msg
        ]);
    }

    /**
     * @param int $id
     * @param int $status
     * @return mixed|string
     * 申请审核
     */
    public function actionStatus($id = 0, $status = 1)
    {
        $share = Share::findOne(['id' => $id, 'is_delete' => 0, 'store_id' => $this->store->id]);
        if (!$share) {
            return json_encode([
                'code' => 1,
                'msg' => '网络异常'
            ], JSON_UNESCAPED_UNICODE);
        }
        if (!in_array($status, [1, 2])) {
            return json_encode([
                'code' => 1,
                'msg' => '网络异常'
            ], JSON_UNESCAPED_UNICODE);
        }
        $share->status = $status;
        if ($status == 1) {
            User::updateAll(['time' => time(), 'is_distributor' => 1], ['id' => $share->user_id, 'store_id' => $this->store->id]);
        } else {
            User::updateAll(['time' => time(), 'is_distributor' => 0], ['id' => $share->user_id, 'store_id' => $this->store->id]);
        }
        return json_encode($share->saveS(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return string
     * 提现列表
     */
    public function actionCash()
    {
        $form = new CashForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $form->limit = 10;
        $arr = $form->getList();
        $count = $form->getCount();
        return $this->render('cash', [
            'list' => $arr[0],
            'pagination' => $arr[1],
            'count' => $count
        ]);
    }

    /**
     * @param int $id
     * @param int $status
     * @return mixed|string
     * 申请审核
     */
    public function actionApply($id = 0, $status = 0)
    {
        $cash = Cash::findOne(['id' => $id, 'is_delete' => 0, 'store_id' => $this->store->id]);
        if (!$cash) {
            return json_encode([
                'code' => 1,
                'msg' => '提现记录不逊在，请刷新重试'
            ], JSON_UNESCAPED_UNICODE);
        }
        if (!in_array($status, [1, 3])) {
            return json_encode([
                'code' => 1,
                'msg' => '提现记录已审核，请刷新重试'
            ], JSON_UNESCAPED_UNICODE);
        }
        $cash->status = $status;
        if ($status == 3) {
            $user = User::findOne(['id' => $cash->user_id]);
            $user->price += $cash->price;
            if (!$user->save()) {
                return json_encode([
                    'code' => 1,
                    'msg' => '网络异常'
                ], JSON_UNESCAPED_UNICODE);
            }
        }
        if ($cash->save()) {
            if ($cash->status == 3) {
                $wechat_tpl_meg_sender = new CashWechatTplSender($this->store->id, $cash->id, $this->wechat);
                $wechat_tpl_meg_sender->cashFailMsg();
            }
            return json_encode([
                'code' => 0,
                'msg' => '成功'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode([
                'code' => 1,
                'msg' => '网络异常'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * @param int $id
     * @param int $status
     * @return mixed|string
     * 确认打款
     * 支付未做
     */
    public function actionConfirm($id = 0, $status = 0)
    {
        $cash = Cash::findOne(['id' => $id, 'is_delete' => 0, 'store_id' => $this->store->id]);
        if (!$cash) {
            return json_encode([
                'code' => 1,
                'msg' => '提现记录不存在，请刷新重试'
            ], JSON_UNESCAPED_UNICODE);
        }
        if ($status == 2) {  //微信自动打款
            $cash->status = 2;
            $cash->pay_time = time();
            $user = User::findOne(['id' => $cash->user_id]);
            $data = [
                'partner_trade_no' => md5(uniqid()),
                'openid' => $user->wechat_open_id,
                'amount' => $cash->price * 100,
                'desc' => '转账'
            ];
            $res = $this->wechat->pay->transfers($data);
        } else if ($status == 4) { //手动打款
            $cash->status = 2;
            $cash->pay_time = time();
//            $cash->type = 2;
            $res['result_code'] = "SUCCESS";
        }
        if ($res['result_code'] == 'SUCCESS') {
            $cash->save();
            $wechat_tpl_meg_sender = new CashWechatTplSender($this->store->id, $cash->id, $this->wechat);
            $wechat_tpl_meg_sender->cashMsg();
            return json_encode([
                'code' => 0,
                'msg' => '成功'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode([
                'code' => 1,
                'msg' => $res['err_code_des'],
                'data' => $res
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * @return string
     * 设置推广海报
     */
    public function actionQrcode()
    {
        $store_id = $this->store->id;
        $qrcode = Qrcode::findOne(['store_id' => $store_id, 'is_delete' => 0]);
        $color = Color::find()->select('id,color')->andWhere(['is_delete' => 0])->asArray()->all();
        if (!$qrcode) {
            $qrcode = new Qrcode();
        }
        if (\Yii::$app->request->isPost) {
            $form = new QrcodeForm();
            $model = \Yii::$app->request->post('model');
            $form->store_id = $store_id;
            $form->qrcode = $qrcode;
            $form->attributes = $model;
            return json_encode($form->save(), JSON_UNESCAPED_UNICODE);
        }
        $font_position = json_decode($qrcode->font_position, true);
        $qrcode_position = json_decode($qrcode->qrcode_position, true);
        $avatar_position = json_decode($qrcode->avatar_position, true);
        $avatar_size = json_decode($qrcode->avatar_size, true);
        $qrcode_size = json_decode($qrcode->qrcode_size, true);
        $font_size = json_decode($qrcode->font, true);
        $first = Color::findOne(['id' => $font_size['color']]);

        return $this->render('qrcode', [
            'qrcode' => $qrcode,
            'color' => json_encode($color, JSON_UNESCAPED_UNICODE),
            'first' => !empty($first) ? $first->color : '',
            'avatar_w' => $avatar_size['w'],
            'avatar_x' => $avatar_position['x'],
            'avatar_y' => $avatar_position['y'],
            'qrcode_w' => $qrcode_size['w'],
            'qrcode_c' => ($qrcode_size['c'] == 'true') ? 1 : 0,
            'qrcode_x' => $qrcode_position['x'],
            'qrcode_y' => $qrcode_position['y'],
            'font_x' => $font_position['x'],
            'font_y' => $font_position['y'],
            'font_w' => $font_size['size'],
        ]);
    }

    /**
     * @param int $id
     * @return mixed|string
     * @throws \yii\db\Exception
     * 删除分销商
     */
    public function actionDel($id = 0)
    {
        $share = Share::findOne(['id' => $id, 'is_delete' => 0]);
        if (!$share) {
            return json_encode([
                'code' => 1,
                'msg' => '网络异常'
            ], JSON_UNESCAPED_UNICODE);
        }
        $t = \Yii::$app->db->beginTransaction();
        $count1 = Share::updateAll(['is_delete' => 1], 'id=:id', [':id' => $id]);
        $count2 = User::updateAll(['is_distributor' => 0, 'parent_id' => 0, 'time' => 0], 'id=:id', [':id' => $share->user_id]);
        $count3 = User::updateAll(['parent_id' => 0], 'parent_id=:parent_id', [':parent_id' => $share->user_id]);
        if ($count1 != 0) {
            $t->commit();
            return json_encode([
                'code' => 0,
                'msg' => '成功'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            $t->rollBack();
            return json_encode([
                'code' => 1,
                'msg' => '网络异常'
            ], JSON_UNESCAPED_UNICODE);
        }

    }

    public function actionTest()
    {
//        return $this->render('/tpl.v2.php');
//        $res = Sms::send($this->store->id, "20170909152136642340");
//        var_dump($res);
//        exit();
    }

    public function actionOrder()
    {
        $parent_id = \Yii::$app->request->get('parent_id');
        $form = new ShareOrderForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $form->parent_id = $parent_id ? (int)$parent_id : false;
        $form->limit = 10;
        $data = $form->search();
        if ($parent_id) {
            $user = User::findOne(['store_id' => $this->store->id, 'id' => $parent_id]);
        }
        $setting = Setting::findOne(['store_id' => $this->store->id]);
        return $this->render('order', [
            'row_count' => $data['row_count'],
            'pagination' => $data['pagination'],
            'list' => $data['list'],
            //'count_data' => OrderListForm::getCountData($this->store->id),
//            'store_data' => $store_data_form->search(),
            'user' => isset($user) ? $user : null,
            'setting' => $setting
        ]);
    }
}