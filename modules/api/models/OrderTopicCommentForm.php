<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/16
 * Time: 10:11
 */

namespace app\modules\api\models;


use app\models\Order;
use app\models\OrderComment;
use app\models\OrderDetail;
use app\models\Topic;
use app\models\User;
use app\modules\api\models\crowdapply\OrderPreviewFrom;
use app\modules\mch\models\RoomForm;
use yii\helpers\Html;

class OrderTopicCommentForm extends Model
{
    public $store_id;
    public $user_id;
    public $order_id;
    public $goods_list;
    public $user;
    public $cart_id_list;

    public function rules()
    {
        return [
            [['goods_list', 'order_id'], 'required'],
            [['cart_id_list'], 'string'],
        ];
    }

    public function save()
    {
        if (!$this->validate())
            return $this->getModelError();
//        $order = Order::findOne([
//            'id' => $this->order_id,
//            'store_id' => $this->store_id,
//            'user_id' => $this->user_id,
//            'is_delete' => 0,
//        ]);
//        if (!$order)
//            return [
//                'code' => 1,
//                'msg' => '订单不存在或已删除',
//            ];
        $goods_list = json_decode($this->goods_list);
        if (!$goods_list)
            return [
                'code' => 1,
                'msg' => '日记信息不能为空',
            ];
        $t = \Yii::$app->db->beginTransaction();
        foreach ($goods_list as $goods) {
            if($goods->order_detail_id==0){
                $order_comment = new Topic();
                $order_comment->store_id = $this->store_id;
                $order_comment->layout = 0;
                $order_comment->sort = 0;
                $order_comment->id = $goods->order_detail_id;
                $order_comment->title = mb_substr(Html::encode($goods->content),0,10);
                $order_comment->content = Html::encode($goods->content);
                //$order_comment->content = mb_convert_encoding($order_comment->content, 'UTF-8');
                $order_comment->content = preg_replace('/[\xf0-\xf7].{3}/', '', $order_comment->content);

                $order_comment->user_id = $this->user_id;  $pic_list = [];
                foreach ($goods->uploaded_pic_list as $pic) {
                    $pic_list[] = Html::encode($pic);
                }
                if($goods->uploaded_pic_list){
                    $order_comment->cover_pic = $goods->uploaded_pic_list[0];
                }else{
                    $order_comment->cover_pic = 'http://airent-hospital.oss-cn-beijing.aliyuncs.com/uploads/image/77/773717c17a32c513f2732a54be676a2b.png';
                }

                $order_comment->content = $order_comment->content . "<img src='{$order_comment->cover_pic}'/>";
                $pic_list = json_encode($pic_list, JSON_UNESCAPED_UNICODE);
                $order_comment->pic_list = $pic_list;
                $order_comment->addtime = time();
                if(!$order_comment->content && !$order_comment->cover_pic){
                    return [
                        'code' => 1,
                        'msg' => '直播日记需要你填写',
                    ];
                }
                $coupon=1;//赠送券
                //新人增加
                 User::updateAll(
                    ['coupon'=>\Yii::$app->user->identity->coupon+$coupon],
                    ['id' => \Yii::$app->user->identity->id]
                );
                //增加一张券
                $res=$order_comment->save();

                $form = new BusinessCommentForm();
                $form->user_id = $this->user_id;
                $form->store_id = 1;
                $form->num = 1;
                $form->title = $order_comment->title;
                $form->room_id = 0;//是智能鲜蜂服务点 智能鲜蜂服务点表象
                $form->good_id = 0;//是智能鲜蜂服务点 智能鲜蜂服务点表象
                $form->article_id = $order_comment->id;//是智能鲜蜂服务点 智能鲜蜂服务点表象


                $res1 = $form->add();



                if (!$res || !$res1) {
                    $t->rollBack();
                    return $this->getModelError($order_comment);
                }
            }
        }

        $t->commit();
        return [
            'code' => 0,
            'msg' => '提交成功',
        ];

    }



