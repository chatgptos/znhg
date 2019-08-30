<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/2
 * Time: 11:41
 */

namespace app\modules\mch\models;
use app\models\Level;
use app\models\Store;

/**
 * @property \app\models\Level $model;
 */
class LevelForm extends Model
{
    public $store_id;
    public $model;

    public $level;
    public $name;
    public $money;
    public $status;
    public $discount;
    public $content;


    public function rules()
    {
        return [
            [['name','money'],'trim'],
            [['name'],'string'],
            [['level','name','money','status','discount'],'required','on'=>'edit'],
            [['status'],'in','range'=>[0,1]],
            [['discount'],'number','min'=>0.1,'max'=>10],
            [['money'],'number','min'=>0],
            [['level'],'integer','min'=>0,'max'=>100],
            [['content'],'required','on'=>'content']
        ];
    }

    public function attributeLabels()
    {
        return [
            'level'=>'会员等级',
            'name'=>'等级名称',
            'money'=>'升级条件',
            'status'=>'状态',
            'discount'=>'折扣',
            'content'=>'会员等级说明'
        ];
    }
    public function save()
    {
        if(!$this->validate()){
            return $this->getModelError();
        }

        if($this->model->isNewRecord){
            $this->model->is_delete = 0;
            $this->model->addtime = time();
        }
        if($this->level != $this->model->level){
            $exit = Level::find()->where(['level'=>$this->level,'store_id'=>$this->store_id,'is_delete'=>0])->exists();
            if($exit){
                return [
                    'code'=>1,
                    'msg'=>'会员等级已存在'
                ];
            }
        }
        if($this->name != $this->model->name){
            $exit_0 = Level::find()->where(['name'=>$this->name,'store_id'=>$this->store_id,'is_delete'=>0])->exists();
            if($exit_0){
                return [
                    'code'=>1,
                    'msg'=>'等级名称重复'
                ];
            }
        }
        /*
        $exit_2 = Level::find()->where(['store_id'=>$this->store_id,'is_delete'=>0])
            ->andWhere(['<','level',$this->level])->andWhere(['>=','money',$this->money])->exists();
        if($exit_2){
            return [
                'code'=>1,
                'msg'=>'升级条件不能小于等于低等级会员'
            ];
        }
        $exit_1 = Level::find()->where(['store_id'=>$this->store_id,'is_delete'=>0])
            ->andWhere(['<','level',$this->level])->andWhere(['<','discount',$this->discount])->exists();
        if($exit_1){
            return [
                'code'=>1,
                'msg'=>'折扣不能小于低等级会员'
            ];
        }
        $exit_3 = Level::find()->where(['store_id'=>$this->store_id,'is_delete'=>0])
            ->andWhere(['>','level',$this->level])->andWhere(['<=','money',$this->money])->exists();
        if($exit_3){
            return [
                'code'=>1,
                'msg'=>'升级条件不能大于等于高等级会员'
            ];
        }
        $exit_4 = Level::find()->where(['store_id'=>$this->store_id,'is_delete'=>0])
            ->andWhere(['>','level',$this->level])->andWhere(['>','discount',$this->discount])->exists();
        if($exit_4){
            return [
                'code'=>1,
                'msg'=>'折扣不能大于高等级会员'
            ];
        }
        */

        $this->model->store_id = $this->store_id;
        $this->model->level = $this->level;
        $this->model->name  = $this->name;
        $this->model->money = $this->money;
        $this->model->status = $this->status;
        $this->model->discount = $this->discount;

        if($this->model->save()){
            return [
                'code'=>0,
                'msg'=>'成功'
            ];
        }else{
            return $this->getModelError($this->model);
        }
    }

    public function saveContent()
    {
        if(!$this->validate()){
            return $this->getModelError();
        }

        $store = Store::findOne(['id'=>$this->store_id]);
        $store->member_content = $this->content;

        if($store->save()){
            return [
                'code'=>0,
                'msg'=>'成功'
            ];
        }else{
            return $this->getModelError($store);
        }
    }
}