<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/9/5
 * Time: 15:51
 */

namespace app\modules\mch\models\crowdmall;
use app\modules\mch\models\crowdmall\Delivery;
use app\models\Express;
use app\models\Sender;
use app\modules\mch\models\Model;
use yii\data\Pagination;

/**
 * @property \app\models\Delivery $delivery;
 */
class DeliveryForm extends Model
{
    public $delivery;

    public $store_id;
    public $express_id;
    public $customer_name;
    public $customer_pwd;
    public $month_code;
    public $send_name;
    public $send_site;

    public function rules()
    {
        return [
            [['customer_name','customer_pwd'],'required'],
            [['express_id'],'integer'],
            [['month_code','send_name','send_site'],'string']
        ];
    }
    public function attributeLabels()
    {
        return [
            'express_id'=>'快递公司',
            'customer_name'=>'电子面单客户账号',
            'customer_pwd'=>'电子面单密码',
            'month_code'=>'月结编码',
            'send_name'=>'网点名称',
            'send_site'=>'网点编码',
        ];
    }
    public function save()
    {
        if(!$this->validate()){
            return $this->getModelError();
        }
        if($this->delivery->isNewRecord){
            $this->delivery->is_delete = 0;
            $this->delivery->addtime = time();
        }
        $this->delivery->store_id = $this->store_id;
        $this->delivery->attributes = $this->attributes;
        if($this->delivery->save()){
            return [
                'code'=>0,
                'msg'=>'成功'
            ];
        }else{
            return [
                'code'=>1,
                'msg'=>'失败'
            ];
        }
    }

    public function getList()
    {
        $query = Delivery::find()->alias('d')->where(['d.is_delete'=>0,'d.store_id'=>$this->store_id])
            ->leftJoin(Express::tableName().' e','e.id=d.express_id')
            ->leftJoin(Sender::tableName().' s','s.delivery_id=d.id');
        $count = $query->count();
        $p = new Pagination(['totalCount'=>$count,'pageSize'=>20]);
        $list = $query->select([
            'd.*','e.name',
            's.name sender_name','s.tel sender_tel','s.mobile sender_mobile','s.province sender_province','s.city sender_city','s.exp_area sender_area','s.address sender_address'
        ])
            ->orderBy('d.addtime DESC')->limit($p->limit)->offset($p->offset)->asArray()->all();
        return [$list,$p];
    }
}