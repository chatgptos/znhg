<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2018/1/3
 * Time: 14:38
 */

namespace app\modules\mch\models;


use app\models\WechatApp;

class WechatSettingForm extends Model
{
    /** @var WechatApp $model */
    public $model;
    public $app_id;
    public $app_secret;
    public $mch_id;
    public $key;
    public $cert_pem;
    public $key_pem;

    public function rules()
    {
        return [
            [['app_id', 'app_secret', 'mch_id', 'key', 'cert_pem', 'key_pem'], 'trim'],
            [['app_id', 'app_secret', 'mch_id', 'key', 'model'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'app_id' => '小程序AppId',
            'app_secret' => '小程序AppSecret',
            'mch_id' => '微信支付商户号',
            'key' => '微信支付Api密钥',
        ];
    }

    public function save()
    {
        if (!$this->validate())
            return $this->getModelError();
        $this->model->attributes = $this->attributes;
        if ($this->model->save())
            return [
                'code' => 0,
                'msg' => '保存成功',
            ];
        return $this->getModelError($this->model);
    }
}