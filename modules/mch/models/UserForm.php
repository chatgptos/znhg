<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/3
 * Time: 13:54
 */

namespace app\modules\mch\models;

/**
 * @property \app\models\User $user
 */
class UserForm extends Model
{
    public $store_id;
    public $user;


    public $level;


    public function rules()
    {
        return [
            [['level'],'integer']
        ];
    }

    public function attributeLabels()
    {
        return [
            'level'=>'会员等级'
        ];
    }

    public function save()
    {
        if(!$this->validate()){
            return $this->getModelError();
        }

        $this->user->level = $this->level;

        if($this->user->save()){
            return [
                'code'=>0,
                'msg'=>'成功'
            ];
        }else{
            return $this->getModelError($this->user);
        }
    }
}