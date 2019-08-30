<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/11
 * Time: 10:45
 */

namespace app\modules\mch\models;


use app\models\Cash;
use yii\data\Pagination;

class CashForm extends Model
{
    public $store_id;
    public $user_id;


    public $page;
    public $limit;
    public $status;
    public $keyword;
    public $id;

    public function rules()
    {
        return [
            [['keyword',], 'trim'],
            [['page','limit','status','id'],'integer'],
            [['status',], 'default', 'value' => -1],
            [['page'],'default','value'=>1]
        ];
    }



    public function getList()
    {
        $query = Cash::find()->alias('c')
            ->where(['c.is_delete'=>0,'c.store_id'=>$this->store_id])
            ->leftJoin('{{%user}} u','u.id=c.user_id')
            ->leftJoin('{{%share}} s','s.user_id=c.user_id')
            ->andWhere(['s.is_delete'=>0]);
        if($this->keyword){
            $query->andWhere([
                'or',
                ['like','u.nickname',$this->keyword],
                ['like','s.name',$this->keyword]
            ]);
        }
        if($this->status == 0 and $this->status != ''){//待审核
            $query->andWhere(['c.status'=>0]);
        }
        if($this->status == 1){//待打款
            $query->andWhere(['c.status'=>1]);
        }
        if($this->status == 2){//已打款
            $query->andWhere(['c.status'=>2]);
        }
        if($this->status == 3){//无效
            $query->andWhere(['c.status'=>3]);
        }
        if($this->id){
            $query->andWhere(['s.id'=>$this->id]);
        }
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => $this->limit, 'page' => $this->page - 1]);
        $list = $query->limit($pagination->limit)->offset($pagination->offset)->orderBy('c.status ASC,c.addtime DESC')
            ->select([
                'c.*','u.nickname','u.avatar_url','u.id user_id'
            ])->asArray()->all();

        return [$list,$pagination];
    }

    public function getCount()
    {
        $list = Cash::find()->select([
            'sum(case when status = 0 then 1 else 0 end) count_1',
            'sum(case when status = 1 then 1 else 0 end) count_2',
            'sum(case when status = 2 then 1 else 0 end) count_3',
            'sum(case when status = 3 then 1 else 0 end) count_4',
            'count(1) total'
        ])->where(['is_delete'=>0,'store_id'=>$this->store_id])->asArray()->one();
        return $list;
    }
}