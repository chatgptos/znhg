<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/2
 * Time: 11:41
 */

namespace app\modules\mch\models;
use app\models\Award;
use app\models\Level;
use app\models\Store;

/**
 * @property \app\models\Award $model;
 */
class AwardForm extends Model
{
    public $store_id;
    public $model;

    public $level;
    public $name;
    public $money;
    public $status;
    public $discount;
    public $content;
    public $chance;
    public $quan;


    public function rules()
    {
        return [
            [['name','money'],'trim'],
            [['name'],'string'],
            [['level','name','money','status','discount'],'required','on'=>'edit'],
            [['status'],'in','range'=>[0,1]],
            [['discount'],'number','min'=>1,'max'=>1000],
            [['money'],'number','min'=>0],
            [['chance'],'number','min'=>0],
            [['quan'],'number','min'=>0],
            [['level'],'integer','min'=>0,'max'=>100],
            [['content'],'required','on'=>'content']
        ];
    }

    public function attributeLabels()
    {
        return [
            'level'=>'奖品等级',
            'name'=>'奖品名称',
            'money'=>'奖品花费多少抽',
            'status'=>'状态',
            'discount'=>'奖品发放个数券张数',
            'chance'=>'gailv',
            'quan'=>'quan',
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
            $exit = Award::find()->where(['level'=>$this->level,'store_id'=>$this->store_id,'is_delete'=>0])->exists();
            if($exit){
                return [
                    'code'=>1,
                    'msg'=>'会员等级已存在'
                ];
            }
        }
        if($this->name != $this->model->name){
            $exit_0 = Award::find()->where(['name'=>$this->name,'store_id'=>$this->store_id,'is_delete'=>0])->exists();
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
        $this->model->quan = $this->quan;
        $this->model->chance = $this->chance;
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