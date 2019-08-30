<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2018/1/3
 * Time: 9:20
 */

namespace app\modules\mch\models;

use app\models\Goods;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\Share;
use app\models\Shop;
use app\models\User;
use yii\data\Pagination;

class ShareOrderForm extends Model
{

    public $store_id;
    public $user_id;
    public $keyword;
    public $status;
    public $page;
    public $limit;

    public $flag;//是否导出
    public $is_offline;
    public $clerk_id;
    public $parent_id;
    public $shop_id;

    public function rules()
    {
        return [
            [['keyword',], 'trim'],
            [['status', 'page', 'limit', 'user_id', 'is_offline', 'clerk_id', 'shop_id'], 'integer'],
            [['status',], 'default', 'value' => -1],
            [['page',], 'default', 'value' => 1],
            //[['limit',], 'default', 'value' => 20],
            [['flag'], 'trim'],
            [['flag'], 'default', 'value' => 'no']
        ];
    }

    public function search()
    {
        if (!$this->validate())
            return $this->getModelError();
        $query = Order::find()->alias('o')->where([
            'o.store_id' => $this->store_id,'o.is_cancel'=>0,'o.is_delete'=>0
        ])->leftJoin(['u' => User::tableName()], 'u.id=o.user_id');
        if ($this->status == 0) {//未付款
            $query->andWhere([
                'o.is_pay' => 0,
            ]);
        }
        if ($this->status == 1) {//待发货
            $query->andWhere([
                'o.is_pay' => 1,
                'o.is_send' => 0,
            ]);
        }
        if ($this->status == 2) {//待确认收货
            $query->andWhere([
                'o.is_pay' => 1,
                'o.is_send' => 1,
                'o.is_confirm' => 0,
            ]);
        }
        if ($this->status == 3) {//已确认收货
            $query->andWhere([
                'o.is_pay' => 1,
                'o.is_send' => 1,
                'o.is_confirm' => 1,
            ]);
        }
        if ($this->parent_id) {
            $query->andWhere(['o.parent_id' => $this->parent_id]);
        }else{
            $query->andWhere(['>','o.parent_id',0]);
        }


        if ($this->keyword) {//关键字查找
            $query->andWhere([
                'OR',
                ['LIKE', 'o.id', $this->keyword],
                ['LIKE', 'o.order_no', $this->keyword],
                ['LIKE', 'o.name', $this->keyword],
                ['LIKE', 'o.mobile', $this->keyword],
                ['LIKE', 'o.address', $this->keyword],
                ['LIKE', 'o.remark', $this->keyword],
                ['LIKE', 'o.express_no', $this->keyword],
                ['LIKE', 'u.nickname', $this->keyword],
            ]);
        }
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => $this->limit, 'page' => $this->page - 1]);
        $list = $query->limit($pagination->limit)->offset($pagination->offset)->orderBy('o.addtime DESC')
            ->select('o.*,u.nickname')->asArray()->all();
        foreach ($list as $i => $item) {
            $list[$i]['goods_list'] = $this->getOrderGoodsList($item['id']);
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
            $share = Share::find()->alias('s')->where(['s.user_id' => $item['parent_id'], 's.store_id' => $this->store_id, 's.is_delete' => 0])
                ->leftJoin(User::tableName() . ' u', 'u.id=s.user_id')->select([
                    'u.nickname', 's.name', 's.mobile'
                ])->asArray()->one();
            $list[$i]['share'] = $share;
            $share_1 = Share::find()->alias('s')->where(['s.user_id' => $item['parent_id_1'], 's.store_id' => $this->store_id, 's.is_delete' => 0])
                ->leftJoin(User::tableName() . ' u', 'u.id=s.user_id')->select([
                    'u.nickname', 's.name', 's.mobile'
                ])->asArray()->one();
            $list[$i]['share_1'] = $share_1;
            $share_2 = Share::find()->alias('s')->where(['s.user_id' => $item['parent_id_2'], 's.store_id' => $this->store_id, 's.is_delete' => 0])
                ->leftJoin(User::tableName() . ' u', 'u.id=s.user_id')->select([
                    'u.nickname', 's.name', 's.mobile'
                ])->asArray()->one();
            $list[$i]['share_2'] = $share_2;
        }
        return [
            'row_count' => $count,
            'page_count' => $pagination->pageCount,
            'pagination' => $pagination,
            'list' => $list,
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
}