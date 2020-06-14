<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/9/28
 * Time: 14:11
 */

namespace app\modules\api\models;


use app\extensions\getInfo;
use app\models\Topic;
use app\models\TopicFavorite;
use app\models\User;
use yii\helpers\VarDumper;

class TopicForm extends Model
{
    public $store_id;
    public $user_id;
    public $id;

    public function rules()
    {
        return [
            ['id', 'required'],
        ];
    }

    public function search()
    {
        if (!$this->validate())
            return $this->getModelError();
        $model = Topic::find()
            ->alias('t')
            ->where(['t.store_id' => $this->store_id, 't.id' => $this->id, 't.is_delete' => 0])
            ->select('u.avatar_url,u.nickname,t.id,title,read_count,virtual_read_count,content,t.addtime,virtual_favorite_count')
            ->leftJoin(['u' => User::tableName()], 'u.id=t.user_id')
            ->asArray()->one();

        if (empty($model))
            return [
                'code' => 1,
                'msg' => '内容不存在',
            ];
        Topic::updateAll(['read_count' => $model['read_count'] + 1], ['id' => $model['id']]);

        $model['read_count'] = intval($model['read_count']) + intval($model['virtual_read_count']);
        unset($model['virtual_read_count']);
        if ($model['read_count'] < 10000) {
            $model['read_count'] = $model['read_count'] . '人浏览';
        }
        if ($model['read_count'] >= 10000) {
            $model['read_count'] = intval($model['read_count'] / 10000) . '万+人浏览';
        }

        //查找出所有的该书籍用户
        $favorite_user_list = TopicFavorite::find()
            ->select('u.avatar_url,u.nickname')
            ->alias('t')
            ->innerJoin(['u' => User::tableName()], 'u.id=t.user_id')
            ->where([
            'topic_id' => $model['id'],
            't.is_delete' => 0,
            't.store_id' => $this->store_id,
        ]);
        $model['user_list']=$favorite_user_list->limit(6)->orderBy('t.addtime DESC')->asArray()->all();
        $model['user_list_count']=$favorite_user_list->count();

        //最后加一个
        $model['user_list'][$model['user_list_count']]['avatar_url'] = 0;
//            默认加上设定的个数
//        for ($i = $begin_id; $i< $end_id; $i++){
//            if (!isset($model['user_list'][$i])){
//                $model['user_list'][$i]['avatar_url'] = 0;
//            }
//        }



        $model['read_count'] = intval($model['read_count']) + intval($model['virtual_read_count']);
        unset($model['virtual_read_count']);
        if ($model['read_count'] < 10000) {
            $model['read_count'] = $model['read_count'] . '人浏览';
        }
        if ($model['read_count'] >= 10000) {
            $model['read_count'] = intval($model['read_count'] / 10000) . '万+人浏览';
        }


        $model['addtime'] = date('Y-m-d', $model['addtime']);

        $favorite = TopicFavorite::findOne(['user_id' => $this->user_id, 'topic_id' => $model['id'], 'is_delete' => 0]);
        $model['is_favorite'] = $favorite ? 1 : 0;
        $model['content'] = $this->transTxvideo($model['content']);

        return [
            'code' => 0,
            'data' => $model,
        ];
    }

    private function transTxvideo($content)
    {
        preg_match_all("/https\:\/\/v\.qq\.com[^ '\"]+\.html/i", $content, $match_list);
        if (!is_array($match_list) || count($match_list) == 0)
            return $content;
        $url_list = $match_list[0];
        foreach ($url_list as $url) {
            $res = getInfo::getVideoInfo($url);
            if ($res['code'] == 0) {
                $new_url = $res['url'];
                $content = str_replace('src="' . $url . '"', 'src="' . $new_url . '"', $content);
            }
        }
        return $content;
    }
}