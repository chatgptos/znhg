<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/29
 * Time: 10:57
 */

namespace app\modules\api\models;


use app\models\UserCard;
use yii\data\Pagination;

class CardListForm extends Model
{
    public $store_id;
    public $user_id;

    public $page;
    public $limit;
    public $status;

    public function rules()
    {
        return [
            [['page'],'default','value'=>1],
            [['limit'],'default','value'=>10],
            [['status'],'default','value'=>1]
        ];
    }

    public function search()
    {
        if(!$this->validate()){
            return $this->getModelError();
        }

        $query = UserCard::find()->where([
            'store_id'=>$this->store_id,'user_id'=>$this->user_id,'is_delete'=>0]);

        if($this->status == 1){
            $query->andWhere(['is_use'=>0]);
        }
        if($this->status == 2){
            $query->andWhere(['is_use'=>1]);
        }
        $count = $query->count();

        $p = new Pagination(['totalCount'=>$count,'pageSize'=>$this->limit]);

        $list = $query->limit($p->limit)->offset($p->offset)->orderBy(['addtime'=>SORT_DESC])->asArray()->all();
        foreach($list as $k=>$v){
            $list[$k]['clerk_time'] = date('Y-m-d H:i:s',$v['clerk_time']);
        }
        return [
            'code'=>0,
            'msg'=>'',
            'data'=>[
                'list'=>$list,
                'page_count'=>$p->pageCount,
                'row_count'=>$count
            ]
        ];

    }
}