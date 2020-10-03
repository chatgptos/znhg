<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/27
 * Time: 9:32
 */

namespace app\modules\api\models\couponmall;


use app\models\PtOrderDetail;
use app\models\Room;
use app\models\Shop;
use app\models\Topic;
use app\models\User;
use app\models\Video;
use app\modules\api\models\Model;
use yii\data\Pagination;
use yii\db\Query;

class GoodsForm extends Model
{
    public $page = 0;
    public $store_id;

    public $user_id;

    public $gid;

    public $limit;


    /**
     * @return array
     * 拼团商品列表
     */
    public function getList()
    {
        $page = \Yii::$app->request->get('page')?:1;
        $limit = (int)\Yii::$app->request->get('limit')?:9;
        $cid = \Yii::$app->request->get('cid');
        $query = Goods::find()
            ->andWhere(['is_delete' => 0, 'store_id' => $this->store_id, 'status' => 1]);
        if ((int)$cid){
            // 分类
            $query->andWhere(['cat_id'=>$cid]);
        }
        $count = $query->count();
        $p = new Pagination(['totalCount' => $count, 'pageSize' => $limit, 'page' => $page - 1]);
        $list = $query
            ->offset($p->offset)
            ->limit($p->limit)
            ->orderBy('sort ASC')
            ->asArray()
            ->all();

        foreach ($list AS $k => $v){
            $room_info='';
            if($v['room_id']){
                $room_info =  Room::findOne(['room_id' => $v['room_id'], 'store_id' => $this->store_id ,'is_delete' =>0]);
                if($room_info){
                    $room_info= $room_info->toArray();
                }
            }
            $list[$k]['room_info']=$room_info;
        }





        return [
            'row_count'     => intval($count),
            'page_count'    => intval($p->pageCount),
            'page'          => intval($page),
            'list'          => $list,
        ];
    }



    /**
     * @return array
     * 拼团商品列表
     */
    public function getFindList()
    {
        $page = \Yii::$app->request->get('page')?:1;
        $limit = (int)\Yii::$app->request->get('limit')?:9;
        $cid = \Yii::$app->request->get('cid');


        $query2 = Topic::find() ->alias('t')
            ->select('cat_id,user_id,title,addtime,cover_pic,id topic_id,is_video video_id,is_video,sort')
            ->andWhere(['>','user_id',0])
            ->andWhere(['t.is_delete' => 0, 't.store_id' => $this->store_id, 't.status' => 1]);



        $query1 = Video::find() ->alias('v')
            ->select('cat_id,user_id,title,addtime,pic_url cover_pic ,is_video topic_id,id video_id,is_video,sort ')
            ->andWhere(['>','user_id',0])
            ->andWhere(['v.is_delete' => 0, 'v.store_id' => $this->store_id, 'v.status' => 1]);


        $query3 = Room::find() ->alias('r')
            ->select('cat_id,user_id,name title,addtime,pic_url cover_pic ,is_video topic_id,id video_id,is_video,sort ')
            ->andWhere(['>','user_id',0])
            ->andWhere(['r.is_delete' => 0, 'r.store_id' => $this->store_id, 'r.status' => 1]);


        if ((int)$cid){
            // 分类
            $query1->andWhere(['cat_id'=>$cid]);
            $query2->andWhere(['cat_id'=>$cid]);
            $query3->andWhere(['cat_id'=>$cid]);
        }

        $queryAll = $query1->union($query2, true)->union($query3, true);


        $query = (new Query())->from(['c' => $queryAll])
            ->select('u.avatar_url,u.nickname name,user_id,cat_id,title,cover_pic,topic_id,video_id,is_video,sort')
            ->innerJoin(['u'=>user::tableName()],'u.id = c.user_id')
            ->orderBy(['c.addtime'=>SORT_DESC]);

        if ((int)$cid){
            // 分类
            $query->andWhere(['c.cat_id'=>$cid]);
        }


        $count = $query->count();
        $p = new Pagination(['totalCount' => $count, 'pageSize' => $limit, 'page' => $page - 1]);
        $list = $query
            ->offset($p->offset)
            ->limit($p->limit)
            ->orderBy('c.sort ASC')
            ->all();

        foreach ($list AS $k => $v){
            $room_info='';
            if($v['room_id']){
                $room_info =  Room::findOne(['room_id' => $v['room_id'], 'store_id' => $this->store_id ,'is_delete' =>0]);
                if($room_info){
                    $room_info= $room_info->toArray();
                }
            }
            $list[$k]['room_info']=$room_info;
        }





        return [
            'row_count'     => intval($count),
            'page_count'    => intval($p->pageCount),
            'page'          => intval($page),
            'list'          => $list,
        ];
    }

