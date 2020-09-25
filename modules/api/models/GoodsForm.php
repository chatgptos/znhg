<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/15
 * Time: 9:56
 */

namespace app\modules\api\models;

use app\extensions\getInfo;
use app\models\Favorite;
use app\models\Goods;
use app\models\GoodsPic;
use app\models\Room;
use app\models\SeckillGoods;

class GoodsForm extends Model
{
    public $id;
    public $user_id;
    public $store_id;
    public $room_id;

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
        $goods = Goods::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'status' => 1,
            'store_id' => $this->store_id,
        ]);

//        $this->room_id=122;
//
//        if($this->room_id){
////            $Room = Room::findOne(['room_id'=>$this->room_id,'store_id'=>$this->store_id]);
////
////
////            var_dump($Room);die;
////            if($Room){
////               //绑定用户关系
////                //登入状态
////                //新人增加
////                $res=User::updateAll(
////                    ['parent_id' => $order->user_id,'is_distributor' => 1,'time'=>time(),'integral' => \Yii::$app->user->identity->integral+intval($integral)],
////                    ['id' => \Yii::$app->user->identity->id]
////                );
////                //本人
////                $user=\Yii::$app->user->identity;
////                //如果当前用户登入了但是没有上级
////                if(!\Yii::$app->user->identity->parent_id && $user) {
////                    //新增功能 来自智能鲜蜂服务点的订单只要注册了判定没有上级 上级的user
////                    $user_shop = User::findOne(['shop_id' => $order->user_id, 'store_id' => $this->store_id]);
////                    //修改当前用户的上级
////                    //修改上级出错不抛出
////                    //先简单使用//注册成功一个并且开门赠送1积分，没有上级的
////                    //后期对接到商城
////                    $integral = '1.00';//赠送积分
////                    $coupon = 2;//赠送券
////
////                    //新人增加
////                    $res = User::updateAll(
////                        ['parent_id' => $user_shop->id, 'is_distributor' => 1, 'time' => time(), 'coupon' => \Yii::$app->user->identity->coupon + $coupon, 'integral' => \Yii::$app->user->identity->integral + intval($integral)],
////                        ['id' => \Yii::$app->user->identity->id]
////                    );
////                }
////            }
//        }

        if (!$goods)
            return [
                'code' => 1,
                'msg' => '商品不存在或已下架',
            ];
        $pic_list = GoodsPic::find()->select('pic_url')->where(['goods_id' => $goods->id, 'is_delete' => 0])->asArray()->all();
        $is_favorite = 0;
        if ($this->user_id) {
            $exist_favorite = Favorite::find()->where(['user_id' => $this->user_id, 'goods_id' => $goods->id, 'is_delete' => 0])->exists();
            if ($exist_favorite)
                $is_favorite = 1;
        }
        $service_list = explode(',', $goods->service);
        $new_service_list = [];
        if (is_array($service_list))
            foreach ($service_list as $item) {
                $item = trim($item);
                if ($item)
                    $new_service_list[] = $item;
            }
        $res_url = getInfo::getVideoInfo($goods->video_url);
        $goods->video_url = $res_url['url'];
        return [
            'code' => 0,
            'data' => (object)[
                'id' => $goods->id,
                'pic_list' => $pic_list,
                'name' => $goods->name,
                'price' => floatval($goods->price),
                'detail' => $goods->detail,
                'sales_volume' => $goods->getSalesVolume() + $goods->virtual_sales,
                'attr_group_list' => $goods->getAttrGroupList(),
                'num' => $goods->getNum(),
                'is_favorite' => $is_favorite,
                'service_list' => $new_service_list,
                'original_price' => floatval($goods->original_price),
                'video_url' => $goods->video_url,
                'unit' => $goods->unit,
                'seckill' => $this->getSeckillData($goods->id),
                'use_attr' => intval($goods->use_attr),
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