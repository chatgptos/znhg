<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/27
 * Time: 15:25
 */

namespace app\modules\mch\models;
use app\models\Room;
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




    public function rules()
    {
        return [
            [['name','pic_url','content','room_id','live_status'],'trim'],
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


    public function transfer()
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
                'start' => "0",
                'limit' => '100',
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
}