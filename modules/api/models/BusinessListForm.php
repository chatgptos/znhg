<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/7/1
 * Time: 23:33
 */

namespace app\modules\api\models;


use app\models\Business;
use app\models\Cat;
use app\models\Goods;
use app\models\GoodsPic;
use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use yii\data\Pagination;

class BusinessListForm extends Model
{
    public $store_id;
    public $keyword;
    public $cat_id;
    public $page;
    public $limit;

    public $sort;
    public $sort_type;


    public function rules()
    {
        return [
            [['keyword'], 'trim'],
            [['store_id', 'cat_id', 'page', 'limit',], 'integer'],
            [['limit',], 'integer', 'max' => 100],
            [['limit',], 'default', 'value' => 12],
            [['sort', 'sort_type',], 'integer',],
            [['sort',], 'default', 'value' => 0],
        ];
    }

    public function search()
    {
        if (!$this->validate())
            return $this->getModelError();
        $query = Business::find()->alias('g')->where([
            'g.status' => 1,
            'g.is_delete' => 0,
        ]);
        if ($this->store_id)
            $query->andWhere(['g.store_id' => $this->store_id]);
        if ($this->keyword)
            $query->andWhere(['LIKE', 'g.name', $this->keyword]);
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => $this->limit, 'page' => $this->page - 1]);

        $list = $query
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->asArray()->all();

        foreach ($list as $i => $item) {
            if (!$item['pic_url']) {
                $list[$i]['pic_url'] =  User::findOne(['id' => $item['user_id'], 'store_id' => $this->store_id])->avatar_url;
                $list[$i]['name'] =  User::findOne(['id' => $item['user_id'], 'store_id' => $this->store_id])->nickname;

            }
        }
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'row_count' => $count,
                'page_count' => 1,
                'list' => $list,
            ],
        ];
    }

    private function numToW($sales)
    {
        if($sales < 10000){
            return $sales;
        }else{
            return round($sales/10000,2).'W';
        }
    }
}