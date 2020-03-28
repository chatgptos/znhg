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
    public $user_id;



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
        $query = Business::find()->select('u.avatar_url pic_url,u.nickname name,g.id,title,status,g.addtime,num,user_id,huanledou,huanledou_charge,xtjl,user_id_buyer,is_exchange,is_hongbao,is_parent,is_aim,is_hg')->alias('g')->where([
            'g.status' => 1,
            'g.is_exchange' => 0,
            'g.is_delete' => 0,
        ])->leftJoin(['u' => User::tableName()], 'u.id=g.user_id')
            ->orderBy('g.addtime DESC');
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


        //过滤掉不能看到红包的
        $getHongbao=$this->getUserHuobao();

        foreach ($list as $i => $item) {
            if ($item['pic_url']) {
//                $list[$i]['pic_url'] =  User::findOne(['id' => $item['user_id'], 'store_id' => $this->store_id])->avatar_url;
//                $list[$i]['name'] =  User::findOne(['id' => $item['user_id'], 'store_id' => $this->store_id])->nickname;

                if($list[$i]['is_hongbao']==1){
                    $list[$i]['avatar_url_hongbao'] = '/images/red_envelope.png';
                    if($list[$i]['is_parent']==1){
                        $list[$i]['avatar_url_hongbao'] = '/images/red_envelope1.png';
                        if($list[$i]['is_aim']==1){
                            $list[$i]['avatar_url_hongbao'] = '/images/red_envelope2.png';
                        }
                    }
                }


                //开关打开 投放红包  判断当前用户属性 如果 没达标隐藏
                if(!$getHongbao){
                    $list[$i]['is_hongbao'] =  0;
                }


                //如果是货柜来的券必须显示出来
                if($list[$i]['is_hg']){
                    $list[$i]['avatar_url_hongbao'] = '/images/red_envelope3.png';
                    $list[$i]['is_hongbao']=1;
                }
                $list[$i]['is_ad'] =  0;
                if($i%9==5){
                    $list[$i]['is_ad'] =  rand(0,1);
                }

            }
        }

        return [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'row_count' => $count,
                'page_count' => $pagination->pageCount,
                'pagination' => $pagination,
                'list' => $list,
                'is_hongbao' => $getHongbao,
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


    public  function getUserHuobao()
    {

        //判断逻辑 基本分数
        $score =0;
        $gailvarray=[75,95,99];//控制抽到奖品人抽奖
        $user = User::findOne(['id' => $this->user_id]);

        //如果是新用户 10分
        //1.是否是萌新
        if($user->level<2){
            $score +=10;
        }




        //如果当天发布优惠券超过10张
        $user_id_hongbao_num_now = Business::find()->alias('g')
            ->where([
                'g.status' => 1,
                'g.is_delete' => 0,
                'g.store_id' => $this->store_id,
                'g.user_id_hongbao' => $this->user_id,
            ])
            ->andWhere(['>', 'addtime', strtotime(date('Y-m-d'))])
            ->count();

        //没有抽到过今天  优化 数据  刷 保障最多刷3次   刷到一次基本上是 贡献流量100次
        $gailv=rand(1,100);

        if($user_id_hongbao_num_now == 0){
            $score +=10; //继续计算
        }elseif($user_id_hongbao_num_now == 1){
            if($gailv<$gailvarray[0]){//95%概率 直接没有
                return 0;
            }
        }elseif($user_id_hongbao_num_now == 2){
            if($gailv<$gailvarray[1]){//95%概率 直接没有
                return 0;
            }
        }elseif($user_id_hongbao_num_now == 3){
            if($gailv<$gailvarray[2]){//98%概率 直接没有
                return 0;
            }
        }else{
            //其他的凑中次数太多的 肯定刷了很多次
        }

        //如果当天发布优惠券超过10张
        $query = Business::find()->alias('g')->where([
            'g.status' => 1,
            'g.is_delete' => 0,
            'g.is_exchange' => 1,
            'g.store_id' => $this->store_id
        ])->where(['>', 'addtime', strtotime(date('Y-m-d'))]);

         $query1=$query->where(['g.user_id' => $this->user_id]);
         $count1 = $query1->count();
         $query2=$query->andWhere(['g.user_id_buyer' => $this->user_id]);
         $count2 = $query2->count();
         $count=$count1+$count2;

         if($count>10){
             $score +=10;
         }
         //如果今天有推荐新用户
        $userquery = User::find(['parent_id' => $this->user_id])
            ->where(['>', 'addtime', strtotime(date('Y-m-d'))]);

        $userNum = $userquery->count();
         if($userNum>0){
             $score +=10;
             $userNum1 = $userquery  ->where([ 'level' => 1]) ->count();
             if($userNum1){
                 $score +=10;
             }
         }
        //如果今天有下单
        $orderquery = Order::find(['user_id' => $this->user_id])
            ->where(['>', 'addtime', strtotime(date('Y-m-d'))])->count();
        if($orderquery){
            $score +=10;
        }


        //如果今天推荐人有下单
        $orderquerytj = Order::find(['parent_id' => $this->user_id])
            ->where(['>', 'addtime', strtotime(date('Y-m-d'))])->count();
        if($orderquerytj){
            $score +=10;
        }


        $gailv=rand($score,60);
        if($gailv>50){//概率大 抽奖看到
            return 1;
        }else{
            return 0;
        }
    }
}