<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/12/7
 * Time: 18:49
 */

namespace app\modules\mch\models\group;


use app\models\Option;
use app\modules\mch\models\Model;

class AdForm extends Model
{
    public $store_id;
    public $pic_list;

    public function rules()
    {
        return [
            [['pic_list'], 'safe'],
        ];
    }

    public function save()
    {
        if (!$this->validate())
            return $this->getModelError();
        Option::set('pt_ad', $this->pic_list, $this->store_id);
        return [
            'code' => 0,
            'msg' => '保存成功',
        ];
    }

    public function getPicList()
    {
        $data = Option::get('pt_ad', $this->store_id);
        return $data;
    }
}