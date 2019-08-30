<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2018/03/1
 * Time: 11:05
 */

namespace app\modules\mch\models;

use Yii;
use app\models\StoreUser;

/**
 * 商城管理员登录
 */
class LoginForm extends Model
{
    public $user_name;
    public $password;
    private $_user;

    public function rules()
    {
        return [
            [['user_name', 'password'], 'required'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if (!$this->validate()) {
            return false;
        }

        /* 执行登录 */
        $identity = $this->getUser();
        $duration = 3600 * 24 * 30;
        if (Yii::$app->store->login($identity, $duration)) {
            Yii::$app->session->set('store_id', $identity->store_id);
            return true;
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return StoreUser|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = StoreUser::findByUsername($this->user_name);
        }
        return $this->_user;
    }

}