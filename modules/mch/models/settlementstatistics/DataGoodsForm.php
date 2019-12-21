<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2018/1/6
 * Time: 12:00
 */

namespace app\modules\mch\models\settlementstatistics;


use app\models\Cat;
use app\models\Model;
use app\models\Order;
use app\models\PtGoods;
use app\models\PtOrder;
use app\models\PtOrderDetail;
use app\models\User;
use yii\data\Pagination;

class DataGoodsForm extends Model
{
    public $store_id;
    public $status;

    public $limit;
    public $page;
    public $keyword;

    public function rules()
    {
        return [
            [['status', 'limit', 'page'], 'integer'],
            [['page'], 'default', 'value' => 1],
            [['limit'], 'default', 'value' => 20],
            [['status'], 'default', 'value' => 1],
            [['keyword'], 'trim'],
            [['keyword'], 'string'],
        ];
    }

    /**
     * @return array
     * $status //1--销量排序  2--销售额排序
     */
    public function search()
    {
        if (!$this->validate()) {
            return $this->getModelError();
        }
        $query = PtGoods::find()->alias('g')->where(['g.is_delete' => 0, 'g.store_id' => $this->store_id])
            ->leftJoin(['od' => PtOrderDetail::tableName()], 'od.goods_id = g.id')
            ->leftJoin(['o' => PtOrder::tableName()], 'o.id = od.order_id')
            ->andWhere([
                'or',
                ['od.is_delete' => 0, 'o.is_delete' => 0, 'o.is_pay' => 1, 'o.is_success' => 1],
                'isnull(od.id)'
            ])->groupBy('g.id');

//        $query = Goods::find()->alias('g')->where(['g.is_delete' => 0, 'g.store_id' => $this->store_id])
//            ->leftJoin(['od' => PtOrderDetail::tableName()], 'od.goods_id = g.id')
//            ->leftJoin(['o' => PtOrder::tableName()], 'o.id = od.order_id')
//            ->andWhere([
//                'or',
//                ['od.is_delete' => 0, 'o.is_delete' => 0, 'o.is_pay' => 1,'o.is_success'=>1],
//                'isnull(od.id)'
//            ])->groupBy('g.id');
//
//        echo '<pre>';
//        var_dump($query);
//        die;


        if ($this->keyword) {
            $query->andWhere(['like', 'g.name', $this->keyword]);
        }
        $count = $query->count();

        $p = new Pagination(['totalCount' => $count, 'pageSize' => $this->limit]);
        if ($this->status == 1) {
            $query->orderBy(['sales_volume' => SORT_DESC]);
        } else if ($this->status == 2) {
            $query->orderBy(['sales_price' => SORT_DESC]);
        }
        $list = $query->select([
            'g.*', 'sum(case when isnull(o.id) then 0 else od.num end) as sales_volume',
            'sum(case when isnull(o.id) then 0 else od.total_price end) as sales_price'
        ])->offset($p->offset)->limit($p->limit)->asArray()->all();

        return [
            'list' => $list,
            'row_count' => $count,
            'pagination' => $p
        ];

    }

