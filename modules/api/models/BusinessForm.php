<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/15
 * Time: 9:56
 */

namespace app\modules\api\models;

use app\extensions\getInfo;
use app\models\Business;
use app\models\Favorite;
use app\models\Goods;
use app\models\GoodsPic;
use app\models\SeckillGoods;
use app\models\User;

class BusinessForm extends Model
{
    public $id;
    public $user_id;
    public $store_id;

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['user_id'], 'safe'],
        ];
    }

    /**
     * 排序类型$sort   1--综合排序 2--销量排序
     */
    public function search()
    {
        if (!$this->validate())
            return $this->getModelError();
        $goods = Business::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'status' => 1,
            'store_id' => $this->store_id,
        ]);
        if (!$goods)
            return [
                'code' => 1,
                'msg' => '商品不存在或已下架',
            ];
        $pic_list['avatar_url'] =  User::findOne(['id' => $goods['user_id'], 'store_id' => $this->store_id])->avatar_url;
        $pic_list['nickname'] =  User::findOne(['id' => $goods['user_id'], 'store_id' => $this->store_id])->nickname;


        $is_favorite = 0;
        if ($this->user_id) {
            $exist_favorite = Favorite::find()->where(['user_id' => $this->user_id, 'goods_id' => $goods->id, 'is_delete' => 0])->exists();
            if ($exist_favorite)
                $is_favorite = 1;
        }

//      预计付费收益
        $huanledou_total =$goods->huanledou+$goods->huanledou_charge;// 需要的欢乐豆 + 总的*手续费

        return [
            'code' => 0,
            'data' => (object)[
                'id' => $goods->id,
                'userlist' => $pic_list,
                'title' => $goods->title,
                'is_exchange'=> $goods->is_exchange,
                'num' => $goods->num,
                'hld' => $goods->huanledou,
                'huanledou_charge' => $goods->huanledou_charge,
                'huanledou' => $goods->huanledou,
                'huanledou_total' => $huanledou_total,
                'xtjl' => $goods->xtjl,
                'is_favorite' => $is_favorite,
            ],
        ];
    }

    //获取商品秒杀数据
    public function getSeckillData($goods_id)
    {
        $seckill_goods = SeckillGoods::findOne([
            'goods_id' => $goods_id,
            'is_delete' => 0,
            'start_time' => intval(date('H')),
            'open_date' => date('Y-m-d'),
        ]);
        if (!$seckill_goods)
            return null;
        $attr_data = json_decode($seckill_goods->attr, true);
        $total_seckill_num = 0;
        $total_sell_num = 0;
        $seckill_price = 0.00;
        foreach ($attr_data as $i => $attr_data_item) {
            $total_seckill_num += $attr_data_item['seckill_num'];
            $total_sell_num += $attr_data_item['sell_num'];
            if ($seckill_price == 0) {
                $seckill_price = $attr_data_item['seckill_price'];
            } else {
                $seckill_price = min($seckill_price, $attr_data_item['seckill_price']);
            }
        }
        return [
            'seckill_num' => $total_seckill_num,
            'sell_num' => $total_sell_num,
            'seckill_price' => (float)$seckill_price,
            'begin_time' => strtotime($seckill_goods->open_date . ' ' . $seckill_goods->start_time . ':00:00'),
            'end_time' => strtotime($seckill_goods->open_date . ' ' . $seckill_goods->start_time . ':59:59'),
            'now_time' => time(),
        ];
    }
}