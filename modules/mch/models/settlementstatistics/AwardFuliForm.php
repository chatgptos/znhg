<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/2
 * Time: 11:41
 */

namespace app\modules\mch\models\settlementstatistics;


use app\models\Store;
use app\modules\mch\models\Model;

/**
 * @property AwardFuli $model;
 */
class AwardFuliForm extends Model
{
    public $store_id;
    public $model;

    public $level;
    public $name;
    public $money;
    public $all_money;
    public $status;
    public $num;
    public $coupon_require;
    public $end_fulichi_time;
    public $require_level;




    public function rules()
    { 
        
        return [
            [['num','coupon_require','all_money','name','money'],'trim'],
            [['end_fulichi_time','name'],'string'],
            [['require_level','level','name','money','status'],'required','on'=>'edit'],
            [['status'],'in','range'=>[0,1]],   
            [['level'],'integer','min'=>0,'max'=>100], 
        ];
    }

    public function attributeLabels()
    {
        return [
            'level'=>'奖品等级',
            'name'=>'奖品名称',
            'money'=>'奖励',
            'status'=>'状态',
            'num'=>'奖品发放个数券张数',
            'coupon_require'=>'gailv',
            'all_money'=>'总奖励',
            'end_fulichi_time'=>'时间',
            'require_level'=>'require_level'

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
//        if($this->level != $this->model->level){
//            $exit = AwardFuli::find()->where(['level'=>$this->level,'store_id'=>$this->store_id,'is_delete'=>0])->exists();
//            if($exit){
//                return [
//                    'code'=>1,
//                    'msg'=>'会员等级已存在'
//                ];
//            }
//        }
        if($this->name != $this->model->name){
            $exit_0 = AwardFuli::find()->where(['name'=>$this->name,'store_id'=>$this->store_id,'is_delete'=>0])->exists();
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
        $this->model->num = $this->num;
        $this->model->coupon_require = $this->coupon_require;
        $this->model->all_money = $this->all_money;
        $this->model->end_fulichi_time =strtotime($this->end_fulichi_time);
        $this->model->require_level = $this->require_level;
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