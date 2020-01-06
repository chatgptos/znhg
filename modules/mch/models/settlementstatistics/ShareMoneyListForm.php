<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/2
 * Time: 14:01
 */

namespace app\modules\mch\models\settlementstatistics;


use app\models\Goods;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\Shop;
use app\models\User;
use app\models\UserShareMoney;
use app\modules\mch\models\Model;
use yii\data\Pagination;

class ShareMoneyListForm extends Model
{
    public $store_id;


    public $page;
    public $limit;
    public $keyword;

    public function rules()
    {
        return [
            [['page'],'default','value'=>1],
            [['limit'],'default','value'=>20],
            [['keyword'],'trim'],
            [['keyword'],'string'],
        ];
    }

    public function search()
    {
        if(!$this->validate()){
            return $this->getModelError();
        }

        $query = UserShareMoney::find() ->alias('usm')
            ->select('usm.id,o.order_no,o.order_no,o.order_no,o.order_no,u.nickname,us.nickname as us_nickname,o.order_no,usm.user_id,usm.money,usm.order_id,usm.type,usm.status,usm.source,usm.is_delete,usm.addtime as addtime')
            ->leftJoin(['o' => Order::tableName()], 'o.id=usm.order_id')
            ->leftJoin(['u' => User::tableName()], 'u.id=o.user_id')
            ->leftJoin(['us' => User::tableName()], 'us.id=usm.user_id')
            ->where(['usm.store_id'=>$this->store_id,'usm.is_delete'=>0]);

        if($this->keyword){
//            $query->where(['usm.user_id'=>$this->keyword]);
//                ->andWhere(['like','user_id',$this->keyword]);

            $query->andWhere([
                'or',
                ['usm.user_id'=>$this->keyword],
                ['o.order_no'=>$this->keyword],
            ]);
        }

        $count = $query->count();
        $p = new Pagination(['totalCount'=>$count,'pageSize'=>$this->limit]);
        $list = $query->offset($p->offset)->limit($p->limit)->orderBy(['addtime'=>SORT_ASC])->asArray()->all();
        foreach ($list as $i => $item) {
            $list[$i]['goods_list'] = $this->getOrderGoodsList($item['order_id']);
            if ($item['is_offline'] == 1 && $item['is_send'] == 1) {
                $user = User::findOne(['id' => $item['clerk_id'], 'store_id' => $this->store_id]);
                $list[$i]['clerk_name'] = $user->nickname;
            }
            if ($item['shop_id'] && $item['shop_id'] != 0) {
                $shop = Shop::find()->where(['store_id' => $this->store_id, 'id' => $item['shop_id']])->asArray()->one();
                $list[$i]['shop'] = $shop;
            }
            $order_refund = OrderRefund::findOne(['store_id' => $this->store_id, 'order_id' => $item['id'], 'is_delete' => 0]);
            $list[$i]['refund'] = "";
            if ($order_refund) {
                $list[$i]['refund'] = $order_refund->status;
            }
            $list[$i]['integral'] = json_decode($item['integral'], true);
        }
        return [
            'list'=>$list,
            'p'=>$p,
            'row_count'=>$count
        ];
    }

    public function getOrderGoodsList($order_id)
    {
        $order_detail_list = OrderDetail::find()->alias('od')
            ->leftJoin(['g' => Goods::tableName()], 'od.goods_id=g.id')
            ->where([
                'od.is_delete' => 0,
                'od.order_id' => $order_id,
            ])->select('od.*,g.name,g.unit')->asArray()->all();
        foreach ($order_detail_list as $i => $order_detail) {
            $goods = new Goods();
            $goods->id = $order_detail['goods_id'];
            $order_detail_list[$i]['goods_pic'] = $goods->getGoodsPic(0)->pic_url;
            $order_detail_list[$i]['attr_list'] = json_decode($order_detail['attr']);
        }
        return $order_detail_list;
    }

    public function searchName()
    {
        if(!$this->validate()){
            return $this->getModelError();
        }

        $list = UserShareMoney::find()->where(['store_id'=>$this->store_id,'is_delete'=>0,'status'=>1])->orderBy(['level'=>SORT_ASC])->asArray()->all();

        $award =[];
        $num =[];
        $quan =[];
        $mkawardlist =[];
        $award =[];
        $money=0;
        foreach ($list as $key =>$value){
            $mkawardlist[$key]=array(
                'id'=>$key+1,
                'prize'=>$value['name'],
                'v'=>$value['chance'],
            );
            $quan[$key]=$value['quan'];
            $award[$key]=$value['name'];
            $num[$key]=$value['discount'];
            $money=$value['money'];
        }

        $awardlist=array(
            'name'=>$award,
            'money'=>$money,
            'num'=>$num,
            'quan'=>$quan,
            'mkawardlist'=>$mkawardlist,
        );

        return $awardlist;
    }
}