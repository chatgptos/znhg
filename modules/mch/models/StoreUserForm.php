<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/6/28
 * Time: 9:51
 */

namespace app\modules\mch\models;

use app\models\StoreUser;

/**
 * 商城后台账户设置
 * Class StoreUserForm
 * @package app\modules\mch\models
 */
class StoreUserForm extends Model
{
    public $user_id;
    public $user_name;
    public $password;
    public $repassword;
    public $store_id;



    public function rules()
    {
        return [
            ['user_name', 'trim'],
            ['user_name', 'required'],
            ['user_name', 'string', 'min' => 2, 'max' => 16],

            ['password', 'string', 'min' => 6, 'max' => 16],
            ['password', 'validatePassword'],

            ['repassword', 'string']
        ];
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array(
            'user_name' => '用户名',
            'password' => '密码',
            'repassword' => '确认密码',
        );
    }

    /**
     * 验证确认密码
     * @param $attribute
     */
    public function validatePassword($attribute)
    {
        if (!$this->hasErrors()) {
            if ($this->password !== $this->repassword) {
                $this->addError($attribute, '请输入正确的确认密码');
            }
        }
    }

    /**
     * 更新账户信息
     * @param $data
     * @return array|bool
     * @throws \yii\base\Exception
     */
    public function update($data)
    {
        if ($this->load($data) && $this->validate()) {
            $model = StoreUser::findOne($this->user_id);
            $model->user_name = $this->user_name;
            if (!empty($this->password)) {
                $model->setPassword($this->password);
            }
            $model->save(false);
            return ['code' => 0, 'msg' => '保存成功'];
        }
        return $this->getModelError();
    }



    /**
     * 更新账户信息
     * @param $data
     * @return array|bool
     * @throws \yii\base\Exception
     */
    public function add($data)
    {
        if ($this->load($data) && $this->validate()) {


            $name = StoreUser::findByUsername($this->user_name);
            if($name){
                return ['code' => 1, 'msg' => '用户已经存在'];
            }else{
                if (!empty($this->password)) {
                    $StoreUser = new StoreUser();
                    $StoreUser->user_name = $this->user_name;
                    $StoreUser->store_id = $this->store_id;
                    $StoreUser->setPassword($this->password);
                    $StoreUser->auth_key =$StoreUser->getgenerateAuthKey();
                    $StoreUser->password =$StoreUser->getPassword();
                }
                $StoreUser->save(false);
            }
            return ['code' => 0, 'msg' => '保存成功'];
        }
        return $this->getModelError();
    }

}