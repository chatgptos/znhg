<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/9/29
 * Time: 18:57
 */

namespace app\modules\api\models;


use app\models\IntegralLog;
use app\models\Topic;
use app\models\TopicFavorite;
use app\models\User;

class TopicFavoriteForm extends Model
{
    public $store_id;
    public $user_id;
    public $topic_id;
    public $action;

    public function rules()
    {
        return [
            [['topic_id', 'action'], 'required'],
            [['action'], 'in', 'range' => [0,1,2,3,5]],
        ];
    }

    public function save()
    {
        if (!$this->validate())
            return $this->getModelError();
        if ($this->action == 0) {
            TopicFavorite::updateAll([
                'is_delete' => 1
            ], [
                'user_id' => $this->user_id,
                'topic_id' => $this->topic_id,
                'is_delete' => 0,
                'store_id' => $this->store_id,
            ]);
            return [
                'code' => 0,
                'msg' => '已取消',
            ];
        }

        //当等于1时候收藏
        if($this->action == 1){
            $favorite = TopicFavorite::findOne([
                'user_id' => $this->user_id,
                'topic_id' => $this->topic_id,
                'is_delete' => 0,
                'store_id' => $this->store_id,
            ]);
            if ($favorite)
                return [
                    'code' => 0,
                    'msg' => '收藏成功',
                ];
            $favorite = new TopicFavorite();
            $favorite->attributes = $this->attributes;
            $favorite->addtime = time();
            if ($favorite->save())
                return [
                    'code' => 0,
                    'msg' => '收藏成功',
                ];
            return $this->getModelError($favorite);
        }

        //当等于2时候追更
        if($this->action == 2){
            $favorite = TopicFavorite::findOne([
                'user_id' => $this->user_id,
                'topic_id' => $this->topic_id,
                'is_delete' => 0,
                'store_id' => $this->store_id,
            ]);
            if ($favorite)
                return [
                    'code' => 0,
                    'msg' => '成功通知作者',
                ];
            $favorite = new TopicFavorite();
            $favorite->attributes = $this->attributes;
            $favorite->addtime = time();
            if ($favorite->save())
                return [
                    'code' => 0,
                    'msg' => '成功通知作者',
                ];
            return $this->getModelError($favorite);
        }

        //当等于3时候购买该书
        if($this->action == 3){
            $model = Topic::find()
                ->alias('t')
                ->where(['t.store_id' => $this->store_id, 't.id' => $this->topic_id, 't.is_delete' => 0])
                ->select('user_id,u.avatar_url,u.nickname,t.id,title,read_count,virtual_read_count,content,t.addtime,virtual_favorite_count')
                ->innerJoin(['u' => User::tableName()], 'u.id=t.user_id')
                ->asArray()->one();

            //查找出所有的该书籍用户
            $favorite_user_list = TopicFavorite::find()
                ->select('u.avatar_url,u.nickname')
                ->alias('t')
                ->innerJoin(['u' => User::tableName()], 'u.id=t.user_id')
                ->where([
                    'topic_id' => $this->topic_id,
                    't.is_delete' => 0,
                    't.store_id' => $this->store_id,
                ]);
            $favorite_count=$favorite_user_list->count();



            //扣除和新增hld


            $user = User::findOne(['id' => $model['user_id']]);

            $user_buyer = User::findOne(['id' => $this->user_id]);

            if (!$user || !$user_buyer){
                return [
                    'code' => 1,
                    'msg' => '用户不存在',
                ];
            }



            if($model['user_id']==$this->user_id){
                return [
                    'code' => 1,
                    'msg' => '不能购买自己作品',
                ];
            }


            $model['read_count'] = intval($model['read_count']) + intval($model['virtual_read_count']);
            $goumai_hld=$model['read_count']+$favorite_count*100;
            $huanledou_charge=0.1;


            //扣除双方手续费
            //卖家
            $sellhld = (int)intval($user->hld + $goumai_hld - $goumai_hld*$huanledou_charge);//欢乐豆卖家 + 总的-手续费
            $selltotal_hld = (int)intval($user->total_hld + $goumai_hld - $goumai_hld*$huanledou_charge);//欢乐豆卖家 + 总的-手续费


            $user->hld = intval($sellhld);
            $user->total_hld = intval($selltotal_hld);
            //买家
            $buyhld = (int)intval($user_buyer->hld - $goumai_hld - $goumai_hld*$huanledou_charge);//欢乐豆卖家 + 总的-手续费
            $buytotal_hld = (int)intval($user_buyer->total_hld - $goumai_hld - $goumai_hld*$huanledou_charge);//欢乐豆卖家 + 总的-手续费

            $user_buyer->hld =intval( $buyhld);
            $user_buyer->total_hld = intval($buytotal_hld);
            //xtjl

            if (($user_buyer->hld) < 0) {
                return [
                    'code' => 1,
                    'msg' => $user_buyer->hld.'欢乐豆不够',
                ];
            }

            $t = \Yii::$app->db->beginTransaction();

            $res=Topic::updateAll([
                'user_id' => $this->user_id,
                'id' => $this->topic_id,
                'is_delete' => 0,
                'store_id' => $this->store_id,
                'virtual_favorite_count' =>  $model['virtual_favorite_count']+$favorite_count,//重新计算追更人数量
            ], ['id' =>  $this->topic_id]);
            //卖家 卖
            $this->insertintegralLog(1, $user->id, $this->topic_id, $goumai_hld, $huanledou_charge, $goumai_hld*$huanledou_charge);
            //买家 买
            $this->insertintegralLog(2, $user_buyer->id, $this->topic_id, $goumai_hld, $huanledou_charge, $goumai_hld*$huanledou_charge);

            if ($res && $user->save() && $user_buyer->save()) {
                $t->commit();
                return [
                    'code' => 0,
                    'msg' => '成功买下',
                    'data' => [
                        'hld' => $goumai_hld,
                        'nickname' => $user_buyer->nickname,
                        'avatar_url' => $user_buyer->avatar_url,
                    ],
                ];
            } else {
                $t->rollBack();
                return $this->getModelError($res);
            }
        }

    }




    public function insertintegralLog($rechangeType, $user_id, $num, $hld = 0, $xtjl = 0, $sxf)
    {


        $user = User::findOne(['id' => $user_id]);
        $integralLog = new IntegralLog();
        $integralLog->user_id = $user->id;
        if ($rechangeType == '2') {
            //买优惠券
            $integralLog->content = "购买书籍 券池操作：" . $user->nickname . " 欢乐豆".$user->hld."已经扣除：" . $hld . " 豆" . $num . " 文章id" . "系统奖励" . $xtjl;
        } elseif ($rechangeType == '1') {
            //卖优惠券
            $integralLog->content = "购买书籍 券池操作：" . $user->nickname . " 欢乐豆".$user->hld."已经充值：" . $hld . " 豆"  . $num . " 文章id,（交易时扣除去手续费" . $sxf . '个欢乐豆）';
        }

        $integralLog->hld = $hld;
        $integralLog->coupon = $num + $xtjl;
        $integralLog->addtime = time();
        $integralLog->username = $user->nickname;
        $integralLog->operator = 'admin';
        $integralLog->store_id = $this->store_id;
        $integralLog->operator_id = 0;
        $integralLog->save();
    }
}