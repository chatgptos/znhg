<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/7/27
 * Time: 18:41
 */

namespace app\modules\api\models;


use app\models\Goods;
use app\models\SeckillGoods;

class GoodsAttrInfoForm extends Model
{
    public $goods_id;
    public $attr_list;

    public function rules()
    {
        return [
            [['goods_id', 'attr_list'], 'required'],
        ];
    }

    public function search()
    {
        if (!$this->validate())
            return $this->getModelError();
        $this->attr_list = json_decode($this->attr_list, true);
        $goods = Goods::findOne($this->goods_id);
        if (!$goods)
            return [
                'code' => 1,
                'msg' => '商品不存在',
            ];
        $res = $goods->getAttrInfo($this->attr_list);

        $seckill_data = $this->getSeckillData($goods, $this->attr_list);
        if ($seckill_data) {
            $seckill_data['seckill_price'] = number_format($seckill_data['seckill_price'], 2, '.', '');
            $seckill_data['rest_num'] = min((int)$res['num'], (int)$seckill_data['seckill_num']) - $seckill_data['sell_num'];
        }
        $res['seckill'] = $seckill_data;
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => $res,
        ];
    }

    /**
     * @param Goods $goods
     * @param array $attr_id_list eg.[12,34,22]
     * @return array ['attr_list'=>[],'seckill_price'=>'秒杀价格','seckill_num'=>'秒杀数量','sell_num'=>'已秒杀商品数量']
     */
    private function getSeckillData($goods, $attr_id_list = [])
    {
        $seckill_goods = SeckillGoods::findOne([
            'goods_id' => $goods->id,
            'is_delete' => 0,
            'open_date' => date('Y-m-d'),
            'start_time' => intval(date('H')),
        ]);
        if (!$seckill_goods)
            return null;
        $attr_data = json_decode($seckill_goods->attr, true);
        sort($attr_id_list);
        $seckill_data = null;
        foreach ($attr_data as $i => $attr_data_item) {
            $_tmp_attr_id_list = [];
            foreach ($attr_data_item['attr_list'] as $item) {
                $_tmp_attr_id_list[] = $item['attr_id'];
            }
            sort($_tmp_attr_id_list);
            if ($attr_id_list == $_tmp_attr_id_list) {
                $seckill_data = $attr_data_item;
                break;
            }
        }
        return $seckill_data;
    }
}