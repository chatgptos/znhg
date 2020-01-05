<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/2
 * Time: 14:01
 */

namespace app\modules\mch\models\settlementstatistics;


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

        $query = UserShareMoney::find()->where(['store_id'=>$this->store_id,'is_delete'=>0]);

        if($this->keyword){
            $query->andWhere(['like','user_id',$this->keyword]);
        }

        $count = $query->count();
        $p = new Pagination(['totalCount'=>$count,'pageSize'=>$this->limit]);
        $list = $query->offset($p->offset)->limit($p->limit)->orderBy(['addtime'=>SORT_ASC])->asArray()->all();
        return [
            'list'=>$list,
            'p'=>$p,
            'row_count'=>$count
        ];
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