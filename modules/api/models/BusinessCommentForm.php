<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/16
 * Time: 10:11
 */

namespace app\modules\api\models;


use app\models\Business;
use app\models\BusinessComment;
use app\models\BusinessSetting;
use app\models\IntegralLog;
use app\models\Option;
use app\models\Order;
use app\models\OrderComment;
use app\models\OrderDetail;
use app\models\User;
use yii\helpers\Html;

class BusinessCommentForm extends Model
{
    public $store_id;
    public $user_id;
    public $order_id;
    public $goods_list;
    public $sethuanledou = 7;//7欢乐豆一张
    public $JFTOHLD = 10;//积分对欢乐豆
    public $do_JFTOHLD = false;//积分对欢乐豆是否打开
    public $BusinessSetting;//积分对欢乐豆是否打开

    public $time;//当前时间
    public $open_time;//开放时间
    public $hldtoyhq = 7;//7欢乐豆一张
    public $xtjl = 1;//系统赠送张数
    public $xtjlsell;//系统赠送张数卖方
    public $jftohld;//积分对欢乐豆
    public $hldtojf;//欢乐豆对积分
    public $charge = 3;//百分比手续费
    public $charge1;//百分比手续费2级
    public $charge2;//百分比手续费3级
    public $is_hldtoyhq;//欢乐豆对优惠券是否打开 买优惠券
    public $is_jftohld;//积分对欢乐豆是否打开
    public $is_hldtojf;//欢乐豆对积分是否打开
    public $is_yhqtohld;//优惠券对欢乐豆是否打开 卖优惠券
    public $charge3;//优惠券对欢乐豆是否打开 卖优惠券
    public $chargeNum;//优惠券对欢乐豆是否打开 卖优惠券
    public $chargeNum1;//优惠券对欢乐豆是否打开 卖优惠券
    public $chargeNum2;//优惠券对欢乐豆是否打开 卖优惠券
    public $chargeNum3;//优惠券对欢乐豆是否打开 卖优惠券
    public $charge5;//优惠券对欢乐豆是否打开 卖优惠券
    public $is_hongbao_gl =2;//优惠券对欢乐豆是否打开 卖优惠券
    public $is_hongbao_num =100;//优惠券对欢乐豆是否打开 卖优惠券




    public $is_hongbao;//优惠券对欢乐豆是否打开 卖优惠券
    public $is_parent;//优惠券对欢乐豆是否打开 卖优惠券
    public $is_aim;//优惠券对欢乐豆是否打开 卖优惠券

    public $num;//优惠券对欢乐豆是否打开 卖优惠券
    public $is_hg=0;//优惠券对欢乐豆是否打开 卖优惠券





    public function rules()
    {
        return [
        ];
    }


