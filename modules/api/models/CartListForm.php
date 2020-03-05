<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/7/15
 * Time: 14:33
 */

namespace app\modules\api\models;


use app\extensions\HuoGui;
use app\extensions\WxPayScoreOrder;
use app\models\Attr;
use app\models\AttrGroup;
use app\models\Cart;
use app\models\Goods;
use app\models\SeckillGoods;
use app\models\Shop;
use yii\data\Pagination;

class CartListForm extends Model
{
    public $store_id;
    public $user_id;
    public $page;
    public $limit;

    public function rules()
    {
        return [
            [['page', 'limit'], 'integer'],
            [['page',], 'default', 'value' => 1],
            [['limit',], 'default', 'value' => 20],
        ];
    }

    public function search()
    {
        $query = Cart::find()->where(['store_id' => $this->store_id, 'user_id' => $this->user_id, 'is_delete' => 0]);
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'page' => $this->page - 1]);
        /* @var Cart[] $list */
        $list = $query->orderBy('goods_id DESC')->limit($pagination->limit)->offset($pagination->offset)->all();
        $new_list = [];
        foreach ($list as $item) {
            $goods = Goods::findOne([
                'id' => $item->goods_id,
                'is_delete' => 0,
                'status' => 1,
            ]);
            if (!$goods)
                continue;
            $attr_list = Attr::find()->alias('a')
                ->select('ag.attr_group_name,a.attr_name,')
                ->leftJoin(['ag' => AttrGroup::tableName()], 'a.attr_group_id=ag.id')
                ->where(['a.id' => json_decode($item->attr, true)])
                ->asArray()->all();
            $goods_attr_info = $goods->getAttrInfo(json_decode($item->attr, true));
            $attr_num = intval(empty($goods_attr_info['num']) ? 0 : $goods_attr_info['num']);
            $goods_pic = isset($goods_attr_info['pic'])?$goods_attr_info['pic']?:$goods->getGoodsPic(0)->pic_url:$goods->getGoodsPic(0)->pic_url;
            $new_item = (object)[
                'cart_id' => $item->id,
                'goods_id' => $goods->id,
                'goods_name' => $goods->name,
                'goods_pic' => $goods_pic,
                'num' => $item->num,
                'attr_list' => $attr_list,
                'price' => doubleval(empty($goods_attr_info['price']) ? $goods->price : $goods_attr_info['price']) * $item->num,
                'max_num' => $attr_num,
                'disabled' => ($item->num > $attr_num) ? true : false,
            ];

            //秒杀价计算
            $seckill_data = $this->getSeckillData($goods, json_decode($item->attr, true));
            if ($seckill_data) {
                $temp_price = $this->getSeckillPrice($seckill_data, $goods, json_decode($item->attr, true), $item->num);
                if ($temp_price !== false)
                    $new_item->price = $temp_price;
            }

            $new_list[] = $new_item;
        }
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'row_count' => $count,
                'page_count' => $pagination->pageCount,
                'list' => $new_list,
            ],
        ];
    }



    public function searchHg()
    {
        //查询实时货柜信息
        $hg_id = \Yii::$app->request->post('hg_id');
        //创建订单
        $opendoorRecordId = \Yii::$app->request->post('opendoorRecordId');
        $isreplenish =  \Yii::$app->request->post('isreplenish');
        //调用实时数据
        $biz_content=array(
            "deviceId"=>$hg_id,//必须要有设备
            "unionid"=>\Yii::$app->user->identity->wechat_open_id,
        );



        $shop = Shop::findOne(['hg_id' => $hg_id, 'store_id' => $this->store_id, 'is_delete' => 0]);
        if (!$shop) {
            return json_encode([
                'code'  => '1',
                'msg'   => '该货柜没有还未人抢购，未配置',
                'success'   => false,
                'data'  => '该货柜没有还未人抢购，未配置',
            ],JSON_UNESCAPED_UNICODE);
        }

        $HuoGui = new HuoGui();
        if($isreplenish){
            $goods= $HuoGui->getDeviceRealTimeGoods($biz_content);
        }else{
            $goods= $HuoGui->getSelectGoods($biz_content);
        }
        $goods='{
                "msg":"",
                "code":200,
                "success":true,
                "data":{
                    "isClose":true,
                    "goodsList":[
                        {
                            "goodsName":"瓶装饮料",
                            "imgUrl":"http://images.voidiot.com/Foj2Z9bLgpPuueLMnKd5e6RN10oh",
                            "price":3,
                            "count":2,
                            "valuatType":0,
                            "weight":1070,
                            "baseWeight":550,
                            "deviceId":100023,
                            "trayNum":4,
                            "sourPrice":0,
                            "discount":null
                        },{
                            "goodsName":"瓶装饮料",
                            "imgUrl":"http://images.voidiot.com/Foj2Z9bLgpPuueLMnKd5e6RN10oh",
                            "price":3,
                            "count":2,
                            "valuatType":0,
                            "weight":1070,
                            "baseWeight":550,
                            "deviceId":100023,
                            "trayNum":4,
                            "sourPrice":0,
                            "discount":null
                        }
                    ]
                },
                "fail":false
            }';

        $res =json_decode($goods,true);



        //加工返回参数
        $data=$res['data'];
        $list=$data['goodsList'];
        $isClose=$data['isClose'];
        //购物车显示
        $new_list = [];
        foreach ($list as $item) {
            $attr_list[] = [
                'attr_group_name'=>'来源',
                'attr_name'=>'智能货柜',
            ];
            $attr_num = 99;
            $num =$item['count'];
            $goods_pic =$item['imgUrl'];
            $new_item = (object)[
                'cart_id' => $item['categoryId'],
                'goods_id' =>$item['goodsId'],
                'goods_name' =>$item['goodsName'],
                'goods_pic' => $goods_pic,
                'num' =>$num,
                'attr_list' => $attr_list,
                'price' =>$item['price'],
                'max_num' => $attr_num,
                'disabled' => ($num > $attr_num) ? true : false,
            ];

            $new_list[] = $new_item;
        }

        //开始判断逻辑
        if ($res['success']==true){
            if($isClose){
                //这是管理员补货标记 过滤返回规定格式参数
                if(empty($isreplenish)){
                    $isreplenish=false;
                }
                if($isreplenish){
                    //直接返回
                    return [
                        'code' => 0,
                        'msg' => 'success',
                        'data' => [
                            'isClose' => true,
                            'opendoorRecordId' => $opendoorRecordId,
                            'data' => $res['data'],
                            'isreplenish'=>$isreplenish,
                            'order_no' => $res['data']['order_no'],
                        ],
                    ];
                }

                //如果是用户继续往下走
                //如果关门 订单显示
                //支付订单
                //查询订单 查询货柜生成的订单---生成的订单支付
                $form = new \app\modules\api\models\couponmall\OrderListForm();
                $res = $form->actionOrderDetailshg($opendoorRecordId,true);
                //如果成功生成货柜订单+微信订单
                if($res['success']){
                    $pay_data=[];
                    if(true){
                        //没有授权，开始授权
                        $WxPayScoreOrder = new WxPayScoreOrder();
                        $out_order_no=$res['data']['order_no'];
                        $pay_data= $WxPayScoreOrder->wxpayScoreDetail($out_order_no);//获得微信分授权参数
                    }
                    if ($shop->hg_yx){
                        $isWechatJump=true;
                    }else{
                        $isWechatJump=false;
                    }
                    return [
                        'code' => 0,
                        'msg' => 'success',
                        'data' => [
                            'isWechatJump' => $isWechatJump,
                            'isClose' => true,
                            'opendoorRecordId' => $opendoorRecordId,
                            'data' => $res['data'],
                            'pay_data' => $pay_data,
                            'isreplenish'=>$isreplenish,
                            'order_no' => $res['data']['order_no'],
                        ],
                    ];
                }
            }
        }
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'row_count' => count($new_item),
                'page_count' => 10,
                'list' => $new_list,
            ],
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

    /**
     * 获取商品秒杀价格，若库存不足则使用商品原价，若有部分库存，则部分数量使用秒杀价，部分使用商品原价，商品库存不足返回false
     * @param array $seckill_data ['attr_list'=>[],'seckill_price'=>'秒杀价格','seckill_num'=>'秒杀数量','sell_num'=>'已秒杀商品数量']
     * @param Goods $goods
     * @param array $attr_id_list eg.[12,34,22]
     * @param integer $buy_num 购买数量
     *
     * @return false|float
     */
    private function getSeckillPrice($seckill_data, $goods, $attr_id_list, $buy_num)
    {
        $attr_data = json_decode($goods->attr, true);
        sort($attr_id_list);
        $goost_attr_data = null;
        foreach ($attr_data as $i => $attr_data_item) {
            $_tmp_attr_id_list = [];
            foreach ($attr_data_item['attr_list'] as $item) {
                $_tmp_attr_id_list[] = intval($item['attr_id']);
            }
            sort($_tmp_attr_id_list);
            if ($attr_id_list == $_tmp_attr_id_list) {
                $goost_attr_data = $attr_data_item;
                break;
            }
        }
        $goods_price = $goost_attr_data['price'];
        if (!$goods_price)
            $goods_price = $goods->price;

        $seckill_price = min($seckill_data['seckill_price'], $goods_price);

        if ($buy_num > $goost_attr_data['num'])//商品库存不足
        {
            \Yii::warning([
                'res' => '库存不足',
                'm_data' => $seckill_data,
                'g_data' => $goost_attr_data,
                '$attr_id_list' => $attr_id_list,
            ]);
            return false;
        }

        if ($buy_num <= ($seckill_data['seckill_num'] - $seckill_data['sell_num'])) {
            \Yii::warning([
                'res' => '库存充足',
                'price' => $buy_num * $seckill_price,
                'm_data' => $seckill_data,
            ]);
            return $buy_num * $seckill_price;
        }

        $seckill_num = ($seckill_data['seckill_num'] - $seckill_data['sell_num']);
        $original_num = $buy_num - $seckill_num;

        \Yii::warning([
            'res' => '部分充足',
            'price' => $seckill_num * $seckill_price + $original_num * $goods_price,
            'm_data' => $seckill_data,
        ]);
        return $seckill_num * $seckill_price + $original_num * $goods_price;
    }

}