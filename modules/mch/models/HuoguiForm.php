<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/9/22
 * Time: 17:06
 */

namespace app\modules\mch\models;


use app\extensions\HuoGui;
use app\models\Order;
use app\models\Shop;
use app\models\ShopPic;
use app\models\Store;
use app\models\UserCard;
use yii\data\Pagination;

/**
 * @property \app\models\Shop $shop
 */
class HuoguiForm extends Model
{
    public $store_id;
    public $shop;
    public $limit;

    public $name;
    public $mobile;
    public $address;
    public $longitude;
    public $latitude;
    public $score;
    public $cover_url;
    public $pic_url;
    public $content;
    public $shop_time;
    public $shop_pic;
    public $hg_id;
    public $hg_yx;

    public function rules()
    {
        return [
            [['name', 'mobile', 'address','latitude','longitude'], 'required'],
            [['name', 'mobile', 'address','latitude','longitude','cover_url','pic_url','content','shop_time'], 'string'],
            [['hg_yx','hg_id','name', 'mobile', 'address','cover_url','pic_url','content','shop_time'], 'trim'],
            [['score'],'integer','min'=>1,'max'=>5],
            [['shop_pic'],'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'name'=>'智能鲜蜂服务点名称',
            'mobile'=>'联系方式',
            'address'=>'智能鲜蜂服务点地址',
            'latitude'=>'经纬度',
            'longitude'=>'经纬度',
            'score'=>'评分',
            'hg_id'=>'hg',
            'hg_yx'=>'hgyx',
            'cover_url'=>'智能鲜蜂服务点大图',
            'pic_url'=>'门店小图',
            'content'=>'门店介绍',
            'shop_time'=>'营业时间',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getModelError();
        }
        $shop = $this->shop;
        if($shop->isNewRecord){
            $shop->is_delete = 0;
            $shop->addtime = time();
            $shop->store_id = $this->store_id;
        }
        $shop->attributes = $this->attributes;
        if(is_array($this->shop_pic)){
            $shop->cover_url = $this->shop_pic[0];
        }
        if ($shop->save()) {
            ShopPic::updateAll(['is_delete' => 1], ['shop_id' => $shop->id]);
            foreach($this->shop_pic as $pic_url){
                $shop_pic = new ShopPic();
                $shop_pic->shop_id = $shop->id;
                $shop_pic->pic_url = $pic_url;
                $shop_pic->store_id = $shop->store_id;
                $shop_pic->is_delete = 0;
                $shop_pic->save();
            }
            return [
                'code' => 0,
                'msg' => '成功'
            ];
        } else {
            return [
                'code' => 1,
                'msg' => '网络异常'
            ];
        }
    }


    public function getList()
    {
        $HuoGui = new HuoGui();



        $res= $HuoGui->getDeviceList();
        $list=$res['data'];

        foreach ($list as $index => $value) {
                $biz_content=array(
                    "deviceId"=>$value['id'],//必须要有设备
                );
                $res= $HuoGui->getDeviceGoods($biz_content);
                $list[$index]['good_list']=$res['data'];
        }


        $count = count($list);
        $p = new Pagination(['totalCount' => $count, 'pageSize' => $this->limit]);
        return [
            'row_count' => $count,
            'pagination' => $p,
            'list' => $list
        ];
    }
}