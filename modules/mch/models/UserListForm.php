<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/3
 * Time: 13:52
 */

namespace app\modules\mch\models;


use app\models\Level;
use app\models\Order;
use app\models\Shop;
use app\models\Store;
use app\models\User;
use app\models\UserCard;
use yii\data\Pagination;

class UserListForm extends Model
{
    public $store_id;
    public $page;
    public $keyword;
    public $is_clerk;
    public $level;

    public function rules()
    {
        return [
            [['keyword','level'], 'trim'],
            [['page', 'is_clerk'], 'integer'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function search()
    {
        $query = User::find()->alias('u')->where([
            'u.type' => 1,
            'u.store_id' => $this->store_id,
            'u.is_delete' => 0
        ])->leftJoin(Shop::tableName() . ' s', 's.id=u.shop_id')
        ->leftJoin(Level::tableName(). ' l','l.level=u.level and l.is_delete = 0 and l.store_id = '.$this->store_id);
        if ($this->keyword)
            $query->andWhere(['LIKE', 'u.nickname', $this->keyword]);
        if ($this->is_clerk == 1) {
            $query->andWhere(['u.is_clerk' => 1]);
        }
        if($this->level){
            $query->andWhere(['l.level'=>$this->level]);
        }
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'page' => $this->page - 1]);
        $list = $query->select([
            'u.*', 's.name shop_name','l.name l_name'
        ])->limit($pagination->limit)->offset($pagination->offset)->orderBy('u.addtime DESC')->asArray()->all();
        $store = Store::findOne(['id' => $this->store_id]);
        foreach ($list as $index => $value) {
//            $time = time() - $store->after_sale_time * 86400;
            $order_count = Order::find()->where(['store_id' => $this->store_id, 'is_delete' => 0, 'clerk_id' => $value['id']])
                ->count();
            $list[$index]['order_count'] = $order_count;
            $list[$index]['card_count'] = UserCard::find()->where([
                'store_id'=>$this->store_id,'is_delete'=>0,'clerk_id'=>$value['id']
            ])->count();
        }
        return [
            'row_count' => $count,
            'page_count' => $pagination->pageCount,
            'pagination' => $pagination,
            'list' => $list,
        ];
    }

    public function getUser()
    {
        $query = User::find()->where([
            'type' => 1,
            'store_id' => $this->store_id,
            'is_clerk' => 0,
            'is_delete' => 0
        ]);
        if ($this->keyword)
            $query->andWhere(['LIKE', 'nickname', $this->keyword]);
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'page' => $this->page - 1]);
        $list = $query->limit($pagination->limit)->offset($pagination->offset)->orderBy('addtime DESC')->asArray()->all();
//        $list = $query->orderBy('addtime DESC')->asArray()->all();

        return $list;
    }
}