    //卖
    public function add()
    {
        $check = $this->getBusinessSetting();
        if ($check) {
            return json_encode([
                'code' => 1,
                'msg' => $check
            ], JSON_UNESCAPED_UNICODE);
        }
        if (!$this->is_yhqtohld) {
            return json_encode([
                'code' => 1,
                'msg' => '暂未开放'
            ], JSON_UNESCAPED_UNICODE);
        }

        $num = (int)\Yii::$app->request->post('num');

        if(!$num){
            $num =$this->num;
        }

        $user = User::findOne(['id' => $this->user_id]);
        $coupon = $user->coupon;
        $coupon_total = $user->coupon_total;


        if ($num < 1 || !is_int($num)) {
            return json_encode([
                'code' => 1,
                'msg' => '数量不正确'
            ], JSON_UNESCAPED_UNICODE);
        } elseif ($num > $user->coupon) {
            return json_encode([
                'code' => 1,
                'msg' => '优惠券不足'
            ], JSON_UNESCAPED_UNICODE);
        }


        if (!$this->validate())
            return $this->getModelError();

        //发布的时候券出去了
        $user->coupon = $coupon - $num;
        $user->coupon_total = $coupon_total - $num;


        $Business = new Business();
        $Business->store_id = $this->store_id;
        $Business->status = 1;//售卖中 上架
        $Business->user_id = $this->user_id;//卖方用户id

        $guanggao = array(
            '1' => "欢乐豆出，机不可失 买到就是赚到！！"
        , '2' => "欢乐豆出，平台优惠券，立刻出手！！"
        , '3' => "欢乐豆出，可以兑抽奖的优惠券出手！！"
        , '4' => "欢乐豆出，平台保证！立刻出兑换"
        , '5' => '欢乐豆出，良心优惠券出手'
        , '6' => '欢乐豆出，优惠券可参加福利分红'
        , '7' => '欢乐豆出，劲爆优惠券 欢乐豆就出手'
        , '8' => '欢乐豆出，系统奖励系统奖励 兑换就送'
        , '9' => '欢乐豆出，机不可失 买到就是赚到！！'
        , '10' => '欢乐豆出，千万不要错过 ！！'
        , '10' => '欢乐豆出，错过就后悔 ！！'
        , '11' => '欢乐豆出，手续费劲爆最低 ！！'
        , '12' => '欢乐豆出，货柜分红想要就  收集优惠券 ！！'
        , '13' => '欢乐豆出，券池广告收益全部计入本期福利分红'
        , '14' => '欢乐豆出，小心券池红包，藏起来的 ！！'
        , '15' => '欢乐豆出，小心被券池红包砸到，多点点就有红包 ！！'
        , '16' => '欢乐豆出，小心券池详情页也有红包 ！！'
        , '17' => '欢乐豆出，红包全部来源券池收益 ！！'
        , '18' => '欢乐豆出，券池红包最喜欢刚下单的新人 ！！'
        , '19' => '欢乐豆出，红包是藏起来的，藏的越深越大 ！！'
        , '20' => '欢乐豆出，点击优惠券，容易出红包 ！！'
        , '21' => '欢乐豆出，被点击的优惠券，所有人也会收到红包 ！！'
        , '22' => '欢乐豆出，点出红包的推荐人，也会收到红包 ！！'
        , '23' => '欢乐豆出，红包和订单数有关！！'
        , '24' => '欢乐豆出，红包和每天券交易数量有关！！'
        , '25' => '欢乐豆出，红包喜欢留言！！'
        , '26' => '欢乐豆出，红包留言：猜猜我在哪里！！'
        , '27' => '欢乐豆出，优惠券留言：我最喜欢被点了！！'
        , '28' => '欢乐豆出，优惠券大喊：点我点我，记得详情页也有红包！！'
        , '29' => '欢乐豆出，推荐新人有红包！！'
        , '30' => '欢乐豆出，券池最喜欢推荐新人的家伙了'
        , '31' => '欢乐豆出，前往别和他们说往下刷也可以把我们刷出来'
        , '32' => '欢乐豆出，我才不会告诉你我藏在底下，哼'
        , '33' => '欢乐豆出，坐在最底下真爽，谁也不知道'
        );
        $Business->title = $num . '优惠券，' . $this->hldtoyhq * $num . $guanggao[array_rand($guanggao)];//卖的张数
//        $Business->order_num = $this->user_id; //成交交易数量
//        $Business->integral = $this->user_id; //需要积分


//      成交金额内扣除手续费（冻结手续费 减少可用欢乐豆）
//      发布时候扣除卡券数量

//      卖的张数
        $Business->num = $num;//卖的张数


//      欢乐豆实际总价值
        $Business->huanledou = (int) intval($this->hldtoyhq * $num);//卖的张数*平台固定的每张欢乐豆价值

//      手续费欢乐豆价值
        $Business->huanledou_charge = (int) intval(($this->getCharge($num)) * 0.01 * ($this->hldtoyhq * $num));//卖的张数*平台固定的欢乐豆

//      系统奖励
        $Business->xtjl = (int)intval($this->xtjl);//系统奖励

//      合计收益
        $huanledou_total = (int)intval($Business->huanledou - $Business->huanledou_charge);// 需要的欢乐豆 + 总的*手续费

        $Business->addtime = time();

        //红包绑定
        //总收益 千次    1000*7*0.3*2=4200欢乐豆=60元
        //总支出 千次    9元+6元=15元,预计红包
        //总广告广告收益  1万/千次曝光*0.03=0.3元
        //概率方法 1-10 设置 百分之一 全量， 10-100 约1/100 设置98以上必中



        $gailv=rand($this->is_hongbao_gl*10,1000);

        $adhb='';
        if($this->is_hongbao_gl==1){
            //发全量红包 0.9元  强制
            $Business->is_hongbao = rand(1,2);//一半详情
            $Business->is_parent = 1;
            $Business->is_aim = 1;
            $guanggao = array(
                '1' => '欢乐豆出，裂变红包：挖槽挖槽我是能裂变的，快点我！！！'
            , '2' => '欢乐豆出，裂变红包：抢到我你就发了，包括你的红娘！！'
            , '3' => '欢乐豆出，裂变红包：我就喜欢黏在这张券上🈶🈶又砸门了，我就是喜欢！！'
            , '4' => '欢乐豆出，裂变红包：我帮我主人挣钱，你点我我就给他钱不行吗?！！'
            , '5' => '欢乐豆出，裂变红包：最喜欢推荐新人的臭大叔了'
            , '6' => '欢乐豆出，裂变红包：好吧我招了，A我下我就了'
            , '7' => '欢乐豆出，裂变红包：想谢谢推荐人吗，点我，说得再好不如送人红包，说到做到！！'
            );
            $adhb='又一个爆击红包出生在券池！！生亦何欢死亦何爆我，我命由我不由天！';
        }

        if($gailv<101){
            //发全量红包 0.9元  1/100概率 每千次 支出：9元
            $Business->is_hongbao = rand(1,2);//一半详情
            $Business->is_parent = 1;
            $Business->is_aim = 1;
            $guanggao = array(
                '1' => '欢乐豆出，裂变红包：挖槽挖槽我是能裂变的，快点我！！！'
            , '2' => '欢乐豆出，裂变红包：抢到我你就发了，包括你的红娘！！'
            , '3' => '欢乐豆出，裂变红包：我就喜欢黏在这张券上🈶🈶又砸门了，我就是喜欢！！'
            , '4' => '欢乐豆出，裂变红包：我帮我主人挣钱，你点我我就给他钱不行吗?！！'
            , '5' => '欢乐豆出，裂变红包：最喜欢推荐新人的臭大叔了'
            , '6' => '欢乐豆出，裂变红包：好吧我招了，A我下我就了'
            , '7' => '欢乐豆出，裂变红包：想谢谢推荐人吗，点我，说得再好不如送人红包，说到做到！！'
            , '7' => '欢乐豆出，裂变红包：我藏起来，才不告诉你我在这张券里面！！'
            );
            $adhb='又一个爆击红包出生在券池！！低调不是醉';
        }elseif($gailv>980){
            //发半量红包 0.6元  2/100概率 每千次 支出: 20次*0.3=6元
            $Business->is_hongbao = rand(1,2);//一半详情
            $Business->is_parent = 1;
            $guanggao = array(
                '1' => '欢乐豆出，券池红包：挖槽挖槽我是能粘住钱的的，快点我！！！'
            , '2' => '欢乐豆出，券池红包：抢到我就好了，我会告诉喜欢你的人呢！！'
            , '3' => '欢乐豆出，券池红包：我就喜欢黏在这张券上🈶🈶又砸门了，我就是喜欢！！'
            , '4' => '欢乐豆出，券池红包：我帮你的红娘开心一下，你点我我就给他钱不行吗?！！'
            , '4' => '欢乐豆出，券池红包：你领导是谁爱她吗，点我，你点我我就给她钱不行吗?！！'
            , '4' => '欢乐豆出，券池红包：你领导是谁爱他吗，点我，你点我我就给他钱不行吗?！！'
            , '5' => '欢乐豆出，券池红包：这回我要狠狠爱一下介绍你来的人'
            , '6' => '欢乐豆出，券池红包：券池是我加，我就最爱她，谁介绍你来的，告诉我'
            , '7' => '欢乐豆出，券池红包：想谢谢推荐人吗，点我，说得再好不如送人红包，说到做到！！'
            , '7' => '欢乐豆出，券池红包：我藏起来，才不告诉你我在这张券里面！！'
            );
            $adhb='又一个裂变红包出生在券池！！干嘛要告诉你我什么时候出生的？';
            if($gailv>990){
                //发半量红包 0.6元  2/100概率 每千次 支出: 20次*0.3=6元
                $Business->is_hongbao = rand(1,2);//一半详情
                $Business->is_aim = 1;
                $guanggao = array(
                    '1' => '欢乐豆出，暴击红包：挖槽挖槽我是能粘的，快点我，我这就炸银行！！！'
                , '2' => '欢乐豆出，暴击红包：你有我就好了，我是会发钱的！！'
                , '3' => '欢乐豆出，暴击红包：我能暴击，哐哐哐别惹我！！'
                , '4' => '欢乐豆出，暴击红包：你想干嘛，这么晚还敢找我?！！'
                , '4' => '欢乐豆出，暴击红包：A一下我我就炸给你看，点我，你点我我就给她钱不行吗?！！'
                , '4' => '欢乐豆出，暴击红包：我就是任性，点我，你点我我就给他钱不行吗?！！'
                , '5' => '欢乐豆出，暴击红包：这回我的券生我做主，我就是喜欢发钱'
                , '6' => '欢乐豆出，暴击红包：你想怎么样？我脾气不好别惹我，惹急了我就用钱炸你'
                , '7' => '欢乐豆出，暴击红包：点我，点我，优惠券那小子最喜欢别人，点他了！！'
                , '7' => '欢乐豆出，暴击红包：我藏起来，才不告诉你我在这张券里面！！'
                );
                $adhb='又一个券池红包出生在券池！！想找我？谜语:看我生辰就知道我姓啥';
            }
        }





        //广告覆盖
        $Business->title = $num . '优惠券，' . $this->hldtoyhq * $num . $guanggao[array_rand($guanggao)];//卖的张数

        //如果当天发布红包优惠券数量超过
        $query = Business::find()->alias('g')
            ->where([
                'g.status' => 1,
                'g.is_delete' => 0,
                'g.store_id' => $this->store_id,
            ])
            ->andWhere(['>', 'user_id_hongbao', 0])
            ->andWhere(['>', 'addtime', strtotime(date('Y-m-d'))])
            ->count();


        //超过数量限制时候
        if($query>$this->is_hongbao_num){
            $Business->is_hongbao = 0;//
            $Business->is_parent = 0;
            $Business->is_aim = 0;
        }



        //不参与限制
        if($this->is_hg){
            if($this->is_hg==1){
                $Business->is_hongbao = 1;//
                $Business->is_parent = 1;
                $Business->is_aim = 1;
                $Business->is_hg = 1;

            }

            if($this->is_hg==2){
                $Business->is_hongbao = 2;//
                $Business->is_parent = 1;
                $Business->is_aim = 1;
                $Business->is_hg = 2;
            }
            $guanggao = array(
                '1' => '欢乐豆出，暴击红包：卧槽老子是智能饮料机来的，点进来必爆！'
            , '2' => '欢乐豆出，暴击红包：智能机消费赠送一个暴击红包！！'
            , '3' => '欢乐豆出，暴击红包：我能爆击，老子智能机来的！！'
            , '4' => '欢乐豆出，暴击红包：你想干嘛，这么晚还来"小机"那里买东西奖你?！'
            , '4' => '欢乐豆出，暴击红包：点我给智能机主人自动发红包咯，我是货柜来的?！'
            , '4' => '欢乐豆出，暴击红包：智能机自动绑定主人哦，坐着也收钱?！！'
            , '5' => '欢乐豆出，暴击红包：谁说公"机"不生蛋没有生蛋，我生金蛋！！'
            , '6' => '欢乐豆出，暴击红包：我是货柜来的？难得我开心，不和你"机"较'
            , '7' => '欢乐豆出，暴击红包：点我，点我，我喜欢智能机，点他了！！'
            , '7' => '欢乐豆出，暴击红包：我不藏起来了，我有智能机大哥我怕谁！！'
            , '7' => '欢乐豆出，暴击红包：智能机半夜也收钱，新人必爆！！'
            );
            $adhb='暴击红包："有人在-智能鲜蜂智能机购买饮料，该券必爆，点我点我"';
            //广告覆盖
            $Business->title = $num . '优惠券，' . $this->hldtoyhq * $num . $guanggao[array_rand($guanggao)];//卖的张数

        }




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
            '2' => "(他们有猪一样的队友把我们一起和券卖了也不知道多傻啦吧唧--券池红包)",
            '3' => "(扣扣鼻屎，看你们折腾--爆击红包)",
            '4' => "(我们最喜欢新萌了,新萌来了我就出来--券池留言)"
//            '1' => "(每次兑换产生红包一个,金额为券池广告点击次数/1000*人数)"
        );
        $ad = $guanggao[array_rand($guanggao)];


