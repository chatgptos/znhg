<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/9/28
 * Time: 14:11
 */

namespace app\modules\api\models;


use app\models\Topic;
use yii\data\Pagination;

class TopicListForm extends Model
{
    public $store_id;

    public $page;
    public $limit = 20;

    public function rules()
    {
        return [
            [['page'], 'integer'],
            [['page'], 'default', 'value' => 1],
        ];
    }

    public function search()
    {
        $query = Topic::find()->where(['store_id' => $this->store_id, 'is_delete' => 0]);
        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'page' => $this->page - 1, 'pageSize' => $this->limit]);
        $list = $query->orderBy('sort ASC,addtime DESC')->limit($pagination->limit)->offset($pagination->offset)
            ->select('id,title,sub_title,cover_pic,read_count,virtual_read_count,virtual_favorite_count,addtime,layout,content')->asArray()->all();

        foreach ($list as $i => $item) {
            $read_count = intval($item['read_count'] + $item['virtual_read_count']);
            unset($list[$i]['read_count']);
            unset($list[$i]['virtual_read_count']);
            if ($read_count < 10000) {
                $read_count = $read_count . '人浏览';
            }
            if ($read_count >= 10000) {
                $read_count = intval($read_count / 10000) . '万+人浏览';
            }
            $goods_class = 'class="goods-link"';
            $goods_count = mb_substr_count($item['content'], $goods_class);
            unset($list[$i]['content']);
            $list[$i]['read_count'] = $read_count;
            if ($goods_count)
                $list[$i]['goods_count'] = $goods_count . '件宝贝';
        }

        return [
            'code' => 0,
            'data' => [
                'list' => $list,
            ],
        ];

    }
}