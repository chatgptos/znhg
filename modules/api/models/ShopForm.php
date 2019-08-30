<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/24
 * Time: 14:44
 */

namespace app\modules\api\models;


use app\models\Shop;
use app\models\ShopPic;

class ShopForm extends Model
{
    public $store_id;
    public $user;
    public $shop_id;


    public function rules()
    {
        return [
            [['shop_id'], 'integer']
        ];
    }

    public function search()
    {
        if (!$this->validate()) {
            $this->getModelError();
        }
        $shop = Shop::find()->where([
            'store_id' => $this->store_id, 'id' => $this->shop_id, 'is_delete' => 0
        ])->asArray()->one();

        if (!$shop) {
            return [
                'code' => 1,
                'msg' => '店铺不存在'
            ];
        }
        $shop_pic = ShopPic::find()->select(['pic_url'])->where(['store_id' => $this->store_id, 'shop_id' => $shop['id'], 'is_delete' => 0])->column();
        $shop['pic_list'] = $shop_pic;
        if (!$shop_pic) {
            $shop['pic_list'] = [$shop['pic_url']];
        }

        foreach ($shop as $index => $value) {
            if (!$value) {
                if (in_array($index, ['pic_url', 'cover_url', 'pic_list'])) {
                    continue;
                }
                $shop[$index] = "暂无设置";
            }
        }

        return [
            'code' => 0,
            'msg' => '',
            'data' => [
                'shop' => $shop
            ]
        ];
    }
}