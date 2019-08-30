<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/11
 * Time: 9:53
 */

namespace app\modules\api\models;


use app\models\Cash;
use app\models\FormId;
use app\models\Option;
use app\models\Setting;
use app\models\User;

class CashForm extends Model
{
    public $user_id;
    public $store_id;

    public $cash;
    public $name;
    public $mobile;
    public $bank;
    public $pay_type;
    public $form_id;

    public function rules()
    {
        return [
            [['cash'], 'required'],
            [['name','mobile','bank','pay_type'],'required','on'=>'CASH'],
            [['cash'], 'number', 'min' => 0,],
            [['name','mobile','form_id','bank'],'trim'],
            [['pay_type'],'in','range'=>[0,1,3]]
        ];
    }

    public function attributeLabels()
    {
        return [
            'cash' => '提现金额',
            'name' => '姓名',
            'pay_type' => '提现方式',
            'mobile' => '账号',
            'bank' => '开户行',
        ];
    }

    public function save()
    {
        if ($this->validate()) {
            $cash_max_day = floatval(Option::get('cash_max_day', $this->store_id, 'share', 0));
            if ($cash_max_day) {
                $cash_sum = Cash::find()->where([
                    'store_id' => $this->store_id,
                    'is_delete' => 0,
                    'status' => [0, 1, 2],
                ])->andWhere([
                    'AND',
                    ['>=', 'addtime', strtotime(date('Y-m-d 00:00:00'))],
                    ['<=', 'addtime', strtotime(date('Y-m-d 23:59:59'))],
                ])->sum('price');
                $cash_max_day = $cash_max_day - ($cash_sum ? $cash_sum : 0);
                if ($this->cash > $cash_max_day) {
                    return [
                        'code' => 1,
                        'msg' => '提现金额不能超过' . $cash_max_day . '元'
                    ];
                }
            }


            $user = User::findOne(['id' => $this->user_id, 'store_id' => $this->store_id]);
            if (!$user) {
                return [
                    'code' => 1,
                    'msg' => '网络异常'
                ];
            }
            $share_setting = Setting::findOne(['store_id' => $this->store_id]);
            if ($this->cash < $share_setting->min_money) {
                return [
                    'code' => 1,
                    'msg' => '提现金额不能小于' . $share_setting->min_money . '元'
                ];
            }
            if ($user->price < $this->cash) {
                return [
                    'code' => 1,
                    'msg' => '提现金额不能超过剩余金额'
                ];
            }
            $exit = Cash::find()->andWhere(['=', 'status', 0])->andWhere(['user_id' => $this->user_id, 'store_id' => $this->store_id])->exists();
            if ($exit) {
                return [
                    'code' => 1,
                    'msg' => '尚有未完成的提现申请'
                ];
            }
            $t = \Yii::$app->db->beginTransaction();
            $cash = new Cash();
            $cash->is_delete = 0;
            $cash->status = 0;
            $cash->price = $this->cash;
            $cash->addtime = time();
            $cash->user_id = $this->user_id;
            $cash->store_id = $this->store_id;
            $cash->type = $this->pay_type;
            $cash->name = $this->name;
            $cash->mobile = $this->mobile;
            $cash->bank = $this->bank;
            $cash->pay_time = 0;
            if ($cash->save()) {
                FormId::addFormId([
                    'form_id'=>$this->form_id,
                    'store_id'=>$this->store_id,
                    'wechat_open_id'=>$user->wechat_open_id,
                    'order_no'=>'cash'.md5("id={$cash->id}&store_id={$this->store_id}"),
                    'type'=>'form_id',
                    'send_count'=>0,
                    'user_id'=>$user->id
                ]);
                $user->price -= $this->cash;
                if (!$user->save()) {
                    $t->rollBack();
                    return [
                        'code' => 1,
                        'msg' => '网络异常'
                    ];
                }
                $t->commit();
                return [
                    'code' => 0,
                    'msg' => '申请成功'
                ];
            } else {
                $t->rollBack();
                return [
                    'code' => 1,
                    'msg' => '网络异常'
                ];
            }
        } else {
            return $this->getModelError();
        }
    }
}