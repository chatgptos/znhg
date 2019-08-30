<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/9/14
 * Time: 14:53
 */

namespace app\modules\api\models;


use app\extensions\getInfo;
use app\models\Video;
use yii\data\Pagination;

class VideoForm extends Model
{

    public $store_id;
    public $page;
    public $limit;
    public function rules()
    {
        return [
            [['page', 'limit',], 'integer'],
            [['page',], 'default', 'value' => 1],
            [['limit',], 'default', 'value' => 10],
        ];
    }
    public function getList()
    {
        if(!$this->validate()){
            return $this->getModelError();
        }
        $query = Video::find()->where(['is_delete'=>0,'store_id'=>$this->store_id]);
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => $this->limit, 'page' => $this->page - 1]);
        $list = $query->limit($pagination->limit)
            ->offset($pagination->offset)
            ->orderBy(['(sort+0)'=>SORT_ASC,'addtime'=>SORT_DESC])
            ->asArray()->all();
        foreach($list as $index=>$value){
            $list[$index]['time'] = date('m月d日',$value['addtime']);
            $list[$index]['show'] = -1;
            if($value['type'] == 1){
                $res = getInfo::getVideoInfo($value['url']);
                $list[$index]['url'] = $res['url'];
            }
        }
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'row_count' => $count,
                'page_count' => $pagination->pageCount,
                'list' => $list,
            ],
        ];
    }
}