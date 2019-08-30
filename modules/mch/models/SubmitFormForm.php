<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/12/28
 * Time: 11:24
 */

namespace app\modules\mch\models;


use app\models\Form;
use app\models\Option;

class SubmitFormForm extends Model
{
    public $store_id;

    public $form_list;
    public $is_form;
    public $form_name;

    public function rules()
    {
        return [
            [['form_list'], 'safe'],
            [['is_form'], 'integer'],
            [['form_name'],'trim'],
            [['form_name'],'string'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getModelError();
        }
        Form::updateAll(['is_delete' => 1], ['store_id' => $this->store_id]);
        $this->form_list = array_values($this->form_list);
        foreach ($this->form_list as $index => $value) {
            if (!$value['name']) {
                return [
                    'code' => 1,
                    'msg' => '请输入字段名称'
                ];
            }
            if(in_array($value['type'],['radio','checkbox'])){
                if(!$value['default']){
                    return [
                        'code'=>1,
                        'msg'=>'请输入单选或多选的默认值'
                    ];
                }
            }
            if ($value['id']) {
                $form = Form::findOne(['store_id' => $this->store_id, 'id' => $value['id']]);
            } else {
                $form = new Form();
            }
            $form->is_delete = 0;
            $form->addtime = time();
            $form->type = $value['type'];
            $form->name = $value['name'];
            $form->default = $value['default'];
            $form->required = $value['required'] ? $value['required'] : 0;
            $form->tip = $value['tip'];
            $form->sort = $index;
            $form->store_id = $this->store_id;
            $form->save();
        }
        Option::set('is_form', $this->is_form, $this->store_id, 'admin',0);
        Option::set('form_name', $this->form_name, $this->store_id, 'admin',"表单信息");
        return [
            'code' => 0,
            'msg' => '成功'
        ];
    }

}