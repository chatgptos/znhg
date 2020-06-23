<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/15
 * Time: 17:23
 */

namespace app\modules\api\models;


use app\models\Goods;
use app\models\Order;
use app\models\OrderDetail;
use app\models\User;
use yii\helpers\Html;

class OrderTopicCommentPreview extends Model
{
    public $store_id;
    public $user_id;
    public $order_id;

    public function rules()
    {
        return [
            [['order_id'], 'required'],
        ];
    }

    public function search()
    {
        if (!$this->validate())
            return $this->getModelError();
        $order = Order::findOne([
            'is_delete' => 0,
            'store_id' => $this->store_id,
            'user_id' => $this->user_id,
            'id' => $this->order_id,
        ]);

        $user = User::find()->where(['store_id' => $this->store_id,'id'=>$this->user_id])->one();

        $form = new TopicListForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store_id;
        $form->user_id = $this->user_id;

//        var_dump($form->search_user_id());

        $list=$form->search_user_id()['data']['list'];

        $goods_list=[];
        foreach ($list as $key=>$value){
            $goods_list[$key]['order_detail_id']=$value['id'];
            $goods_list[$key]['goods_id']=$value['id'];
            $goods_list[$key]['goods_pic']=$value['cover_pic'];
            $goods_list[$key]['content']=$value['content'];
            $goods_list[$key]['addtime'] = date('Y-m-d', $value['addtime']);
            $goods_list[$key]['pic_list'] = json_decode($value['pic_list']);
        }


        array_unshift($goods_list, [
            'order_detail_id'=>0,
            'goods_id'=>0,
            'goods_pic'=>$user->avatar_url,
            'content'=>'',
            'pic_list'=>[],
        ]);

        $goods_list=[
            'order_detail_id'=>0,
            'goods_id'=>0,
            'goods_pic'=>0,
            'content'=>'',
            'pic_list'=>[],
        ];

        return [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'order_id' => 0,
                'goods_list' => $goods_list,
            ],
        ];
    }

}