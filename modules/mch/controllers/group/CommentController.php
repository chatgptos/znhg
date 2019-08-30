<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/17
 * Time: 16:53
 */

namespace app\modules\mch\controllers\group;


use app\models\PtGoods;
use app\models\PtOrderComment;
use app\models\User;
use yii\data\Pagination;

class CommentController extends Controller
{
    public function actionIndex()
    {
        $query = PtOrderComment::find()->alias('oc')->where(['oc.store_id' => $this->store->id, 'oc.is_delete' => 0]);
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => 20]);
        $list = $query
            ->leftJoin(['u' => User::tableName()], 'oc.user_id=u.id')
            ->leftJoin(['g' => PtGoods::tableName()], 'oc.goods_id=g.id')
            ->select('oc.id,u.nickname,u.avatar_url,oc.score,oc.content,oc.pic_list,g.name goods_name,oc.is_hide')
            ->orderBy('oc.addtime DESC')->limit($pagination->limit)->offset($pagination->offset)->asArray()->all();
        return $this->render('index', [
            'list' => $list,
            'pagination' => $pagination,
        ]);
    }

    public function actionHideStatus($id, $status)
    {
        $order_comment = PtOrderComment::findOne([
            'store_id' => $this->store->id,
            'id' => $id,
        ]);
        if ($order_comment) {
            $order_comment->is_hide = $status;
            $order_comment->save();
        }
        return $this->renderJson([
            'code' => 0,
            'msg' => '操作成功',
        ]);
    }

    public function actionDeleteStatus($id, $status)
    {
        $order_comment = PtOrderComment::findOne([
            'store_id' => $this->store->id,
            'id' => $id,
        ]);
        if ($order_comment) {
            $order_comment->is_delete = $status;
            $order_comment->save();
        }
        return $this->renderJson([
            'code' => 0,
            'msg' => '操作成功',
        ]);
    }
}