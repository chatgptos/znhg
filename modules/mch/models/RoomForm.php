<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/27
 * Time: 15:25
 */

namespace app\modules\mch\models;
use app\models\Room;
use app\modules\api\models\crowdapply\Order;
use Curl\Curl;

/**
 * @property \app\models\Room $Room;
 */
class RoomForm extends Model
{
    public $store_id;
    public $Room;

    public $name;
    public $pic_url;
    public $content;
    public $room_id;
    public $live_status;
    public $goods;
    public $anchorName;
    public $anchorWechat;
    public $coverImg;
    public $shareImg;
    public $coverImgurl;
    public $shareImgurl;
    public $apply_form_id;
    public $ids;
    public $user_id;










    public function rules()
    {
        return [
            [['user_id','apply_form_id','name','pic_url','content','room_id','live_status'],'trim'],
            [['name','pic_url','content','goods'],'string'],
            [['name','pic_url','content'],'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name'=>'直播间名称',
            'pic_url'=>'直播间图片',
            'content'=>'直播间描述',
            'room_id'=>'room_id',
            'live_status'=>'直播状态',
            'goods'=>'直播商品',
            'apply_form_id'=>'预约单据id',
            'user_id'=>'user_id',
        ];
    }

    public function save()
    {
        if(!$this->validate()){
            $this->getModelError();
        }

        if($this->Room->isNewRecord){
            $this->Room->is_delete = 0;
            $this->Room->store_id = $this->store_id;
            $this->Room->addtime = time();
        }

        $this->Room->name = $this->name;
        $this->Room->room_id = $this->room_id;
        $this->Room->pic_url = $this->pic_url;
        $this->Room->content = $this->content;
        $this->Room->live_status = $this->live_status;
        $this->Room->goods = $this->goods;
        if($this->Room->save()){
            return [
                'code'=>0,
                'msg'=>'成功'
            ];
        }else{
            return $this->getModelError($this->Room);
        }
    }


    public function transfer($id=0)
    {
        $room_info =\Yii::$app->cache->get('room_info');
        if(!$room_info){
            $wechat = $this->getWechat();
            $access_token = $wechat->getAccessToken();
            if (!$access_token) {
                return [
                    'code' => 1,
                    'msg' => $wechat->errMsg,
                ];
            }
            $api = "http://api.weixin.qq.com/wxa/business/getliveinfo?access_token={$access_token}";
            $curl = new Curl();
            $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
            $data = json_encode([
                'start' =>$id,
                'limit' => $id+90,
            ]);
//        $data = json_encode([
//           "action"=> "get_replay", // 获取回放
//            "room_id"=>2, // 直播间   id
//            "start"=> 0, // 起始拉取视频，start =   0   表示从第    1   个视频片段开始拉取
//            "limit"=> 100 // 每次拉取的个数上限，不要设置过大
//        ]);
            $curl->post($api, $data);
            $res = json_decode($curl->response, true);
            \Yii::$app->cache->set('room_info' , $res['room_info'],60*60);
        }
        $this->store_id = $this->store_id;
        foreach ($room_info as $key=>$value){
            $this->name = $value['name'];
            $this->room_id = $value['roomid'];
            $this->pic_url =$value['cover_img'];
            $this->content = $value['anchor_name'];
            $this->content = $value['anchor_name'];
            $this->live_status = $value['live_status'];
            $this->goods = json_encode($value['goods']);
            $is_room = Room::findOne(['room_id'=>$this->room_id,'is_delete'=>0]);
            $model = new Room();
            $this->Room = $model;
            if($is_room){
                $this->Room->isNewRecord=false;//更新
                $res=Room::updateAll(['name'=>$value['name'],'goods'=>$this->goods,'live_status'=>$value['live_status'],'pic_url'=>$value['cover_img'],'content'=>$value['anchor_name']], ['room_id'=>$is_room->room_id]);
                return [
                    'code'=>0,
                    'msg'=>'成功'
                ];
            }else{
                $this->save();
            }
        }

    }




    public function addRoom()
    {
        $wechat = $this->getWechat();
        $access_token = $wechat->getAccessToken();
        if (!$access_token) {
            return [
                'code' => 1,
                'msg' => $wechat->errMsg,
            ];
        }
        if(!$this->coverImg){
            //储存文件
            $url=$_SERVER['DOCUMENT_ROOT'].'/uploads/image/0a/'.'0aef19c6dbff3333f657bf1c3b1f4708.jpg';
            $api = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$access_token}&type=image";;
            $curl = new Curl();
            $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
            $curl->setOpt ( CURLOPT_SAFE_UPLOAD, false);

            $data = ['media' => new \CURLFile($url) ];

            $ch  = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER , true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST , false);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $res  = curl_exec($ch);
            $data = json_decode($res,true);
            $this->coverImg =$data['media_id'];
            $this->shareImg =$data['media_id'];
            $this->coverImgurl ='https://xcx.aijiehun.com/uploads/image/0a/0aef19c6dbff3333f657bf1c3b1f4708.jpg';
            $this->shareImgurl ='https://xcx.aijiehun.com/uploads/image/0a/0aef19c6dbff3333f657bf1c3b1f4708.jpg';
        }