         $noticeHb='「券池花边新闻」:他们一波兄弟来了'.$is_hongbao_num_now .'个,挂了'.$user_id_hongbao_num_now.'个,跑了'.$is_hongbao_num_now_deasper.'券池还有'.($is_hongbao_num_now-$is_hongbao_num_now_deasper).'是藏起来的！！,最怕他们多刷把我刷出来了'.$ad;

        //不管内容是什么补齐250个末尾再增加
        $notice =date('h:m',time()).$this->r_mb_str_kg(Option::get('notice', $this->store_id, 'admin'),200).$noticeHb.'!!!!'.$adhb;
        Option::set('notice', $notice, $this->store_id, 'admin');



        $t = \Yii::$app->db->beginTransaction();


        //卖家 卖
        $this->insertintegralLog(1, $user->id, $Business->num,$Business->huanledou, $Business->xtjl, $Business->huanledou_charge);


        if ($Business->save() && $user->save()) {
            $t->commit();

            $user = User::findOne(['id' => $this->user_id]);
            return [
                'code' => 0,
                'data' => array(
                    'huanledou_total' => $huanledou_total,//合计收益
                    'coupon' => $user->coupon,//合计收益
                    'coupon_total' => $user->coupon_total,//合计收益
                    'huanledou_charge' => $Business->huanledou_charge,//合计收益
                ),
                'msg' => '提交成功',
            ];
        } else {
            $t->rollBack();
            return $this->getModelError($Business);
        }

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
    /*
     * 买
     * */
    public function exchange()
    {

        $check = $this->getBusinessSetting();
        if ($check) {
            return json_encode([
                'code' => 1,
                'msg' => $check
            ], JSON_UNESCAPED_UNICODE);
        }

        if (!$this->is_hldtoyhq) {
            return json_encode([
                'code' => 1,
                'msg' => '暂未开放'
            ], JSON_UNESCAPED_UNICODE);
        }


        if (!$this->validate())
            return $this->getModelError();

        $order_id = (int)\Yii::$app->request->post('order_id');

        $order = Business::findOne([
            'id' => $order_id,
            'store_id' => $this->store_id,
            'is_delete' => 0,
        ]);
        if (!$order)
            return [
                'code' => 1,
                'msg' => '交易不存在',
            ];


        $user = User::findOne(['id' => $order->user_id]);


        $user_buyer = User::findOne(['id' => $this->user_id]);

        if (!$order || !$user_buyer)
            return [
                'code' => 1,
                'msg' => '用户不存在',
            ];



        if ($this->user_id == $order->user_id) {
            return [
                'code' => 1,
                'msg' => '自己不能购买',
            ];
        }


        $order->is_exchange = 1;
        $order->user_id_buyer = $this->user_id;

        //扣除双方手续费
        //卖家
        $sellhld = (int)intval($user->hld + $order->huanledou - $order->huanledou_charge);//欢乐豆卖家 + 总的-手续费
        $selltotal_hld = (int)intval($user->total_hld + $order->huanledou - $order->huanledou_charge);//欢乐豆卖家 + 总的-手续费


        $user->hld = intval($sellhld);
        $user->total_hld = intval($selltotal_hld);
        //xtjl
        //失去券 发布的时候券就失去了
//        $user->coupon = $user->coupon - $order->num;
//        $user->coupon_total = $user->coupon_total - $order->num;

        //买家
        $buyhld = (int)intval($user_buyer->hld - $order->huanledou - $order->huanledou_charge);//欢乐豆卖家 + 总的-手续费
        $buytotal_hld = (int)intval($user_buyer->total_hld - $order->huanledou - $order->huanledou_charge);//欢乐豆卖家 + 总的-手续费

        $user_buyer->hld =intval( $buyhld);
        $user_buyer->total_hld = intval($buytotal_hld);
        //xtjl


        //得到券
        $buycoupon = (int)intval($user_buyer->coupon + $order->num + $order->xtjl);
        $buycoupon_total = (int)intval($user_buyer->coupon_total + $order->num + $order->xtjl);

        $user_buyer->coupon = $buycoupon;
        $user_buyer->coupon_total = $buycoupon_total;

        if (($user_buyer->hld) < 0) {
            return [
                'code' => 1,
                'msg' => '欢乐豆不够',
            ];
        }

        $t = \Yii::$app->db->beginTransaction();


        //卖家 卖
        $this->insertintegralLog(1, $user->id, $order->num, $order->huanledou, $order->xtjl, $order->huanledou_charge);
        //买家 买
        $this->insertintegralLog(2, $user_buyer->id, $order->num, $order->huanledou, $order->xtjl, $order->huanledou_charge);





        if ($order->save() && $user->save() && $user_buyer->save()) {
            $t->commit();
            return [
                'code' => 0,
                'msg' => '交易成功',
                'data' => array(
                    'coupon' => $user_buyer->coupon,
                    'nickname' => $user_buyer->nickname,
                    'is_exchange' => 1,
                )
            ];
        } else {
            $t->rollBack();
            return $this->getModelError($order);
        }

    }


