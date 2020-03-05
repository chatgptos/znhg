<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/12/19
 * Time: 14:30
 */

namespace app\modules\api\models\couponmall;

use app\extensions\HuoGui;
use app\extensions\WxPayScoreOrder;
use app\modules\api\models\Model;
use yii\data\Pagination;

class OrderListForm extends Model
{
    public $store_id;
    public $user_id;
    public $status;
    public $page;
    public $limit;

    public function rules()
    {
        return [
            [['page', 'limit', 'status',], 'integer'],
            [['page',], 'default', 'value' => 1],
            [['limit',], 'default', 'value' => 5],
        ];
    }


    public function search()
    {
        if (!$this->validate())
            return $this->getModelError();

        $query = Order::find()
            ->alias('o')
            ->select([
                'o.*',
                'g.name AS goods_name',
                'g.cover_pic',
                'g.original_price',
            ])
            ->where([
            'o.is_delete' => 0,
            'o.store_id' => $this->store_id,
            'o.user_id' => $this->user_id,
            'o.is_cancel' => 0,
        ]);
        $query->leftJoin(['g'=>Goods::tableName()],'o.goods_id=g.id');
        if ($this->status == 0) {//待付款
            $query->andWhere([
                'o.is_pay' => 0,
                'o.is_cancel' => 0,
            ]);

        }
        if ($this->status == 1) {//待使用
            $query->andWhere([
                'o.is_pay' => 1,
                'o.is_use' => 0,
                'o.is_cancel' => 0,
                'o.apply_delete' => 0,
                'o.is_refund' => 0,
            ]);
        }
        if ($this->status == 2) {// 已使用
            $query->andWhere([
                'o.is_pay' => 1,
                'o.is_use' => 1,
//                'o.is_comment' => 0,
            ]);
        }
        if ($this->status == 3) {//退款
            $query->andWhere([
                'o.is_pay' => 1,
                'o.apply_delete' => 1,
            ]);
        }

        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'page' => $this->page - 1, 'pageSize' => $this->limit]);
        $list = $query
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->orderBy('addtime DESC')
            ->asArray()
            ->all();

        return [
            'code'  => 0,
            'msg'   => 'success',
            'data'  => [
                'row_count' => $count,
                'page_count' => $pagination->pageCount,
                'list' => $list,
            ],
        ];

    }







    public function actionOrderDetailshg($opendoorRecordId)
    {
        //查询是否生成订单
        if(!$opendoorRecordId){
            return [
                'code'  => 1,
                'msg'   => '必须opendoorRecordId',
                'success'  => false,
                'data'  => false,
            ];
        }
        $biz_content=array(
            "unionid"=>\Yii::$app->user->identity->wechat_open_id,
            "opendoorRecordId"=>$opendoorRecordId,
        );
        $HuoGui = new HuoGui();
        //成功关门查询订单
        $res= $HuoGui->getOrdersByOpenDoorId($biz_content);
//        $res='{
//    "msg":"",
//    "code":200,
//    "success":true,
//    "data":{
//        "errTag":2,
//        "orderNo":"2019081237682398",
//        "payTime":1565603026000,
//        "totalGoodCount":1,
//        "goodsList":[
//            {
//                "id":340,
//                "goodsName":"上好佳",
//                "imgUrl":"http://images.voidiot.com/FtZleANQ-HyskUgRhU6rSWMQfjZ_",
//                "price":0.03,
//                "baseWeight":null,
//                "count":1,
//                "valuatType":0,
//                "sourPrice":0,
//                "discount":0,
//                "weight":9,
//                "deviceId":100015,
//                "trayNum":4,
//                "merchantId":10000,
//                "goodsId":948,
//                "ch1":4,
//                "ch2":16
//            }
//        ],
//        "payWay":1,
//        "deviceName":"待初始化",
//        "userId":10881,
//        "payMoney":0.02,
//        "createTime":1565603025000,
//        "price":0.03,
//        "id":2995,
//        "status":1
//    },
//    "fail":false
//}';

        //开始请求数据
        $res =json_decode($res,true);
        if ($res['success']==true) {
            $data = $res['data'];
            $goodsList = $data['goodsList'];
            //订单
            $order_no=$data['orderNo'];
            if($order_no){
                //创建订单

//                $HuoGui = new WxPayScoreOrder();
//                echo '<pre>';
//                $out_order_no="234323JKHDFE1243252Ba";
//                $res= $HuoGui->queryOrder($out_order_no);//补货开门
////        var_dump(json_decode($res, true));
//                $res= $HuoGui->serviceorder($out_order_no);//补货开门
            }




        }else{
            return [
                'code'  => 1,
                'msg'   => '未创建订单',
                'data'  => $res,
            ];
        }

//        var_dump($goodsList);

        $new_list = [];
        foreach ($goodsList as $item) {
            $attr_list[] = [
                'attr_group_name'=>'来源',
                'attr_name'=>'智能货柜',
            ];
            $attr_num = 99;
            $num =$item['count'];
            $goods_id =$item['id'];
            $goods_pic =$item['imgUrl'];
            $goods_name =$item['goodsName'];
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

        $list=array(
            'integral'=>1,
            'coupon'=>1,
            'total_price'=>$data['price'],
            'pay_price'=>$data['payMoney'],
            'is_use'=>1,
            'order_no'=>$data['orderNo'],
            'original_price'=>$data['price'],
            'pay_time'=>date('Y-m-d h:m:s',$data['payTime']),
            'pay_type'=>1,
            'shop_id'=>1,
            'store_id'=>1,
            'use_time'=>date('Y-m-d h:m:s',$data['payTime']),
            'user_id'=>$this->user_id,
            'addtime'=>date('Y-m-d h:m:s',$data['createTime']),
            'cover_pic'=>$goods_pic,
            'goods_id'=>$goods_id,
            'goods_name'=>$goods_name,
            'apply_delete'=>0,
            'clerk_id'=>0,
            'form_id'=>0,
            'is_cancel'=>0,
            'is_comment'=>0,
            'is_delete'=>0,
            'is_pay'=>1,
            'is_refund'=>0,
            'offline_qrcode'=>0,
        );
        return [
            'code'  => 0,
            'msg'   => 'success',
            'success'  => true,
            'data'  => $list,
        ];

    }

}