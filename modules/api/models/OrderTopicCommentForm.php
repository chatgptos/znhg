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
use yii\helpers\Html;

class OrderTopicCommentForm extends Model
{
    public $store_id;
    public $user_id;
    public $order_id;
    public $goods_list;

    public function rules()
    {
        return [
            [['goods_list', 'order_id'], 'required'],
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
                $form->room_id = 0;//是购值爽服务点 购值爽服务点表象
                $form->good_id = 0;//是购值爽服务点 购值爽服务点表象
                $form->article_id = $order_comment->id;//是购值爽服务点 购值爽服务点表象


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
}