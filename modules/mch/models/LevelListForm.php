<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/2
 * Time: 14:01
 */

namespace app\modules\mch\models;


use app\models\Level;
use yii\data\Pagination;

class LevelListForm extends Model
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

        $query = Level::find()->where(['store_id'=>$this->store_id,'is_delete'=>0]);

        if($this->keyword){
            $query->andWhere(['like','name',$this->keyword]);
        }

        $count = $query->count();
        $p = new Pagination(['totalCount'=>$count,'pageSize'=>$this->limit]);

        $list = $query->offset($p->offset)->limit($p->limit)->orderBy(['level'=>SORT_ASC])->asArray()->all();
        return [
            'list'=>$list,
            'p'=>$p,
            'row_count'=>$count
        ];
    }
}