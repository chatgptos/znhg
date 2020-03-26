<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/15
 * Time: 9:56
 */

namespace app\modules\api\models;

use app\extensions\getInfo;
use app\models\Business;
use app\models\CashWechatTplSender;
use app\models\Favorite;
use app\models\Goods;
use app\models\GoodsPic;
use app\models\Option;
use app\models\SeckillGoods;
use app\models\User;

class BusinessForm extends Model
{
    public $id;
    public $user_id;
    public $store_id;

    public function rules()
    {
        return [
            [['id'], 'required'],
            [['user_id'], 'safe'],
        ];
    }

    /**
     * 排序类型$sort   1--综合排序 2--销量排序
     */
    public function search()
    {
        if (!$this->validate())
            return $this->getModelError();
        $goods = Business::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'status' => 1,
            'store_id' => $this->store_id,
        ]);
        if (!$goods)
            return [
                'code' => 1,
                'msg' => '商品不存在或已下架',
            ];
        $pic_list['avatar_url'] =  User::findOne(['id' => $goods['user_id'], 'store_id' => $this->store_id])->avatar_url;
        $pic_list['nickname'] =  User::findOne(['id' => $goods['user_id'], 'store_id' => $this->store_id])->nickname;

        //新增点击发放奖金
//        $this->Hongbao(0,$goods['user_id'],false,false);


        //过滤掉不能看到红包的
        $getHongbao=new BusinessListForm();
        $getHongbao = $getHongbao->getUserHuobao();

        if(!$getHongbao){
            $goods->is_hongbao =0;
        }

        $is_favorite = 0;
        if ($this->user_id) {
            $exist_favorite = Favorite::find()->where(['user_id' => $this->user_id, 'goods_id' => $goods->id, 'is_delete' => 0])->exists();
            if ($exist_favorite)
                $is_favorite = 1;
        }

