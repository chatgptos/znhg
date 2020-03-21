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
use app\modules\mch\extensions\Export;
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
    public $flag;
    public $date_begin;
    public $date_end;



    public function rules()
    {
        return [
            [['page'],'default','value'=>1],
            [['flag','date_begin','date_end','keyword'], 'string'],
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
        if ($this->keyword ==1){
            $query->andWhere(['g.is_exchange'=>0]);
        }elseif($this->keyword == 2) {
            $query->andWhere(['g.is_exchange'=>1]);
        }elseif($this->keyword == 3) {
            $query->andWhere(['g.is_hongbao'=>1]);
        }elseif($this->keyword == 4) {
            $query->andWhere(['g.is_hongbao'=>2]);
        }elseif($this->keyword == 5) {
            $query->andWhere(['g.is_parent'=>1]);
        }elseif($this->keyword == 6) {
            $query->andWhere(['g.is_aim'=>1]);
        }elseif($this->keyword == 13) {
            $query ->andWhere(['>', 'is_hongbao', 0])
                ->andWhere(['g.is_exchange'=>0]);
        }elseif($this->keyword == 14) {
            $query->andWhere(['>', 'is_hongbao', 0])
                ->andWhere(['g.is_aim'=>1])
                ->andWhere(['g.is_exchange'=>0]);
        }elseif($this->keyword == 15) {
            $query->andWhere(['>', 'is_hongbao', 0])
                ->andWhere(['g.is_parent'=>1])
                ->andWhere(['g.is_exchange'=>0]);
        }elseif($this->keyword == 16) {
            $query->andWhere(['>', 'is_hongbao', 0])
                ->andWhere(['g.is_aim'=>1])
                ->andWhere(['g.is_exchange'=>0]);
        }
        if($this->date_begin){
            $query->andWhere(['>', 'g.addtime', strtotime($this->date_begin)]);
        }
        if($this->date_end){
            $query->andWhere(['<', 'g.addtime', strtotime($this->date_end)]);
        }

//            $query->andWhere(['LIKE', 'g.name', $this->keyword]);
        $newquery=$query;

        if ($this->flag == "EXPORT") {
            $identity = \Yii::$app->store->identity;
            if($identity->user_id!=10){
                echo '<h1/>没有权限拉取</h1>';die;
            }

            $list_ex = $query->select('*')
//            ->orderBy('g.addtime DESC')
                ->asArray()->all();
            Export::business($list_ex,$this->date_begin,$this->date_end);
        }

        if ($this->flag == "EXPORTall") {
            $identity = \Yii::$app->store->identity;
            if($identity->user_id!=10){
                echo '<h1/>没有权限拉取</h1>';die;
            }

            $people = $this->getdatatjBydate($newquery);
            Export::businessall($people,$this->date_begin,$this->date_end,$people);
        }




        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => $this->limit, 'page' => $this->page - 1]);
        $list = $query
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->asArray()->all();

        foreach ($list as $i => $item) {
            if (!$item['pic_url']) {
                $user= User::findOne(['id' => $item['user_id'], 'store_id' => $this->store_id]);
                $user_buyer= User::findOne(['id' => $item['user_id_buyer'], 'store_id' => $this->store_id]);
                $list[$i]['avatar_url'] =  $user->avatar_url;
                $list[$i]['nickname'] =  $user->nickname;
                $list[$i]['wechat_open_id'] =  $user->wechat_open_id;
                $list[$i]['hld'] =  $user->hld;
                $list[$i]['coupon'] =  $user->coupon;
                $list[$i]['integral'] =  $user->integral;
                $list[$i]['user_id'] =  $user->id;

                if($item['user_id_buyer']){
                    $list[$i]['avatar_url_buyer'] =  $user_buyer->avatar_url;
                    $list[$i]['nickname_buyer'] =  $user_buyer->nickname;
                    $list[$i]['wechat_open_id_buyer'] =  $user_buyer->wechat_open_id;
                    $list[$i]['hld_buyer'] =  $user_buyer->hld;
                    $list[$i]['coupon_buyer'] =  $user_buyer->coupon;
                    $list[$i]['integral_buyer'] =  $user_buyer->integral;
                    $list[$i]['user_id_buyer'] =  $user_buyer->id;
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


    private function getdatatjBydate($newquery)
    {
        $peoplesellcount_huanledou1= $newquery->sum('huanledou');
        $peoplesellcount_huanledou_charge1= $newquery->sum('huanledou_charge');
        $peoplesellcount_xtjl1= $newquery->sum('xtjl');
        $peoplesellcount_num1= $newquery->sum('num');
        $peoplesellcount1= $newquery->groupBy('user_id_buyer')->count();
        $peoplebuyercount1= $newquery->groupBy('user_id')->count();

        $peoplesellcount_huanledou2= $newquery->where([
            'g.is_exchange' => 0,
        ])->sum('huanledou');
        $peoplesellcount_huanledou_charge2= $newquery->where([
            'g.is_exchange' => 0,
        ])->sum('huanledou_charge');
        $peoplesellcount_xtjl2= $newquery->where([
            'g.is_exchange' => 0,
        ])->sum('xtjl');
        $peoplesellcount_num2= $newquery->where([
            'g.is_exchange' => 0,
        ])->sum('num');
        $peoplesellcount2= $newquery->where([
            'g.is_exchange' => 0,
        ])->groupBy('user_id_buyer')->count();
        $peoplebuyercount2= $newquery->where([
            'g.is_exchange' => 0,
        ])->groupBy('user_id')->count();


        $peoplesellcount_huanledou3= $newquery->where([
            'g.is_exchange' => 1,
        ])->sum('huanledou');
        $peoplesellcount_huanledou_charge3= $newquery->where([
            'g.is_exchange' => 1,
        ])->sum('huanledou_charge');
        $peoplesellcount_xtjl3= $newquery->where([
            'g.is_exchange' => 1,
        ])->sum('xtjl');
        $peoplesellcount_num3= $newquery->where([
            'g.is_exchange' => 1,
        ])->sum('num');
        $peoplesellcount3= $newquery->where([
            'g.is_exchange' => 1,
        ])->groupBy('user_id_buyer')
            ->count();
        $peoplebuyercount3= $newquery->where([
            'g.is_exchange' => 1,
        ])->groupBy('user_id')->count();

        $people=array(
            'peoplesellcount_huanledou1'=>$peoplesellcount_huanledou1,
            'peoplesellcount_huanledou_charge1'=>$peoplesellcount_huanledou_charge1,
            'peoplesellcount_xtjl1'=>$peoplesellcount_xtjl1,
            'peoplesellcount_num1'=>$peoplesellcount_num1,
            'peoplesellcount1'=>$peoplesellcount1,
            'peoplebuyercount1'=>$peoplebuyercount1,
            'peoplesellcount_huanledou2'=>$peoplesellcount_huanledou2,
            'peoplesellcount_huanledou_charge2'=>$peoplesellcount_huanledou_charge2,
            'peoplesellcount_xtjl2'=>$peoplesellcount_xtjl2,
            'peoplesellcount_num2'=>$peoplesellcount_num2,
            'peoplesellcount2'=>$peoplesellcount2,
            'peoplebuyercount2'=>$peoplebuyercount2,
            'peoplesellcount_huanledou3'=>$peoplesellcount_huanledou3,
            'peoplesellcount_huanledou_charge3'=>$peoplesellcount_huanledou_charge3,
            'peoplesellcount_xtjl3'=>$peoplesellcount_xtjl3,
            'peoplesellcount_num3'=>$peoplesellcount_num3,
            'peoplesellcount3'=>$peoplesellcount3,
            'peoplebuyercount3'=>$peoplebuyercount3,
        );
        return $people;
    }

    public function searchforcron()
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
        if ($this->keyword ==1){
            $query->andWhere(['g.is_exchange'=>0]);
        }elseif($this->keyword == 2) {
            $query->andWhere(['g.is_exchange'=>1]);
        }elseif($this->keyword == 3) {

        }
        if($this->date_begin){
            $query->andWhere(['>', 'g.addtime', strtotime($this->date_begin)]);
        }
        if($this->date_end){
            $query->andWhere(['<', 'g.addtime', strtotime($this->date_end)]);
        }

//            $query->andWhere(['LIKE', 'g.name', $this->keyword]);
        $newquery=$query;

        $people = $this->getdatatjBydate($newquery);
        return $people;
    }
}