    /**
     * $status //1--消费金额排序  2--订单数排序
     */
    public function user_search()
    {
        if (!$this->validate()) {
            return $this->getModelError();
        }
        $query = User::find()->alias('u')->where(['u.store_id' => $this->store_id, 'u.is_delete' => 0])
            ->leftJoin(['o' => Order::tableName()], 'o.user_id = u.id')
//            ->andWhere([
//                'or',
//                ['o.is_delete' => 0, 'o.is_pay' => 1],
//                'isnull(o.id)'
//            ])
            ->groupBy('u.id');
        if ($this->keyword) {
            $query->andWhere(['like', 'u.nickname', $this->keyword]);
        }
        $count = $query->count();

        if ($this->status == 1) {
            $query->orderBy(['sales_price' => SORT_DESC]);
        } else if ($this->status == 2) {
            $query->orderBy(['sales_count' => SORT_DESC]);
        }

        $list = $query->select([
            'u.*', 'sum(case when isnull(o.id) then 0 else o.pay_price end) as sales_price',
            'sum(case when isnull(o.id) then 0 else 1 end) as sales_count'
        ])
            ->asArray()->all();


        //总付费人数
        //总人数

        $list_user_haslevel = User::find()->select('id,parent_id')
            ->andWhere(['>', 'level', 0])
            ->asArray()->all();
        $list_user = User::find()->select('id,parent_id')
//            ->andWhere(['>','level',0])
            ->asArray()->all();

        foreach ($list as $key => $value) {
            $allson = $this->getSubs($list_user, $value['id']);
            $son = $this->getSons($list_user, $value['id']);
            $allson_num = count($allson);
            $son_num = count($son);
            $levelMax = $this->searchmax($allson, 'level');
            $list[$key]['allson_num'] = $allson_num;
            $list[$key]['son_num'] = $son_num;
            //获取层级和人数
            $list[$key]['levelMax'] = $levelMax;
            $list[$key]['level_s_children'] = array_count_values(array_column($allson, 'level'));

            $allson_haslevel = $this->getSubs($list_user_haslevel, $value['id']);
            $son_haslevel = $this->getSons($list_user_haslevel, $value['id']);
            $allson_num_haslevel = count($allson_haslevel);
            $son_num_haslevel = count($son_haslevel);
            $levelMax_haslevel = $this->searchmax($allson_haslevel, 'level');
            $list[$key]['allson_num_haslevel'] = $allson_num_haslevel;
            $list[$key]['son_num_haslevel'] = $son_num_haslevel;
            //获取层级和人数
            $list[$key]['levelMax_haslevel'] = $levelMax_haslevel;
            $list[$key]['level_s_children_haslevel'] = array_count_values(array_column($allson_haslevel, 'level'));

        }

        if ($this->status == 1) {
            array_multisort(array_column($list, 'sales_price'), SORT_DESC, $list);
        } else if ($this->status == 2) {
            array_multisort(array_column($list, 'sales_count'), SORT_DESC, $list);
        } else if ($this->status == 3) {
            array_multisort(array_column($list, 'allson_num'), SORT_DESC, $list);
        } else if ($this->status == 4) {
            array_multisort(array_column($list, 'allson_num_haslevel'), SORT_DESC, $list);
        } else {
            array_multisort(array_column($list, 'integral'), SORT_DESC, $list);
        }

        $p = new Pagination(['totalCount' => $count, 'pageSize' => $this->limit]);
        $list = array_slice($list, $p->offset, $p->limit);

        return [
            'list' => $list,
            'row_count' => $count,
            'pagination' => $p
        ];
    }


    public function actionTongji($list_user, $user_id)
    {
        //存放team
        //下级
        $allson = $this->getSubs($list_user, $user_id);
        $son = $this->getSons($list_user, $user_id);
        $allson_num = count($allson);
        $son_num = count($son);
        $levelMax = $this->searchmax($allson, 'level');
        $value['allson_num'] = $allson_num;
        $value['son_num'] = $son_num;
        //获取层级和人数
        $value['levelMax'] = $levelMax;
        $value['level_s_children'] = array_count_values(array_column($allson, 'level'));

        return $value;
    }


    //获取某分类的直接子分类
    public function getSons($categorys, $catId = 0)
    {
        $sons = array();
        foreach ($categorys as $item) {
            if ($item['parent_id'] == $catId)
                $sons[] = $item;
        }
        return $sons;
    }

    //获取某个分类的所有子分类
    public function getSubs($categorys, $catId = 0, $level = 1)
    {
        $subs = array();
        foreach ($categorys as $item) {
            if ($item['parent_id'] == $catId) {
                $item['level'] = $level;
                $subs[] = $item;
                $subs = array_merge($subs, $this->getSubs($categorys, $item['id'], $level + 1));
            }

        }
        return $subs;
    }

    //获取某个分类的所有父分类
    //方法一，递归
    public function getParents($categorys, $catId)
    {
        $tree = array();
        foreach ($categorys as $item) {
            if ($item['id'] == $catId) {
                if ($item['parent_id'] > 0)
                    $tree = array_merge($tree, $this->getParents($categorys, $item['parentId']));
                $tree[] = $item;
                break;
            }
        }
        return $tree;
    }


    public function searchmax($arr, $field) // 最小值 只需要最后一个max函数  替换为 min函数即可
    {
        if (!is_array($arr) || !$field) { //判断是否是数组以及传过来的字段是否是空
            return false;
        }

        $temp = array();
        foreach ($arr as $key => $val) {
            $temp[] = $val[$field]; // 用一个空数组来承接字段
        }
        return max($temp);  // 用php自带函数 max 来返回该数组的最大值，一维数组可直接用max函数
    }


}