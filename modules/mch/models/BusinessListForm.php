<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/7/1
 * Time: 23:33
 */

namespace app\modules\mch\models;


use app\models\Business;
use app\models\Card;
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
            [['page'],'default','value'=>1],
            [['limit'],'default','value'=>20]
        ];
    }


    public function search()
    {
        if (!$this->validate())
            return $this->getModelError();
        $query = Business::find()->alias('g')->where([
            'g.status' => 1,
//            'g.is_exchange' => 0,
            'g.is_delete' => 0,
        ])->orderBy('g.addtime DESC');
        if ($this->store_id)
            $query->andWhere(['g.store_id' => $this->store_id]);
        $this->keyword =\Yii::$app->request->get()['keyword'];
        if ($this->keyword ==0){
            $query->andWhere(['g.is_exchange'=>0]);
        }elseif($this->keyword == 1) {
            $query->andWhere(['g.is_exchange'=>1]);
        }elseif($this->keyword == 2) {

        }
//            $query->andWhere(['LIKE', 'g.name', $this->keyword]);
        $newquery=$query;
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => $this->limit, 'page' => $this->page - 1]);
        $list = $query
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->asArray()->all();

        foreach ($list as $i => $item) {
            if (!$item['pic_url']) {
                $list[$i]['avatar_url'] =  User::findOne(['id' => $item['user_id'], 'store_id' => $this->store_id])->avatar_url;
                $list[$i]['nickname'] =  User::findOne(['id' => $item['user_id'], 'store_id' => $this->store_id])->nickname;
                $list[$i]['wechat_open_id'] =  User::findOne(['id' => $item['user_id'], 'store_id' => $this->store_id])->wechat_open_id;
                $list[$i]['hld'] =  User::findOne(['id' => $item['user_id'], 'store_id' => $this->store_id])->hld;
                $list[$i]['coupon'] =  User::findOne(['id' => $item['user_id'], 'store_id' => $this->store_id])->coupon;
                $list[$i]['integral'] =  User::findOne(['id' => $item['user_id'], 'store_id' => $this->store_id])->integral;
                $list[$i]['user_id'] =  User::findOne(['id' => $item['user_id'], 'store_id' => $this->store_id])->id;

                if($item['user_id_buyer']){
                    $list[$i]['avatar_url_buyer'] =  User::findOne(['id' => $item['user_id_buyer'], 'store_id' => $this->store_id])->avatar_url;
                    $list[$i]['nickname_buyer'] =  User::findOne(['id' => $item['user_id_buyer'], 'store_id' => $this->store_id])->nickname;
                    $list[$i]['wechat_open_id_buyer'] =  User::findOne(['id' => $item['user_id_buyer'], 'store_id' => $this->store_id])->wechat_open_id;
                    $list[$i]['hld_buyer'] =  User::findOne(['id' => $item['user_id_buyer'], 'store_id' => $this->store_id])->hld;
                    $list[$i]['coupon_buyer'] =  User::findOne(['id' => $item['user_id_buyer'], 'store_id' => $this->store_id])->coupon;
                    $list[$i]['integral_buyer'] =  User::findOne(['id' => $item['user_id_buyer'], 'store_id' => $this->store_id])->integral;
                    $list[$i]['user_id_buyer'] =  User::findOne(['id' => $item['user_id_buyer'], 'store_id' => $this->store_id])->id;
                }
            }
        }

        $peoplesellcount_huanledou= $newquery->sum('huanledou');
        $peoplesellcount_huanledou_charge= $newquery->sum('huanledou_charge');
        $peoplesellcount_xtjl= $newquery->sum('xtjl');
        $peoplesellcount_num= $newquery->sum('num');
        $peoplesellcount= $newquery->groupBy('user_id_buyer')->count();
        $peoplebuyercount= $newquery->groupBy('user_id')->count();


        $people=array(
            'peoplesellcount_huanledou'=>$peoplesellcount_huanledou,
            'peoplesellcount_huanledou_charge'=>$peoplesellcount_huanledou_charge,
            'peoplesellcount_xtjl'=>$peoplesellcount_xtjl,
            'peoplesellcount_num'=>$peoplesellcount_num,
            'peoplesellcount'=>$peoplesellcount,
            'peoplebuyercount'=>$peoplebuyercount,
        );

        return [
            'people'=>$people,
            'list'=>$list,
            'row_count'=>$count,
            'pagination'=>$pagination
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