//      预计付费收益
        $huanledou_total =$goods->huanledou+$goods->huanledou_charge;// 需要的欢乐豆 + 总的*手续费

        return [
            'code' => 0,
            'data' => (object)[
                'id' => $goods->id,
                'userlist' => $pic_list,
                'title' => $goods->title,
                'is_exchange'=> $goods->is_exchange,
                'num' => $goods->num,
                'hld' => $goods->huanledou,
                'huanledou_charge' => $goods->huanledou_charge,
                'huanledou' => $goods->huanledou,
                'huanledou_total' => $huanledou_total,
                'xtjl' => $goods->xtjl,
                'is_favorite' => $is_favorite,
                'is_hongbao' => $goods->is_hongbao,
                'is_parent' => $goods->is_parent,
                'is_aim' => $goods->is_aim,
            ],
        ];
    }



    /**
     * 排序类型$sort   1--综合排序 2--销量排序
     */
    public function caihongbao()
    {
        if (!$this->validate())
            return $this->getModelError();
        $goods = Business::findOne([
            'id' => $this->id,
            'is_delete' => 0,
            'status' => 1,
            'store_id' => $this->store_id,
        ]);
        if (!$goods)
            return [
                'code' => 1,
                'msg' => '优惠券不存在或已下架',
            ];
        $pic_list['avatar_url'] =  User::findOne(['id' => $goods['user_id'], 'store_id' => $this->store_id])->avatar_url;
        $pic_list['nickname'] =  User::findOne(['id' => $goods['user_id'], 'store_id' => $this->store_id])->nickname;

        if($goods['user_id_hongbao']){
            $nickname_user_id_hongbao =User::findOne(['id' => $goods['user_id_hongbao'], 'store_id' => $this->store_id]);
            return json_encode([
                'code' => 0,
                'msg' => '已经被'.$nickname_user_id_hongbao->nickname.'抢走',
                'data'=> array(
                    'nickname_hongbao'=>$nickname_user_id_hongbao->nickname,
                    'avatar_url_hongbao'=>$nickname_user_id_hongbao->avatar_url,
                )
            ], JSON_UNESCAPED_UNICODE);
        }


//        $res=Business::updateAll( [
//            'is_hongbao' => 0,//发放了
//            'price_hongbao' => 0.3,//价格
//            'user_id_hongbao' => $this->user_id,//价格
//        ],  ['id' => $this->id ]);
//
//        $user = User::findOne(['id' => $this->user_id]);
//        return json_encode([
//            'code' => 1,
//            'msg' => '已经打到零钱包',
//            'res' =>$res,
//            'data'=> array(
//                'nickname_hongbao'=>$user->nickname,
//                'avatar_url_hongbao'=>$user->avatar_url,
//            )
//        ], JSON_UNESCAPED_UNICODE);


        //新增点击发放奖金
//        $this->Hongbao(0,$goods['user_id'],false,false);
        $price=0;//is_parent,is_aim
        $res = $this->Hongbao($goods['price_hongbao'],$goods['user_id'],$goods['is_parent'],$goods['is_aim'],$goods['is_hg']);
        return $res;
    }

    /**
     * 排序类型$sort   1--综合排序 2--销量排序
     *
     * 发放金额   操作目标
     */
    public function Hongbao($price,$useridAm,$is_parent=false,$isAm=false,$is_hg=0)
    {
        //目标用户 操作人
        $user = User::findOne(['id' => $this->user_id]);

        //广告
        $guanggao = array(
            '1' => "(兑换红包,金额:券池广告点击次数/千*人)",
            '2' => "(兑换必红包,去找券池找吧--就看你了)",
            '3' => "(兑换就有红包,找到就归你--券池留言)",
            '4' => "(推荐人也有红包,赶紧萌新--红包留言)"
//            '1' => "(每次兑换产生红包一个,金额为券池广告点击次数/1000*人数)"
        );

        if($is_hg){
            //广告
            $guanggao = array(
                '1' => "(智能机红包,金额:智能机开门次*0.3*人)",
                '2' => "(智能机必红包,去找券池找吧--就看你了)",
                '3' => "(智能机券池红包,找到就归你--券池留言)",
                '4' => "(智能机主也有红包,赶紧萌新--红包留言)",
                '5' => "(智能机坐享红包,'机'也生金蛋--券留言)"
            );
        }


        $ad = $guanggao[array_rand($guanggao)];

        //操作目标
        $user_from = User::findOne(['id' => $useridAm]);
        if(!$price){
            $price = 0.3;
        }
        $data = [
            'partner_trade_no' => md5(uniqid()),
            'openid' => $user->wechat_open_id,
            'amount' =>$price * 100,
            'desc' => '点到'.$this->r_mb_str($user_from->nickname,3).'优惠券红包'.$ad
        ];

        $res = $this->wechat->pay->transfers($data);
        if ($res['result_code'] != 'SUCCESS') {
            return json_encode([
                'code' => 1,
                'msg' => $res['err_code_des'],
                'data' => $res
            ], JSON_UNESCAPED_UNICODE);
        }
        //中间就记录下，以防超过限制
        Business::updateAll( [
            'is_hongbao' => 0,//发放了
            'price_hongbao' => $price,//价格
            'user_id_hongbao' => $this->user_id,//价格
        ],  ['id' => $this->id ]);

        $notice =date('h:m',time()).$this->r_mb_str(Option::get('notice', $this->store_id, 'admin'),250);
        Option::set('notice', $data['desc'].'|'.$notice, $this->store_id, 'admin');
        if($isAm){
            $data_from = [
                'partner_trade_no' => md5(uniqid()),
                'openid' => $user_from->wechat_open_id,
                'amount' =>$price * 100,
                'desc' => '优惠券获被'.$this->r_mb_str($user->nickname,3).'点击奖红包'.$ad
            ];

            $res = $this->wechat->pay->transfers($data_from);
            if ($res['result_code'] != 'SUCCESS') {
                return json_encode([
                    'code' => 1,
                    'msg' => $res['err_code_des'],
                    'data' => $res
                ], JSON_UNESCAPED_UNICODE);
            }
            $notice =date('h:m',time()).$this->r_mb_str(Option::get('notice', $this->store_id, 'admin'),250);
            Option::set('notice', $data_from['desc'].'|'.$notice, $this->store_id, 'admin');
        }
        //发给上级
        if($is_parent){
            //目标用户的上级
            $user_1 = User::findOne($user->parent_id);
            if (!$user_1) {
                return;
            }
            $data_1 = [
                'partner_trade_no' => md5(uniqid()),
                'openid' => $user_1->wechat_open_id,
                'amount' =>$price * 100,
                'desc' => '你推荐的'.$this->r_mb_str($user->nickname,3).'点到券池红包'.$ad
            ];
            $res = $this->wechat->pay->transfers($data_1);
            $notice =date('h:m',time()).$this->r_mb_str(Option::get('notice', $this->store_id, 'admin'),250);
            Option::set('notice', $data_1['desc'].'|'.$notice, $this->store_id, 'admin');
            if ($res['result_code'] != 'SUCCESS') {
                return json_encode([
                    'code' => 1,
                    'msg' => $res['err_code_des'],
                    'data' => $res
                ], JSON_UNESCAPED_UNICODE);
            }
        }

        if ($res['result_code'] == 'SUCCESS') {
            //发模版消息
            Business::updateAll( [
                'is_hongbao' => 0,//发放了
                'price_hongbao' => $price,//价格
                'user_id_hongbao' => $this->user_id,//价格
            ],  ['id' => $this->id ]);


            //查询公告信息发布
            //如果当天发布红包优惠券数量超过
            $is_hongbao_num_now = Business::find()->alias('g')
                ->where([
                    'g.status' => 1,
                    'g.is_delete' => 0,
                    'g.store_id' => $this->store_id,
                ])
                ->andWhere(['>', 'is_hongbao', 0])
                ->andWhere(['>', 'addtime', strtotime(date('Y-m-d'))])
                ->count();
            //过期红包 已经交易 但是没有使用
            $is_hongbao_num_now_deasper = Business::find()->alias('g')
                ->where([
                    'g.status' => 1,
                    'g.is_delete' => 0,
                    'g.is_exchange' => 1,
                    'g.store_id' => $this->store_id,
                ])
                ->andWhere(['>', 'is_hongbao', 0])
                ->andWhere(['>', 'addtime', strtotime(date('Y-m-d'))])
                ->count();


            $user_id_hongbao_num_now = Business::find()->alias('g')
                ->where([
                    'g.status' => 1,
                    'g.is_delete' => 0,
                    'g.store_id' => $this->store_id,
                ])
                ->andWhere(['>', 'user_id_hongbao', 0])
                ->andWhere(['>', 'addtime', strtotime(date('Y-m-d'))])
                ->count();


            //广告
            $guanggao = array(
                '1' => "(每个人看到的是不一样的红包，那是我分身--裂变红包)",
                '2' => "(他们有猪一样的队友把我们一起和券卖了也不知道多傻--券池红包)",
                '3' => "(扣扣鼻屎，看你们折腾--爆击红包)",
                '4' => "(我们最喜欢新萌了,新萌来了我就出来--券池留言)"
//            '1' => "(每次兑换产生红包一个,金额为券池广告点击次数/1000*人数)"
            );
            $ad = $guanggao[array_rand($guanggao)];


            $noticeHb='「券池花边新闻」:他们一波兄弟来了'.$is_hongbao_num_now .'个,挂了'.$user_id_hongbao_num_now.'个,跑了'.$is_hongbao_num_now_deasper.'券池还有'.($is_hongbao_num_now-$user_id_hongbao_num_now).'是藏起来的！！,最怕他们多刷把我刷出来了'.$ad;

            //不管内容是什么补齐250个末尾再增加
            $notice =date('h:m',time()).$this->r_mb_str_kg(Option::get('notice', $this->store_id, 'admin'),200).$noticeHb;
            Option::set('notice', $notice, $this->store_id, 'admin');



            return json_encode([
                'code' => 0,
                'msg' => '已经打到零钱包',
                'data'=> array(
                    'nickname_hongbao'=>$user->nickname,
                    'avatar_url_hongbao'=>$user->avatar_url,
                 )
            ], JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode([
                'code' => 1,
                'msg' => $res['err_code_des'],
                'data' => $res
            ], JSON_UNESCAPED_UNICODE);
        }
    }


    /**
     * 补齐空格
     * 截取$n个中文字符长度
     */
    private function r_mb_str_kg($input, $n)
    {
        $string = "";
        $count = 0;
        $c_count = 0;
        for ($i = 0; $i < mb_strlen($input, 'UTF-8'); $i++) {
            $char = mb_substr($input, $i, 1, 'UTF-8');
            $string .= $char;
            if (strlen($char) == 3) {
                $count += 2;
                $c_count++;
            } else {
                $count += 1;
            }
            if ($count >= 2 * $n) {
                break;
            }
        }
        if ($count < 2 * $n) {
            $string = str_pad($string, 2 * $n + $c_count);
        }
        return $string;
    }
    /**
     * 补齐空格
     * 截取$n个中文字符长度
     */
    private function r_mb_str($input, $n)
    {
        $string = mb_substr($input, 0, $n);
        return $string;
    }


    //获取商品秒杀数据
    public function getSeckillData($goods_id)
    {
        $seckill_goods = SeckillGoods::findOne([
            'goods_id' => $goods_id,
            'is_delete' => 0,
            'start_time' => intval(date('H')),
            'open_date' => date('Y-m-d'),
        ]);
        if (!$seckill_goods)
            return null;
        $attr_data = json_decode($seckill_goods->attr, true);
        $total_seckill_num = 0;
        $total_sell_num = 0;
        $seckill_price = 0.00;
        foreach ($attr_data as $i => $attr_data_item) {
            $total_seckill_num += $attr_data_item['seckill_num'];
            $total_sell_num += $attr_data_item['sell_num'];
            if ($seckill_price == 0) {
                $seckill_price = $attr_data_item['seckill_price'];
            } else {
                $seckill_price = min($seckill_price, $attr_data_item['seckill_price']);
            }
        }
        return [
            'seckill_num' => $total_seckill_num,
            'sell_num' => $total_sell_num,
            'seckill_price' => (float)$seckill_price,
            'begin_time' => strtotime($seckill_goods->open_date . ' ' . $seckill_goods->start_time . ':00:00'),
            'end_time' => strtotime($seckill_goods->open_date . ' ' . $seckill_goods->start_time . ':59:59'),
            'now_time' => time(),
        ];
    }
}