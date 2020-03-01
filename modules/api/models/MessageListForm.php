<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/11
 * Time: 11:45
 */

namespace app\modules\api\models;


use app\models\Cash;
use app\models\IntegralLog;
use app\models\Message;
use yii\data\Pagination;

class MessageListForm extends Model
{
    public $store_id;
    public $user_id;
    public $status;
    public $page;
    public $limit;

    public function rules()
    {
        return [
            [['page', 'limit', 'status',], 'integer'],
            [['page',], 'default', 'value' => 1],
            [['limit',], 'default', 'value' => 10],
        ];
    }
    public function getList()
    {
        if (!$this->validate())
            return $this->getModelError();
        $query = Message::find()->where([
            'store_id'=>$this->store_id,
            'user_id'=>$this->user_id
        ]);
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'page' => $this->page - 1, 'pageSize' => $this->limit]);
        $list = $query->limit($pagination->limit)->offset($pagination->offset)->orderBy('addtime DESC')->all();
        $new_list = [];
        /* @var Cash[] $list */
        foreach($list as $index=>$value){
            $new_list[] = (object)[
                'price'=>'系统提醒',
                'addtime'=>date('m-d H:i',$value->addtime),
                'status'=>$value->content
            ];
        }
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'row_count' => $count,//总数
                'page_count' => $pagination->pageCount,//总页数
                'list' => $new_list,
            ],
        ];

    }

}