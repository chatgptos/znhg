<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/7/30
 * Time: 13:22
 */

namespace app\modules\api\models;


use app\models\Favorite;

class FavoriteRemoveForm extends Model
{
    public $store_id;
    public $user_id;
    public $goods_id;

    public function rules()
    {
        return [
            [['goods_id'], 'required',],
        ];
    }

    public function save()
    {
        if (!$this->validate())
            return $this->getModelError();
        $res = Favorite::updateAll(['is_delete' => 1], [
            'store_id' => $this->store_id,
            'user_id' => $this->user_id,
            'goods_id' => $this->goods_id,
        ]);
        if ($res)
            return [
                'code' => 0,
                'msg' => 'success',
            ];
        else
            return [
                'code' => 1,
                'msg' => 'fail',
            ];
    }
}