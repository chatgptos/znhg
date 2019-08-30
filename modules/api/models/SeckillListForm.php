<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/10/16
 * Time: 15:48
 */

namespace app\modules\api\models;


use app\models\Seckill;
use app\models\SeckillGoods;

class SeckillListForm extends Model
{
    public $store_id;
    public $date;
    public $time;

    public function search()
    {
        if (!$this->validate())
            return $this->getModelError();
        $seckill = Seckill::findOne([
            'store_id' => $this->store_id,
        ]);
        if (!$seckill)
            return [
                'code' => 1,
                'msg' => '暂无秒杀安排',
            ];
        $seckill->open_time = json_decode($seckill->open_time, true);
        $seckill_list = SeckillGoods::find()->alias('mg')->where([
            'mg.open_date' => $this->date,
            'start_time' => $seckill->open_time,
            'is_delete' => 0,
        ])->groupBy('mg.start_time')->asArray()
            ->select('mg.*')->all();
        $has_active = false;
        foreach ($seckill_list as $i => $item) {
            if ($item['start_time'] < $this->time) {
                $seckill_list[$i]['status'] = 0;
                $seckill_list[$i]['status_text'] = '已结束';
                $seckill_list[$i]['active'] = false;
            } elseif ($item['start_time'] == $this->time) {
                $seckill_list[$i]['status'] = 1;
                $seckill_list[$i]['status_text'] = '进行中';
                $seckill_list[$i]['active'] = true;
                $has_active = true;
            } else {
                $seckill_list[$i]['status'] = 2;
                $seckill_list[$i]['status_text'] = '即将开始';
                $seckill_list[$i]['active'] = false;
            }
            $seckill_list[$i]['title'] = $item['start_time'] < 10 ? ('0' . $item['start_time'] . ':00') : ($item['start_time'] . ':00');
            $seckill_list[$i]['begin_time'] = strtotime($item['open_date'] . ' ' . $item['start_time'] . ':00:00');
            $seckill_list[$i]['end_time'] = strtotime($item['open_date'] . ' ' . $item['start_time'] . ':59:59');
            $seckill_list[$i]['now_time'] = time();
        }
        if (!$has_active) {
            foreach ($seckill_list as $i => $item) {
                $seckill_list[$i]['active'] = true;
                break;
            }
        }
        return [
            'code' => 0,
            'data' => [
                'list' => $seckill_list,
            ],
        ];
    }
}