    /**
     * @return mixed|string
     * 拼团商品详情
     */
    public function getInfo()
    {
        $info = Goods::find()
            ->andWhere(['is_delete'=>0,'store_id'=>$this->store_id,'status'=>1,'id'=>$this->gid])
            ->asArray()
            ->one();
        $goods = Goods::find()
            ->andWhere(['is_delete'=>0,'store_id'=>$this->store_id,'status'=>1,'id'=>$this->gid])->one();
        if (!$info){
            return [
                'code'  => 1,
                'msg'   => '商品不存在或已下架',
            ];
        }
        $info['pic_list'] = GoodsPic::find()
            ->select('pic_url')
            ->andWhere(['goods_id'=>$this->gid,'is_delete'=>0])
            ->column();
        $info['service'] = explode(',',$info['service']);
//        $shopId = explode(',',$info['shop_id']);
//        $shopList = Shop::find()
////            ->andWhere(['in','id',$shopId])
//            ->andWhere(['id'=>$shopId[0]])
//            ->andWhere(['store_id'=>$this->store_id])
//            ->asArray()
//            ->all();

        if (!empty($info['shop_id']) && $info['shop_id']!='-1'){

            $shopId = explode(',',trim($info['shop_id'],','));
            $shopList = Shop::find()
                ->andWhere(['id'=>$shopId[0]])
                ->andWhere(['store_id'=>$this->store_id,'is_delete'=>0])
                ->asArray()
                ->all();
            $info['shopListNum'] = count($shopId);
        }elseif ($info['shop_id']=='-1'){
            $info['shopListNum'] = 0;
            $shopList = [];
        }else{
            $shopList = Shop::find()
                ->andWhere(['store_id'=>$this->store_id,'is_delete'=>0])
                ->asArray()
                ->limit(1)
                ->all();
            $shopListNum = Shop::find()
                ->andWhere(['store_id'=>$this->store_id])
                ->count();
            $info['shopListNum'] = $shopListNum;
        }

        return [
            'code'  => 0,
            'msg'   => 'success',
            'data'  => [
                'info' => $info,
                'shopList' => $shopList,
            ],
        ];
    }

    /**
     * @return array
     * 评论列表
     */
    public function comment()
    {
        $query = OrderComment::find()
            ->alias('c')
            ->select([
                'c.score','c.content','c.pic_list','c.addtime',
                'u.nickname','u.avatar_url',
                'od.attr'
            ])
            ->andWhere(['c.store_id'=>$this->store_id,'c.goods_id'=>$this->gid,'c.is_delete'=>0,'c.is_hide'=>0])
            ->leftJoin(['u'=>User::tableName()],'u.id = c.user_id')
            ->leftJoin(['od'=>PtOrderDetail::tableName()],'od.id=c.order_detail_id');
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'page' => $this->page, 'pageSize' => 20]);

        $comment = $query
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->orderBy('c.addtime DESC')
            ->asArray()
            ->all();
        foreach ($comment AS $k => $v){
            $comment[$k]['attr'] = json_decode($v['attr'],true);
            $comment[$k]['pic_list'] = json_decode($v['pic_list'],true);
            $comment[$k]['addtime'] = date('m月d日',$v['addtime']);
            $comment[$k]['nickname'] = $this->substr_cut($v['nickname']);
        }
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'row_count' => $count,
                'page_count' => $pagination->pageCount,
                'comment' => $comment,
            ],
        ];
    }



    // 将用户名 做隐藏
    private function substr_cut($user_name){
        $strlen     = mb_strlen($user_name, 'utf-8');
        $firstStr     = mb_substr($user_name, 0, 1, 'utf-8');
        $lastStr     = mb_substr($user_name, -1, 1, 'utf-8');
        return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;
    }


    /**
     * @return array|object
     * 获取数量
     */
    public function countData()
    {
        if (!$this->validate())
            return $this->getModelError();
        $score_all = OrderComment::find()->alias('oc')
            ->where(['oc.goods_id' => $this->goods_id, 'oc.is_delete' => 0, 'oc.is_hide' => 0,])->count();
        $score_3 = OrderComment::find()->alias('oc')
            ->where(['oc.goods_id' => $this->goods_id, 'oc.is_delete' => 0, 'oc.is_hide' => 0, 'oc.score' => 3])->count();
        $score_2 = OrderComment::find()->alias('oc')
            ->where(['oc.goods_id' => $this->goods_id, 'oc.is_delete' => 0, 'oc.is_hide' => 0, 'oc.score' => 2])->count();
        $score_1 = OrderComment::find()->alias('oc')
            ->where(['oc.goods_id' => $this->goods_id, 'oc.is_delete' => 0, 'oc.is_hide' => 0, 'oc.score' => 1])->count();
        return (object)[
            'score_all' => $score_all ? $score_all : 0,
            'score_3' => $score_3 ? $score_3 : 0,
            'score_2' => $score_2 ? $score_2 : 0,
            'score_1' => $score_1 ? $score_1 : 0,
        ];
    }

}