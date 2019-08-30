<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/6/26
 * Time: 16:27
 */

namespace app\modules\mch\models;

use app\models\Banner;
use app\models\Model;

use yii\data\Pagination;
use yii\db\Query;

class BannerForm extends Model
{
    public $banner;

    public $store_id;
    public $pic_url;
    public $title;
    public $page_url;
    public $sort;
//    public $addtime;
//    public $is_delete;

    /**
     * @return array
     * 验证
     */
    public function rules()
    {
        return [
            [['store_id', 'pic_url',], 'required'],
            [['store_id', 'sort'], 'integer'],
            [['pic_url', 'page_url'], 'string'],
            [['title'], 'string', 'max' => 255],
            /*
            ["page_url",function($attr,$params){
                // 判断 路径地址是否符合条件
                $page_url = $this->$attr;
                $pageArr = explode('?',$page_url);
                $pageLeftArr = explode('/',$pageArr[0]);
                if ($pageLeftArr[3] != 'goods' && $pageLeftArr[3] != 'list'){
                    $this->addError("username","路径地址不正确请重试");
                }else{
                    return true;
                }
            }],
            */
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'store_id' => '商城id',
            'pic_url' => '图片',
            'title' => '标题',
            'page_url' => '页面路径',
            'sort' => '排序',
//            'addtime' => '添加时间',
            'is_delete' => '是否删除：0=未删除，1=已删除',
        ];
    }

    /**
     * 获取列表
     * @return array
     */
    public function getList($store_id)
    {
        $query = Banner::find()->andWhere(['is_delete' => 0, 'store_id' => $store_id, 'type' => 1]);
        $count = $query->count();
        $p = new Pagination(['totalCount' => $count, 'pageSize' => 20]);
        $list = $query
            ->orderBy('sort ASC')
            ->offset($p->offset)
            ->limit($p->limit)
            ->asArray()
            ->all();

        return [$list, $p];
    }

    public function save()
    {
        if ($this->validate()) {
            $banner = $this->banner;
            if ($banner->isNewRecord) {
                $banner->is_delete = 0;
                $banner->addtime = time();
                $banner->type = 1;
            }

//            // 判断 路径地址是否符合条件
//            $page_url = $this->attributes['page_url'];
//            $pageArr = explode('?',$page_url);
//            $pageLeftArr = explode('/',$pageArr[0]);
//            if ($pageLeftArr[2] != 'goods' && $pageLeftArr[2] != 'list'){
//                return [
//                    'code'=>1,
//                    'msg'=>'路径地址不正确请重试!'
//                ];
//            }

            $banner->attributes = $this->attributes;
            return $banner->saveBanner();
        } else {
            return $this->getModelError();
        }
    }
}