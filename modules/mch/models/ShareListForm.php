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
                    's.*','u.nickname','u.avatar_url','u.time','u.price','u.total_price','u.id user_id','u.parent_id'
                ])->asArray()->all();

            $result = User::find()->alias('u')->select('*')->asArray()->all();



            //存放team
            $list_son_team = [];

            foreach($list as $index=>$value){
                $user = User::findOne(['id' =>  $value['parent_id']]);
                if($user && isset($user->nickname)){
                    $list[$index]['parent_id_nickname'] = $user->nickname;
                }

                //下级
                $allson = $this->getSubs($result,$value['user_id']);
                $son = $this->getSons($result,$value['user_id']);
                $allson_num=count($allson);
                $son_num=count($son);
                $list[$index]['allson_num'] = $allson_num;
                $list[$index]['allson'] = $allson;
                $list[$index]['son_num'] = $son_num;
                $list[$index]['son'] = $son;

                //获取层级和人数
                $levelMax=$this->searchmax($allson,'level');
                $list[$index]['levelMax']=$levelMax;
                $list[$index]['level_s_children']=array_count_values(array_column($allson,'level'));


                $list_son_team[$index]=$list[$index];

                $list_son= array();
                foreach ($allson as $key => $info) {
                    $list_son[$info['level']][] = $info;
                }
                $list_son_team[$index]['list_son']=$list_son;

            }




            return [$list,$pagination,$list_son_team];

        }else{
            return $this->getModelError();
        }
    }


    public function getTeam()
    {
        //获取有一级下线的分销商
        $query = Share::find()->alias('s')
            ->where(['s.is_delete'=>0,'s.store_id'=>$this->store_id])
            ->leftJoin(User::tableName().' u','u.id=s.user_id')
            ->joinWith('firstChildren')
            ->groupBy('s.id');
        $count = $query->count();
//        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => $this->limit, 'page' => $this->page - 1]);
        $list = $query->select(['s.*','u.nickname','u.parent_id'])->asArray()->all();


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





//        echo '<pre>';
//        echo $query->createCommand()->getRawSql();
//        var_dump($list);
//
//        die;
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
                $subs = array_merge($subs,$this->getSubs($categorys, $item['id'], $level + 1));
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



    public function searchmax($arr,$field) // 最小值 只需要最后一个max函数  替换为 min函数即可
    {
        if(!is_array($arr) || !$field){ //判断是否是数组以及传过来的字段是否是空
            return false;
        }

        $temp = array();
        foreach ($arr as $key=>$val) {
            $temp[] = $val[$field]; // 用一个空数组来承接字段
        }
        return max($temp);  // 用php自带函数 max 来返回该数组的最大值，一维数组可直接用max函数
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

    public function getTeamNew()
    {
        //获取有一级下线的分销商
        $query = Share::find()->alias('s')
            ->where(['s.is_delete'=>0,'s.store_id'=>$this->store_id])
            ->leftJoin(User::tableName().' u','u.id=s.user_id')
            ->joinWith('firstChildren')
            ->groupBy('s.id');
//        $count = $query->count();
//        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => $this->limit, 'page' => $this->page - 1]);
        $list = $query->select(['s.*','u.nickname','u.parent_id'])->asArray()->all();
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