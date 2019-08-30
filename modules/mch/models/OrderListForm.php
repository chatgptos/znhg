<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/7/20
 * Time: 14:34
 */

namespace app\modules\mch\models;


use app\models\Goods;
use app\models\Order;
use app\models\OrderDetail;
use app\models\OrderRefund;
use app\models\Share;
use app\models\Shop;
use app\models\User;
use app\modules\mch\extensions\Export;
use yii\data\Pagination;

class OrderListForm extends Model
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

    public $date_start;
    public $date_end;
    public $express_type;
    public $keyword_1;

    public function rules()
    {
        return [
            [['keyword',], 'trim'],
            [['status', 'page', 'limit', 'user_id', 'is_offline', 'clerk_id', 'shop_id', 'keyword_1'], 'integer'],
            [['status',], 'default', 'value' => -1],
            [['page',], 'default', 'value' => 1],
            //[['limit',], 'default', 'value' => 20],
            [['flag', 'date_start', 'date_end', 'express_type'], 'trim'],
            [['flag'], 'default', 'value' => 'no']
        ];
    }

    public function search()
    {
        if (!$this->validate())
            return $this->getModelError();
        $query = Order::find()->alias('o')->where([
            'o.store_id' => $this->store_id,
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
        if ($this->status == 4) {//售后
        }
        if ($this->status == 5) {//已取消订单
            $query->andWhere(['or', ['o.is_cancel' => 1], ['o.apply_delete' => 1, 'o.is_delete' => 1]]);
        } else {
            $query->andWhere(['o.is_cancel' => 0, 'o.is_delete' => 0]);
        }
        if ($this->status != 5) {
            if ($this->user_id) {//查找指定用户的
                $query->andWhere([
                    'o.user_id' => $this->user_id,
                ]);
            }
            if ($this->clerk_id) {//查找指定核销员的订单
                $query->andWhere([
                    'o.clerk_id' => $this->clerk_id,
                ]);
            }
            if ($this->shop_id) {//查找指定门店的订单
                $query->andWhere([
                    'o.shop_id' => $this->shop_id,
                ]);
            }
            if ($this->parent_id) {
                $query->andWhere(['o.parent_id' => $this->parent_id]);
            }
        }
        if ($this->date_start) {
            $query->andWhere(['>=', 'o.addtime', strtotime($this->date_start)]);
        }
        if ($this->date_end) {
            $query->andWhere(['<=', 'o.addtime', strtotime($this->date_end)]);
        }

        if ($this->keyword) {//关键字查找
            if($this->keyword_1 == 1){
                $query->andWhere(['like', 'o.order_no', $this->keyword]);
            }
            if($this->keyword_1 == 2){
                $query->andWhere(['like', 'u.nickname', $this->keyword]);
            }
            if($this->keyword_1 == 3){
                $query->andWhere(['like', 'o.name', $this->keyword]);
            }
//            $query->andWhere([
//                'OR',
//                ['LIKE', 'o.id', $this->keyword],
//                ['LIKE', 'o.order_no', $this->keyword],
//                ['LIKE', 'o.name', $this->keyword],
//                ['LIKE', 'o.mobile', $this->keyword],
//                ['LIKE', 'o.address', $this->keyword],
//                ['LIKE', 'o.remark', $this->keyword],
//                ['LIKE', 'o.express_no', $this->keyword],
//                ['LIKE', 'u.nickname', $this->keyword],
//            ]);
        }
        if ($this->is_offline) {
            $query->andWhere(['o.is_offline' => $this->is_offline]);
        }
        $query1 = clone $query;
        if ($this->flag == "EXPORT") {
            $list_ex = $query1->select('o.*,u.nickname')->orderBy('o.addtime DESC')->asArray()->all();
            foreach ($list_ex as $i => $item) {
                $list_ex[$i]['goods_list'] = $this->getOrderGoodsList($item['id']);
            }
            Export::order_2($list_ex);
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

    public static function getCountData($store_id)
    {
        $form = new OrderListForm();
        $form->limit = 0;
        $form->store_id = $store_id;
        $data = [];

        $form->status = -1;
        $res = $form->search();
        $data['all'] = $res['row_count'];

        $form->status = 0;
        $res = $form->search();
        $data['status_0'] = $res['row_count'];

        $form->status = 1;
        $res = $form->search();
        $data['status_1'] = $res['row_count'];

        $form->status = 2;
        $res = $form->search();
        $data['status_2'] = $res['row_count'];

        $form->status = 3;
        $res = $form->search();
        $data['status_3'] = $res['row_count'];

        $form->status = 5;
        $res = $form->search();
        $data['status_5'] = $res['row_count'];

        return $data;
    }
}