    public function insertintegralLog($rechangeType, $user_id, $num, $hld = 0, $xtjl = 0, $sxf)
    {


        $user = User::findOne(['id' => $user_id]);
        $integralLog = new IntegralLog();
        $integralLog->user_id = $user->id;
        if ($rechangeType == '2') {
            //买优惠券
            $integralLog->content = "管理员（欢乐豆兑换优惠券） 后台操作账号：" . $user->nickname . " 欢乐豆".$user->hld."已经扣除：" . $hld . " 豆" . " 优惠券".$user->coupon."已经充值（包含奖励）：" . $num . " 张" . "系统奖励" . $xtjl;
        } elseif ($rechangeType == '1') {
            //卖优惠券
            $integralLog->content = "管理员（优惠券换欢乐豆） 后台操作账号：" . $user->nickname . " 欢乐豆".$user->hld."已经充值：" . $hld . " 豆" . " 优惠券".$user->coupon."已经扣除：" . $num . " 张,（发布时候已经扣除优惠券）（交易时扣除去手续费" . $sxf . '个欢乐豆）';
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


    public function getBusinessSetting()
    {
        $this->BusinessSetting = BusinessSetting::findOne(['store_id' => $this->store_id]);
        $this->open_time = $this->BusinessSetting['open_time'];
        $this->hldtoyhq = $this->BusinessSetting['hldtoyhq'];
        $this->xtjl = $this->BusinessSetting['xtjl'];
        $this->xtjlsell = $this->BusinessSetting['xtjlsell'];
        $this->jftohld = $this->BusinessSetting['jftohld'];
        $this->hldtojf = $this->BusinessSetting['hldtojf'];
        $this->charge = $this->BusinessSetting['charge'];
        $this->charge1 = $this->BusinessSetting['charge1'];
        $this->charge2 = $this->BusinessSetting['charge2'];


        $this->is_hldtoyhq = $this->BusinessSetting['is_hldtoyhq'];
        $this->is_jftohld = $this->BusinessSetting['is_jftohld'];
        $this->is_hldtojf = $this->BusinessSetting['is_hldtojf'];
        $this->is_yhqtohld = $this->BusinessSetting['is_yhqtohld'];

        $this->charge3 = $this->BusinessSetting['charge3'];
        $this->charge5 = $this->BusinessSetting['charge5'];
        $this->chargeNum = $this->BusinessSetting['chargeNum'];
        $this->chargeNum1 = $this->BusinessSetting['chargeNum1'];
        $this->chargeNum2 = $this->BusinessSetting['chargeNum2'];
        $this->chargeNum3 = $this->BusinessSetting['chargeNum3'];

        $this->is_hongbao_gl = $this->BusinessSetting['is_hongbao_gl'];
        $this->is_hongbao_num = $this->BusinessSetting['is_hongbao_num'];



        $open_time = json_decode($this->open_time, true);
        $this->time = intval(date('H'));

        if (!in_array($this->time, $open_time)) {
            if($this->is_hg){
                return false;//如果是货柜来源不需要验证
            }
            return '集市未到开放时间';
        }

        return false;
    }


    public function getBusinessSettingAll()
    {
        $this->BusinessSetting = BusinessSetting::findOne(['store_id' => $this->store_id]);
        $this->open_time = $this->BusinessSetting['open_time'];
        $this->hldtoyhq = $this->BusinessSetting['hldtoyhq'];
        $this->xtjl = $this->BusinessSetting['xtjl'];
        $this->xtjlsell = $this->BusinessSetting['xtjlsell'];
        $this->jftohld = $this->BusinessSetting['jftohld'];
        $this->hldtojf = $this->BusinessSetting['hldtojf'];
        $this->charge = $this->BusinessSetting['charge'];
        $this->charge1 = $this->BusinessSetting['charge1'];
        $this->charge2 = $this->BusinessSetting['charge2'];
        $this->is_hldtoyhq = $this->BusinessSetting['is_hldtoyhq'];
        $this->is_jftohld = $this->BusinessSetting['is_jftohld'];
        $this->is_hldtojf = $this->BusinessSetting['is_hldtojf'];
        $this->is_yhqtohld = $this->BusinessSetting['is_yhqtohld'];
        $open_time = json_decode($this->open_time, true);
        $this->time = intval(date('H'));


        $this->charge3 = $this->BusinessSetting['charge3'];
        $this->charge5 = $this->BusinessSetting['charge5'];
        $this->chargeNum = $this->BusinessSetting['chargeNum'];
        $this->chargeNum1 = $this->BusinessSetting['chargeNum1'];
        $this->chargeNum2 = $this->BusinessSetting['chargeNum2'];
        $this->chargeNum3 = $this->BusinessSetting['chargeNum3'];

        $this->is_hongbao_gl = $this->BusinessSetting['is_hongbao_gl'];
        $this->is_hongbao_num = $this->BusinessSetting['is_hongbao_num'];



        $rechangeType = (int)\Yii::$app->request->post('rechangeType');

        if ($rechangeType == 0) {//is_yhqtohld     卖 sell

            if (!in_array($this->time, $open_time)) {
                return json_encode([
                    'code' => 1,
                    'msg' => '集市未到开放时间',
                ], JSON_UNESCAPED_UNICODE);
            } elseif (!$this->is_yhqtohld) {
                return json_encode([
                    'code' => 1,
                    'msg' => '暂未开放',
                ], JSON_UNESCAPED_UNICODE);
            }

        } elseif ($rechangeType == 2) {//is_jftohld


        } elseif ($rechangeType == 2) {//is_jftohld


        } elseif ($rechangeType == 3) {//   is_hldtoyhq   getcard 买


            if (!in_array($this->time, $open_time)) {
                return json_encode([
                    'code' => 1,
                    'msg' => '集市未到开放时间',
                ], JSON_UNESCAPED_UNICODE);
            } elseif (!$this->is_hldtoyhq) {
                return json_encode([
                    'code' => 1,
                    'msg' => '暂未开放',
                ], JSON_UNESCAPED_UNICODE);
            }


        } elseif ($rechangeType == 4) {//is_hldtojf


        } elseif ($rechangeType == 5) {//is_yhqtohld


        } else {


        }


        return json_encode([
            'code' => 0,
            'data' => array(
                'open_time' => $this->open_time,
                'hldtoyhq' => $this->open_time,
                'xtjl' => $this->open_time,
                'xtjlsell' => $this->open_time,
                'jftohld' => $this->open_time,
                'hldtojf' => $this->open_time,
                'charge' => $this->open_time,
                'charge1' => $this->open_time,
                'is_hldtoyhq' => $this->open_time,
                'is_jftohld' => $this->open_time,
                'is_hldtojf' => $this->open_time,
                'is_yhqtohld' => $this->open_time,
                'is_opentime' => in_array($this->time, $open_time),
            )
        ], JSON_UNESCAPED_UNICODE);
    }


    /*
     *
     * 卖优惠券 预计欢乐豆
     *
     * */
    public function PreJfToHld()
    {
        $check = $this->getBusinessSetting();
        if ($check) {
            return json_encode([
                'code' => 1,
                'msg' => $check
            ], JSON_UNESCAPED_UNICODE);
        }

        if (!$this->is_yhqtohld) {
            return json_encode([
                'code' => 1,
                'msg' => '暂未开放'
            ], JSON_UNESCAPED_UNICODE);
        }


        $num = (int)\Yii::$app->request->post('num');

        if (empty($num)) {
            return json_encode([
                'code' => 1,
                'msg' => '数量不正确'
            ], JSON_UNESCAPED_UNICODE);
        }


//      成交金额内扣除手续费（冻结手续费 减少可用欢乐豆）
//      发布时候扣除卡券数量

//      卖的张数

//      欢乐豆实际总价值
        $huanledou =  (int)intval($this->hldtoyhq * $num);//卖的张数*平台固定的每张欢乐豆价值

//      手续费欢乐豆价值
        $huanledou_charge = (int)intval( ($this->getCharge($num)) * 0.01 * ($this->hldtoyhq * $num));//卖的张数*平台固定的欢乐豆

//      系统奖励
        $xtjl = $this->xtjl;//系统奖励

//      合计收益
        $huanledou_total =  (int)intval($huanledou -$huanledou_charge) ;// 需要的欢乐豆 + 总的*手续费

        return [
            'code' => 0,
            'data' => array(
                'num' => (int)$num,//合计收益
                'huanledou' => (int)$huanledou,//合计收益
                'huanledou_charge' => (int)$huanledou_charge,//合计收益
                'xtjl' => 0,//合计收益
                'huanledou_total' => (int)$huanledou_total,//合计收益
            ),
            'msg' => '计算中...',
        ];

    }

    public function getCharge($num)
    {
        $charge = 0;

        if ($num <= $this->chargeNum && $num >= 0) {
//            $this->charge = 100 / 7;
            $charge = $this->charge;  //1张
        } elseif ($num <= $this->chargeNum1 && $num > $this->chargeNum) {
//            $this->charge = 100 / 7;
            $charge = $this->charge1; //1-6
        } elseif ($num <= $this->chargeNum2 && $num > $this->chargeNum1) {
            $charge = $this->charge2;//7-18
//            $this->charge = 3;
        } elseif ($num <= $this->chargeNum3 && $num > $this->chargeNum2) {
//            $this->charge = 1;
            $charge = $this->charge3; //18以上
        } else {
            $charge = $this->charge5;  //1张
        }

        return $charge;
    }

    public function save()
    {
        if (!$this->validate())
            return $this->getModelError();
        $order = Business::findOne([
            'id' => $this->order_id,
            'store_id' => $this->store_id,
            'user_id' => $this->user_id,
            'is_delete' => 0,
        ]);
        if (!$order)
            return [
                'code' => 1,
                'msg' => '订单不存在或已删除',
            ];
        $goods_list = $this->goods_list;
        if (!$goods_list)
            return [
                'code' => 1,
                'msg' => '信息不能为空',
            ];
        $t = \Yii::$app->db->beginTransaction();

        $order_comment = new BusinessComment();
        $order_comment->store_id = $this->store_id;
        $order_comment->user_id = $this->user_id;
        $order_comment->order_id = $this->order_id;
        $order_comment->content = Html::encode($this->goods_list);
        //$order_comment->content = mb_convert_encoding($order_comment->content, 'UTF-8');
        $order_comment->content = preg_replace('/[\xf0-\xf7].{3}/', '', $order_comment->content);
        $order_comment->addtime = time();
        if (!$order_comment->save()) {
            $t->rollBack();
            return $this->getModelError($order_comment);
        }
        //被评论了  //被交易了
        $order->is_comment = 1;
        $order->user_id_buyer = $this->user_id;
        if ($order->save()) {
            $t->commit();
            return [
                'code' => 0,
                'msg' => '提交成功',
            ];
        } else {
            $t->rollBack();
            return $this->getModelError($order);
        }

    }


    public function JfToHld()
    {
        $this->getBusinessSetting();
        if (!$this->is_jftohld) {
            return json_encode([
                'code' => 1,
                'msg' => '暂未开放'
            ], JSON_UNESCAPED_UNICODE);
        }


        $integral = (int)\Yii::$app->request->post('integral');
        $hld = (int)\Yii::$app->request->post('hld');
        $rechangeType = \Yii::$app->request->post('rechangeType', 2);
        $user = User::findOne(['id' => $this->user_id, 'store_id' => $this->store_id]);
        if (!$user) {
            return json_encode([
                'code' => 1,
                'msg' => '用户不存在，或已删除'
            ], JSON_UNESCAPED_UNICODE);
        }
        if (empty($integral) && empty($hld)) {
            return json_encode([
                'code' => 1,
                'msg' => '数量不正确'
            ], JSON_UNESCAPED_UNICODE);
        }


        if ($rechangeType == '2') {
            //扣除积分

            if ($integral > $user->integral) {
                return json_encode([
                    'code' => 1,
                    'msg' => '积分不足'
                ], JSON_UNESCAPED_UNICODE);
            } elseif ($integral < 1) {
                return json_encode([
                    'code' => 1,
                    'msg' => '不能小于1'
                ], JSON_UNESCAPED_UNICODE);
            }

            $user->integral -= $integral;
            //增加欢乐豆
            $hldJf = $integral * $this->jftohld;
            $user->hld += $hldJf;
            $user->total_hld += $hldJf;


        } elseif ($rechangeType == '1') {
            //充值积分 扣除欢乐豆

            if (!$this->is_jftohld) {
                return json_encode([
                    'code' => 1,
                    'msg' => '暂不支持'
                ], JSON_UNESCAPED_UNICODE);
            }

            $hldJf = $hld / $this->jftohld;

            if ($hld > $user->hld) {
                return json_encode([
                    'code' => 1,
                    'msg' => '欢乐豆不足'
                ], JSON_UNESCAPED_UNICODE);
            } elseif (!is_int($hldJf)) {
                return json_encode([
                    'code' => 1,
                    'msg' => '请输入' . $this->jftohld . '的倍数'
                ], JSON_UNESCAPED_UNICODE);
            } elseif ($hldJf < 1) {
                return json_encode([
                    'code' => 1,
                    'msg' => '不能小于10'
                ], JSON_UNESCAPED_UNICODE);
            }

            $user->integral += $hldJf;
            $user->total_integral += $hldJf;

            //增加欢乐豆
            $user->hld -= $hld;
            $user->total_hld -= $hld;

            $integral = $hldJf;
        }


        $integralLog = new IntegralLog();
        $integralLog->user_id = $user->id;
        if ($rechangeType == '2') {
            $integralLog->content = "管理员（积分兑换欢乐豆） 后台操作账号：" . $user->nickname . " 积分扣除：" . $integral . " 积分" . " 欢乐豆充值：" . $integral * $this->jftohld . " 个";
        } elseif ($rechangeType == '1') {
            $integralLog->content = "管理员（欢乐豆兑换积分） 后台操作账号：" . $user->nickname . " 积分充值：" . $integral . " 积分" . " 欢乐豆扣除：" . $integral * $this->jftohld . " 个";
        }

        $integralLog->integral = $integral;
        $integralLog->addtime = time();
        $integralLog->username = $user->nickname;
        $integralLog->operator = 'admin';
        $integralLog->store_id = $this->store_id;
        $integralLog->operator_id = 0;

        $t = \Yii::$app->db->beginTransaction();

        if ($user->save() && $integralLog->save()) {
            $t->commit();
            $user = User::findOne(['id' => $this->user_id, 'store_id' => $this->store_id]);
            return [
                'code' => 0,
                'msg' => '交易成功',
                'data' => array(
                    'is_exchange' => 1,
                    'user_info' => array(
                        'hld' => $user->hld,
                        'integral' => $user->integral,
                        'coupon' => $user->coupon
                    )
                )
            ];
        } else {
            $t->rollBack();
            return $this->getModelError($user);
        }
    }

}