        $api = "https://api.weixin.qq.com/wxaapi/broadcast/room/create?access_token={$access_token}";
        $curl = new Curl();
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        $curl->setHeader('Content-Type', 'application/json');
        $data = json_encode([
            'name' => $this->anchorName.'的直播间',// 房间名字
            'startTime' =>  time()+3600,// 开始时间
            'endTime' =>   time()+3600*12,// 开始时间
            'anchorName' => $this->anchorName,// 主播昵称
//            'anchorWechat' => $this->anchorWechat,// 主播微信号
            'anchorWechat' => $this->anchorWechat,// 主播微信号  Lvcj1997 xiaochijiekafei
            'coverImg' => $this->coverImg,// 通过 uploadfile 上传，填写 mediaID
            'shareImg' => $this->shareImg, //通过 uploadfile 上传，填写 mediaID
            'type' => '0', // 直播类型，1 推流 0 手机直播
            'screenType' => '0', // 1：横屏 0：竖屏
            'closeLike' => '0',// 是否 关闭点赞 1 关闭
            'closeGoods' => '0',// 是否 关闭商品货架，1：关闭
            'closeComment' => '0',// 是否开启评论，1：关闭
        ]);
        $curl->post($api, $data);
        $res = json_decode($curl->response, true);
        if($res['errcode'] ==0){
            $is_room = Room::findOne(['room_id'=>$res['roomId'],'is_delete'=>0]);
            if($is_room){
                $this->Room->isNewRecord=false;//更新
                Room::updateAll([
                    'name'=>$this->anchorName.'的直播间',
                    'pic_url'=>$this->coverImg,'content'=>$this->anchorName.'的直播间'],
                    ['room_id'=>$res['roomId']]);
            }else{
                $Room = new Room();
                $Room->name = $this->anchorName.'的直播间';
                $Room->room_id = $res['roomId'];
                $Room->store_id = 1;
                $Room->is_delete = 0;
                $Room->addtime = time();
                $Room->goods = [];
                $Room->pic_url =$this->coverImgurl;
                $Room->content = $this->anchorName.'的直播间';
                $Room->user_id = $this->user_id;
                $Room->save();
            }
            $resorder=Order::findOne([ 'id'=>$this->apply_form_id]);
            if($resorder){
                Order::updateAll(['room_id' => $res['roomId']], ['id' => $this->apply_form_id]);
            }

            return [
                'code'=>0,
                'data'=>$res,
                'msg'=>'成功'
            ];
        } else{
            return $res;
        }

    }




    public function addgoods()
    {
        $wechat = $this->getWechat();
        $access_token = $wechat->getAccessToken();
        if (!$access_token) {
            return [
                'code' => 1,
                'msg' => $wechat->errMsg,
            ];
        }

        $is_room = Room::find()->select('*')->where(['is_delete'=>0,'user_id'=>$this->user_id])->orderBy('id DESC')->limit(1)->one();
        if($is_room){
            $api = "https://api.weixin.qq.com/wxaapi/broadcast/room/addgoods?access_token={$access_token}";
            $curl = new Curl();
            $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
            $curl->setHeader('Content-Type', 'application/json');
            $data = json_encode([
                'ids' => $this->ids,// 房间名字
                'roomId' => $is_room['room_id'],// 房间名字
            ]);
            $curl->post($api, $data);
            $res = json_decode($curl->response, true);
            if($res['errcode'] ==0){
                $res1=Room::updateAll(['goods' => json_encode($this->ids)],
                    ['room_id' => $is_room['room_id']]);
                return [
                    'code'=>0,
                    'data'=>$res,
                    'msg'=>'成功'
                ];
            } else{
                return $res;
            }
        }else{
            return [
                'code'=>2,
                'msg'=>'还没有直播间请开播'
            ];
        }
    }



    public function getgoods($offset=0,$limit=100,$status=2)
    {
        $wechat = $this->getWechat();
        $access_token = $wechat->getAccessToken();
        if (!$access_token) {
            return [
                'code' => 1,
                'msg' => $wechat->errMsg,
            ];
        }
        $api = "https://api.weixin.qq.com/wxaapi/broadcast/goods/getapproved?access_token={$access_token}&status={$status}&offset={$offset}&limit={$limit}";


        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER , true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST , false);
        $res  = curl_exec($ch);
        $data = json_decode($res,true);
        if($data['goods']){
            foreach ($data['goods']  as $key => $item) {
                $id=$this->getQuerystr($item['url'],'id');
                $data['goods'][$key]['id']=$id;
            }
        }
        if($res['errcode'] ==0){
            return [
                'code'=>0,
                'data'=>$data,
                'msg'=>'成功'
            ];
        } else{
            return $res;
        }
    }
}