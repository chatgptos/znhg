<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/10/11
 * Time: 20:26
 */

namespace app\modules\mch\models\bookmall;


use app\modules\mch\models\Model;

class SeckillGoodsEditForm extends Model
{
    public $goods_id;
    public $store_id;
    public $attr;
    public $open_time;
    public $open_date;
    public $start_date_bookmall;
    public $end_date_bookmall;

    public $buy_max;

    public function rules()
    {
        return [
            [['start_date_bookmall','end_date_bookmall','goods_id', 'attr', 'open_time', 'open_date',], 'required'],
            ['buy_max', 'default', 'value' => 0],
            ['buy_max', 'integer', 'min' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'buy_max' => '限购数量',
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getModelError();
        }
        $exist = Goods::find()->where(['id' => $this->goods_id, 'store_id' => $this->store_id])->exists();
        if (!$exist)
            return [
                'code' => 0,
                'msg' => '该商品不存在，请选择其它商品',
            ];
        $open_date = json_decode($this->open_date, true);
        $open_time = json_decode($this->open_time, true);
        \Yii::$app->request->getHostInfo();
        foreach ($open_date as $date) {
            foreach ($open_time as $time) {
                $model = SeckillGoods::findOne([
                    'goods_id' => $this->goods_id,
                    'start_time' => intval($time),
                    'open_date' => $date,
                    'start_date_crowdc' => $this->start_date_bookmall,
                    'end_date_crowdc' => $this->end_date_bookmall,
                    'is_delete' => 0,
                ]);
                \Yii::trace("---->" . ($model == null));
                if (!$model) {
                    $model = new SeckillGoods();
                    $model->store_id = $this->store_id;
                    $model->goods_id = $this->goods_id;
                    $model->start_time = intval($time);
                    $model->open_date = $date;
                    $model->start_date_bookmall = $this->start_date_bookmall;
                    $model->end_date_bookmall = $this->end_date_bookmall;
                    $model->is_delete = 0;
                }
                $model->attr = $this->attr;
                $model->buy_max = $this->buy_max;
                $model->save();
            }
        }
        return [
            'code' => 0,
            'msg' => '保存成功',
            'data' => [
                'return_url' => \Yii::$app->urlManager->createUrl(['mch/bookmall/seckill/goods-detail', 'goods_id' => $this->goods_id]),
            ],
        ];
    }
}