<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/7/25
 * Time: 19:19
 */

namespace app\modules\api\models;


use app\models\Address;

class AddressDeleteForm extends Model
{
    public $store_id;
    public $user_id;
    public $address_id;

    public function rules()
    {
        return [
            [['address_id'], 'required'],
        ];
    }

    public function save()
    {
        if (!$this->validate())
            return $this->getModelError();
        $address = Address::findOne([
            'id' => $this->address_id,
            'store_id' => $this->store_id,
            'user_id' => $this->user_id,
            'is_delete' => 0,
        ]);
        if (!$address)
            return [
                'code' => 1,
                'msg' => '操作失败，收货地址不存在',
            ];
        $address->is_delete = 1;
        $address->save();
        return [
            'code' => 0,
            'msg' => '操作成功',
        ];
    }
}