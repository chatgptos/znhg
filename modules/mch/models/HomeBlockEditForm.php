<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/9/1
 * Time: 15:01
 */

namespace app\modules\mch\models;

use app\models\HomeBlock;
use app\models\Store;

/**
 * @property HomeBlock $model
 * @property Store $store
 */
class HomeBlockEditForm extends Model
{
    public $model;
    public $store;

    public $name;
    public $pic_list;

    public function rules()
    {
        return [
            [['name',], 'trim'],
            [['name', 'pic_list',], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => '板块名称',
            'pic_list' => '板块图片',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getModelError();
        }
        $this->model->name = $this->name;
        $this->model->data = json_encode([
            'pic_list' => $this->pic_list,
        ]);
        if ($this->model->isNewRecord) {
            $this->model->store_id = $this->store->id;
            $this->model->addtime = time();
            $this->model->is_delete = 0;
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