    public function kaibosave()
    {
        if (!$this->validate())
            return $this->getModelError();


        $goods_list = json_decode($this->goods_list);
        if (!$goods_list)
            return [
                'code' => 1,
                'msg' => '日记信息不能为空',
            ];

//        try {
//            // 两个语句都是在主库上执行的
//
//            $t->commit();
//        } catch(\Exception $e) {
//            $t->rollBack();
//            throw $e;
//        } catch(\Throwable $e) {
//            $t->rollBack();
//            throw $e;
//        }


        foreach ($goods_list as $goods) {
            if($goods->order_detail_id==0){
                $t = \Yii::$app->db->beginTransaction();
                //创建直播间
                $form = new RoomForm();
                $form->store_id = $this->store_id;
                $form->anchorName =$this->user->nickname;
                $form->user_id =$this->user->id;
                if($goods->content){
                    $form->anchorWechat =$goods->content;
                }else{
                    return [
                        'code' => 1,
                        'msg' => '请输入微信号，不是微信昵称，主播需要实名',
                    ];
                }
                $form->coverImg =$goods->media_id[0];
                $form->shareImg =$goods->media_id[0];

                $form->coverImgurl =$goods->uploaded_pic_list[0];

                if($goods->media_id[1]){
                    $form->shareImg=$goods->media_id[1];
                    $form->shareImgurl =$goods->uploaded_pic_list[1];
                }
                //创建主播预约单据
                $data='[{"id":"97","store_id":"1","goods_id":"16","name":"微信号","type":"text","required":"1","default":"yang","tip":"您直播的微信号？","sort":null,"is_delete":"0","addtime":"1592773722","option":null},{"id":"98","store_id":"1","goods_id":"16","name":"真实姓名","type":"text","required":"1","default":"张露悦1","tip":"您的真实姓名？根据电商直播法必须实名制通过后可以直播","sort":null,"is_delete":"0","addtime":"1592773722","option":null},{"id":"99","store_id":"1","goods_id":"16","name":"直播开始时间","type":"time","required":"1","default":"10:00","tip":null,"sort":null,"is_delete":"0","addtime":"1592773722","option":null},{"id":"100","store_id":"1","goods_id":"16","name":"直播结束时间","type":"time","required":"1","default":"00:01","tip":null,"sort":null,"is_delete":"0","addtime":"1592773722","option":null},{"id":"101","store_id":"1","goods_id":"16","name":"政策支持","type":"textarea","required":null,"default":"","tip":"申请您需要的政策支持","sort":null,"is_delete":"0","addtime":"1592773722","option":null},{"id":"102","store_id":"1","goods_id":"16","name":"我擅长","type":"textarea","required":null,"default":"","tip":"例如：我擅长转化客户，请给我导入流量","sort":null,"is_delete":"0","addtime":"1592773722","option":null},{"id":"103","store_id":"1","goods_id":"16","name":"我要带的货","type":"textarea","required":null,"default":"","tip":"请备注您本次直播带的货，可以达标可以获得额外奖励，未申请则平台默认推送","sort":null,"is_delete":"0","addtime":"1592773722","option":null},{"id":"104","store_id":"1","goods_id":"16","name":"我要定制抽奖活动","type":"textarea","required":null,"default":"","tip":"请备注您直播时候抽奖活动奖品我们客服将提前为您设置好，预约成功可以享受直播一键抽奖发起活动，未申请默认","sort":null,"is_delete":"0","addtime":"1592773722","option":null}]';
                $data = json_decode($data,true);
                foreach ($data as $key=>$value){
                    if($value['name']=='真实姓名'){
                        $data[$key]['default']=$form->anchorName ;
                    }

                    if($value['name']=='微信号'){
                        $data[$key]['default']=$this->user->nickname;
                    }

                    if($value['name']=='直播开始时间'){
                        $data[$key]['default']= date("H:i",time()+600) ;// 开始时间
//                        var_dump($data[$key]['default']);die;
                    }

                    if($value['name']=='政策支持'){
                        $data[$key]['default']='我是自动通过，主播报名插件推荐功能来的，已经创建成功「api试用期」，直播插件299元/终身, 免维护，免权限，一键前端创建直播间，添加直播商品，分析数据，联系:13236390680';
                    }
                }
                $order_form = new OrderPreviewFrom();
                $order_form->store_id = $this->store_id;
                $order_form->user_id = \Yii::$app->user->id;
                $order_form->goods_id = 15;//传入的是单据的id活动
                $order_form->form_list = $data;
                $order_form->form_id = 'this is a form';
                $res3=$order_form->save();
                if($res3['code']!=0){
                    return [
                        'code' => 1,
                        'msg' => $res3['msg'],
                    ];
                }
                //增加一张券
                $form->apply_form_id =$res3['dataid'];
                $res=$form->addRoom();
                if($res['errcode']==300036){
                    $e=$t->rollBack();
                    return [
                        'code' => 2,
                        'msg' => '请去认证',
                    ];
                }elseif ($res['errcode']!=0){
                    $e=$t->rollBack();
                    return [
                        'code' => 1,
                        'msg' =>$res['errmsg'],
                    ];
                }

                $coupon=1;//赠送券
                //新人增加
                $res2=User::updateAll(
                    ['coupon'=>\Yii::$app->user->identity->coupon+$coupon],
                    ['id' => \Yii::$app->user->identity->id]
                );
                if(!$res2){
                    $e=$t->rollBack();
                    return [
                        'code' => 1,
                        'msg' => '申请失败',
                    ];
                }
                $form = new BusinessCommentForm();
                $form->user_id = $this->user_id;
                $form->store_id = 1;
                $form->num = 1;
                $form->title = $this->user->nickname.'的直播房间';
                $form->room_id = $res['data']['roomId'];//是智能鲜蜂服务点 智能鲜蜂服务点表象

                $form->good_id = 0;//是智能鲜蜂服务点 智能鲜蜂服务点表象
                $form->article_id = 0;//是智能鲜蜂服务点 智能鲜蜂服务点表象

                $res1 = $form->add();
                $res1=json_decode($res1,true);
                if($res1['code']!=0){
                    $e=$t->rollBack();
                    return [
                        'code' => 1,
                        'msg' => $res1['msg'],
                    ];
                }
                $t->commit();
                return [
                    'code' => 0,
                    'data' => array(
                        'roomId'=>$res['data']['roomId'],
                    ),
                    'msg' => '十分钟后开播个人中心-主播端进入主播开播吧 ,点击确定添加直播间商品立刻赚钱',
                ];
            }
        }


    }



    public function addgoods()
    {
//        if (!$this->validate())
//            return $this->getModelError();
        $this->cart_id_list ='['.$this->cart_id_list.']';
        $form = new RoomForm();
        $form->store_id = $this->store_id;
        $form->ids =json_decode($this->cart_id_list);
        $form->user_id = $this->user_id;
        $res=$form->addgoods();
        return $res; 
    }
}