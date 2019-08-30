<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/12/1
 * Time: 16:43
 */

namespace app\modules\mch\models;

/**
 * @property \app\models\PrinterSetting $model;
 */
class PrinterSettingForm extends Model
{
    public $store_id;
    public $model;

    public $printer_id;
    public $type;
    public $block_id;


    public function rules()
    {
        return [
            [['printer_id','block_id'],'integer'],
            [['type'],'default','value'=>(object)[]]
        ];
    }

    public function save()
    {
        if(!$this->validate()){
            return $this->getModelError();
        }
        if($this->model->isNewRecord){
            $this->model->is_delete = 0;
            $this->model->store_id = $this->store_id;
            $this->model->addtime = time();
        }
        $this->model->printer_id = $this->printer_id;
        $this->model->type = json_encode($this->type,true);
        $this->model->block_id = 0;
        if($this->model->save()){
            return [
                'code'=>0,
                'msg'=>'成功'
            ];
        }else{
            return $this->getModelError($this->model);
        }
    }
}