<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/17
 * Time: 15:07
 */

namespace app\modules\api\models;


use app\models\Business;
use app\models\BusinessComment;
use app\models\OrderComment;
use app\models\User;
use yii\data\Pagination;

class BusinessCommentListForm extends Model
{
    public $order_id;
    public $score;
    public $page = 1;
    public $limit = 20;

    public function rules()
    {
        return [
            [['order_id'], 'required'],
            [['page'], 'integer'],
        ];
    }

    public function search()
    {
        if (!$this->validate())
            return $this->getModelError();
        $query = BusinessComment::find()->alias('oc')->leftJoin(['u' => User::tableName()], 'oc.user_id=u.id')
            ->where(['oc.order_id' => $this->order_id, 'oc.is_delete' => 0, 'oc.is_hide' => 0]);
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => $this->limit, 'page' => $this->page - 1]);
        $list = $query->limit($pagination->limit)->offset($pagination->offset)->orderBy('oc.addtime DESC')->asArray()
            ->select('u.nickname,u.avatar_url,oc.score,oc.content,oc.pic_list,oc.addtime')->all();

        foreach ($list as $i => $item) {
            $list[$i]['addtime'] = date('Y-m-d', $item['addtime']);
            $list[$i]['pic_list'] = json_decode($item['pic_list']);
        }
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'row_count' => $count,
                'page_count' => $pagination->pageCount,
                'list' => $list,
                'comment_count' => $this->countData(),
            ],
        ];
    }

    public function countData()
    {
        if (!$this->validate())
            return $this->getModelError();
        $score_all = BusinessComment::find()->alias('oc')
            ->where(['oc.order_id' => $this->order_id, 'oc.is_delete' => 0, 'oc.is_hide' => 0,])->count();
        $score_3 = BusinessComment::find()->alias('oc')
            ->where(['oc.order_id' => $this->order_id, 'oc.is_delete' => 0, 'oc.is_hide' => 0, 'oc.score' => 3])->count();
        $score_2 = BusinessComment::find()->alias('oc')
            ->where(['oc.order_id' => $this->order_id, 'oc.is_delete' => 0, 'oc.is_hide' => 0, 'oc.score' => 2])->count();
        $score_1 = BusinessComment::find()->alias('oc')
            ->where(['oc.order_id' => $this->order_id, 'oc.is_delete' => 0, 'oc.is_hide' => 0, 'oc.score' => 1])->count();
        return (object)[
            'score_all' => $score_all ? $score_all : 0,
            'score_3' => $score_3 ? $score_3 : 0,
            'score_2' => $score_2 ? $score_2 : 0,
            'score_1' => $score_1 ? $score_1 : 0,
        ];
    }
}