<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/8
 * Time: 16:13
 */

namespace app\modules\mch\models;


use app\models\Share;
use app\models\User;
use yii\data\Pagination;
use yii\helpers\VarDumper;

class ShareListForm extends Model
{
    public $store_id;

    public $page;
    public $limit;
    public $status;
    public $keyword;

    public function rules()
    {
        return [
            [['keyword',], 'trim'],
            [['page','limit','status'],'integer'],
            [['status',], 'default', 'value' => -1],
            [['page'],'default','value'=>1]
        ];
    }



    public function getList()
    {
        if($this->validate()){
            //清楚错误数据
            $error_user = User::find()->alias('u')->where(['u.store_id'=>$this->store_id,'u.is_delete'=>0,'u.is_distributor'=>2])
                ->leftJoin(Share::tableName().' s','s.user_id=u.id and s.is_delete=0')->andWhere('s.id is null')->select('u.id')->asArray()->column();
            User::updateAll(['is_distributor'=>0],['in','id',$error_user]);

            $query = Share::find()->alias('s')
                ->where(['s.is_delete'=>0,'s.store_id'=>$this->store_id])
                ->leftJoin('{{%user}} u','u.id=s.user_id')
                ->andWhere(['u.is_delete'=>0])
                ->andWhere(['in','s.status',[0,1]]);
            if($this->keyword){
                $query->andWhere([
                    'or',
                    ['like','s.name',$this->keyword],
                    ['like','u.nickname',$this->keyword],
                ]);
            }
            if($this->status == 0 && $this->status != ''){
                $query->andWhere(['s.status'=>0]);
            }
            if($this->status == 1){
                $query->andWhere(['s.status'=>1]);
            }
            $count = $query->count();
            $pagination = new Pagination(['totalCount' => $count, 'pageSize' => $this->limit, 'page' => $this->page - 1]);
            $list = $query->limit($pagination->limit)->offset($pagination->offset)->orderBy('s.status ASC,s.addtime DESC')
                ->select([
                    's.*','u.nickname','u.avatar_url','u.time','u.price','u.total_price','u.id user_id'
                ])->asArray()->all();
            foreach($list as $index=>$value){
                $list[$index]['first'] = 0;
                $list[$index]['second'] = 0;
                $list[$index]['third'] = 0;
                $f_children = Share::getChildren($value['user_id']);
                if(!$f_children)
                    continue;
                $list[$index]['first'] += count($f_children);
                foreach($f_children as $A=>$a){
                    $s_children = Share::getChildren($a['id']);
                    if(!$s_children)
                        continue;
                    $list[$index]['second'] += count($s_children);
                    foreach($s_children as $b){
                        $t_children = Share::getChildren($b['id']);
                        if(!$t_children)
                            continue;
                        $list[$index]['third'] += count($t_children);
                    }
                }
            }
            return [$list,$pagination];

        }else{
            return $this->getModelError();
        }
    }
    //无效
    public function getList1()
    {
        $query = Share::find()->alias('s')
            ->where(['s.is_delete'=>0,'s.store_id'=>$this->store_id])
            ->leftJoin(User::tableName().' u','u.id=s.user_id')
            ->joinWith('firstChildren')->groupBy('s.id');
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => $this->limit, 'page' => $this->page - 1]);
        $list = $query->limit($pagination->limit)->offset($pagination->offset)->orderBy('s.addtime DESC')
            ->select(['s.*','u.nickname','u.avatar_url','u.time','u.price','u.total_price',])->asArray()->all();
        $new_list = $list;
        foreach($list as $index=>$value){
            $list[$index]['first'] = count($value['firstChildren']);
            foreach($value['firstChildren'] as $i=>$v){
                $list[$index]['firstChildren'][$i]['time'] = date('Y-m-d',$v['addtime']);
                foreach($new_list as $j=>$item){
                    if($v['id'] == $item['user_id']){
                        $list[$index]['second'] = $new_list[$j]['firstChildren'];
                    }
                }
            }
        }
        $new_list = $list;
        foreach($list as $index=>$value){
            if(isset($value['secondChildren']) && is_array($value['secondChildren'])){
                foreach($value['secondChildren'] as $i=>$v){
                    $list[$index]['secondChildren'][$i]['time'] = date('Y-m-d',$v['addtime']);
                    foreach($new_list as $j=>$item){
                        if($v['id'] == $item['user_id']){
                            $list[$index]['thirdChildren'] = $new_list[$j]['firstChildren'];
                        }
                    }
                }
            }
        }
        return $list;
    }

    public function getTeam()
    {
        //获取有一级下线的分销商
        $query = Share::find()->alias('s')
            ->where(['s.is_delete'=>0,'s.store_id'=>$this->store_id])
            ->leftJoin(User::tableName().' u','u.id=s.user_id')
            ->joinWith('firstChildren')->groupBy('s.id');
//        $count = $query->count();
//        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => $this->limit, 'page' => $this->page - 1]);
        $list = $query->select(['s.*','u.nickname'])->asArray()->all();
        $new_list = $list;
        //获取二级下线
        foreach($list as $index=>$value){
            $res = [];
            foreach($value['firstChildren'] as $i=>$v){
                $list[$index]['firstChildren'][$i]['time'] = date('Y-m-d',$v['addtime']);
                foreach($new_list as $j=>$item){
                    if($v['id'] == $item['user_id']){
//                            $list[$index]['secondChildren'] = $new_list[$j]['firstChildren'];
                        $res = array_merge($res,$new_list[$j]['firstChildren']);
                    }
                }
            }
            $list[$index]['secondChildren'] = $res;
        }
        $new_list = $list;
        foreach($list as $index=>$value){
            $res = [];
            if(isset($value['secondChildren']) && is_array($value['secondChildren'])){
                foreach($value['secondChildren'] as $i=>$v){
                    $list[$index]['secondChildren'][$i]['time'] = date('Y-m-d',$v['addtime']);
                    foreach($new_list as $j=>$item){
                        if($v['id'] == $item['user_id']){
//                            $list[$index]['thirdChildren'] = $new_list[$j]['firstChildren'];
                            $res = array_merge($res,$new_list[$j]['firstChildren']);
                        }
                    }
                }
            }
            $list[$index]['thirdChildren'] = $res;
        }
        return $list;
    }


    public function getCount()
    {
        $list = Share::find()
            ->select([
                'sum(case when status = 0 then 1 else 0 end) count_1',
                'sum(case when status = 1 then 1 else 0 end) count_2',
                'sum(case when status != 2 then 1 else 0 end) total'
            ])
            ->where(['is_delete'=>0,'store_id'=>$this->store_id])->asArray()->one();
        return $list;
    }
}