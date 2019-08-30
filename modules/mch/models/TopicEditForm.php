<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/9/27
 * Time: 19:32
 */

namespace app\modules\mch\models;

use app\models\Topic;

/**
 * @property Topic $model
 */
class TopicEditForm extends Model
{
    public $model;

    public $title;
    public $sub_title;
    public $cover_pic;
    public $content;
    public $virtual_read_count;
    public $virtual_agree_count;
    public $virtual_favorite_count;
    public $layout;
    public $sort;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'content', 'cover_pic',], 'required'],
            [['virtual_read_count', 'virtual_agree_count', 'virtual_favorite_count', 'layout', 'sort',], 'integer'],
            [['cover_pic', 'content'], 'string'],
            [['title', 'sub_title'], 'string', 'max' => 255],
            [['virtual_read_count', 'virtual_agree_count', 'virtual_favorite_count',], 'default', 'value' => 0],
            [['sort'], 'default', 'value' => 1000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'store_id' => 'Store ID',
            'title' => '标题',
            'sub_title' => '副标题',
            'cover_pic' => '封面图片',
            'content' => '专题内容',
            'read_count' => '阅读量',
            'virtual_read_count' => '虚拟阅读量',
            'virtual_agree_count' => '虚拟点赞数',
            'virtual_favorite_count' => '虚拟收藏量',
            'layout' => '布局方式：0=小图，1=大图模式',
            'sort' => '排序：升序',
            'addtime' => 'Addtime',
            'is_delete' => 'Is Delete',
        ];
    }

    public function save()
    {
        if (!$this->validate())
            return $this->getModelError();
        $this->model->attributes = $this->attributes;
        if ($this->model->isNewRecord) {
            $this->model->read_count = 0;
            $this->model->agree_count = 0;
            $this->model->addtime = time();
        }
        if ($this->model->save())
            return [
                'code' => 0,
                'msg' => '保存成功',
            ];
        else
            return $this->getModelError($this->model);
    }
}