<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/7/5
 * Time: 16:00
 */

namespace app\modules\api\models;


use app\models\Banner;
use app\models\Cat;
use app\models\Coupon;
use app\models\Goods;
use app\models\GoodsPic;
use app\models\HomeBlock;
use app\models\HomeNav;
use app\models\SeckillGoods;
use app\models\Option;
use app\models\PtGoods;
use app\models\PtOrder;
use app\models\PtOrderDetail;
use app\models\Store;
use app\models\Topic;
use app\models\UserCoupon;
use yii\helpers\VarDumper;

class IndexForm extends Model
{
    public $store_id;

    public function search()
    {
        $store = Store::findOne($this->store_id);
        if (!$store)
            return [
                'code' => 1,
                'msg' => 'Store不存在',
            ];
        $this->getSeckillData();
        $banner_list = Banner::find()->where([
            'is_delete' => 0,
            'store_id' => $this->store_id,
            'type' => 1,
        ])->orderBy('sort ASC')->asArray()->all();
        foreach ($banner_list as $i => $banner) {
            $banner_list[$i]['open_type'] = 'navigate';
        }

        $nav_icon_list = HomeNav::find()->where([
            'is_delete' => 0,
            'store_id' => $this->store_id,
        ])->orderBy('sort ASC,addtime DESC')->select('name,pic_url,url,name,open_type')->asArray()->all();

        $cat_list = Cat::find()->where([
            'is_delete' => 0,
            'parent_id' => 0,
            'store_id' => $this->store_id,
        ])->orderBy('sort ASC')->asArray()->all();
        foreach ($cat_list as $i => $cat) {
            $cat_list[$i]['page_url'] = '/pages/list/list?cat_id=' . $cat['id'];
            $cat_list[$i]['open_type'] = 'navigate';
            $cat_list[$i]['cat_pic'] = $cat['pic_url'];
            $goods_list_form = new GoodsListForm();
            $goods_list_form->store_id = $this->store_id;
            $goods_list_form->cat_id = $cat['id'];
            $goods_list_form->limit = $store->cat_goods_count;
            $goods_list_form_res = $goods_list_form->search();
            $goods_list = $goods_list_form_res['code'] == 0 ? $goods_list_form_res['data']['list'] : [];
            $cat_list[$i]['goods_list'] = $goods_list;
        }

        $block_list = HomeBlock::find()->where(['store_id' => $this->store_id, 'is_delete' => 0])->all();
        $new_block_list = [];
        foreach ($block_list as $item) {
            $new_block_list[] = [
                'id' => $item->id,
                'name' => $item->name,
                'data' => json_decode($item->data, true),
            ];
        }

        $coupon_form = new CouponListForm();
        $coupon_form->store_id = $this->store_id;
        $coupon_form->user_id = isset(\Yii::$app->user->identity) ? \Yii::$app->user->identity->id : 0;
        $coupon_list = $coupon_form->getList();

        $topic_list = Topic::find()->where(['store_id' => $this->store_id, 'is_delete' => 0])->orderBy('sort ASC,addtime DESC')->limit(6)->select('id,title')->asArray()->all();
        return [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'module_list' => $this->getModuleList($store),
                'store' => [
                    'id' => $store->id,
                    'name' => $store->name,
                    'is_coupon' => $store->is_coupon,
                    'show_customer_service' => $store->show_customer_service,
                    'service' => Option::get('service', $this->store_id, 'admin', '/images/icon-service.png'),
                    'copyright' => $store->copyright,
                    'copyright_pic_url' => $store->copyright_pic_url,
                    'copyright_url' => $store->copyright_url,
                    'contact_tel' => $store->contact_tel,
                    'cat_style' => $store->cat_style,
                    'address' => $store->address,
                    'is_offline' => $store->is_offline,
                ],
                'banner_list' => $banner_list,
                'nav_icon_list' => $nav_icon_list,
                'cat_goods_cols' => $store->cat_goods_cols,
                'cat_list' => $cat_list,
                'block_list' => $new_block_list,
                'coupon_list' => $coupon_list,
                'topic_list' => $topic_list,
                'nav_count' => $store->nav_count,
                'notice' => Option::get('notice', $this->store_id, 'admin'),
                'seckill' => $this->getSeckillData(),
                'pintuan' => $this->getPintuanData(),
            ],
        ];
    }

    private function getBlockList()
    {

    }

    /**
     * @param Store $store
     */
    private function getModuleList($store)
    {
        $list = json_decode($store->home_page_module, true);
        if (!$list) {
            $list = [
                [
                    'name' => 'banner',
                ],
                [
                    'name' => 'search',
                ],
                [
                    'name' => 'nav',
                ],
                [
                    'name' => 'topic',
                ],
                [
                    'name' => 'coupon',
                ],
                [
                    'name' => 'cat',
                ],
            ];
        } else {
            $new_list = [];
            foreach ($list as $item) {
                if (stripos($item['name'], 'block-') !== false) {
                    $names = explode('-', $item['name']);
                    $new_list[] = [
                        'name' => $names[0],
                        'block_id' => $names[1],
                    ];
                } elseif (stripos($item['name'], 'single_cat-') !== false) {
                    $names = explode('-', $item['name']);
                    $new_list[] = [
                        'name' => $names[0],
                        'cat_id' => $names[1],
                    ];
                } else {
                    $new_list[] = $item;
                }
            }
            $list = $new_list;
        }
        return $list;
    }

    public function getSeckillData()
    {
        $list = SeckillGoods::find()->alias('mg')
            ->select('g.id,g.name,g.cover_pic AS pic,g.price,mg.attr,mg.start_time')
            ->leftJoin(['g' => Goods::tableName()], 'mg.goods_id=g.id')
            ->where([
                'AND',
                [
                    'mg.is_delete' => 0,
                    'g.is_delete' => 0,
                    'mg.open_date' => date('Y-m-d'),
                    'g.status' => 1,
                    'mg.start_time' => date('H'),
                    'mg.store_id' => $this->store_id,
                ],
            ])
            ->orderBy('g.sort ASC,g.addtime DESC')
            ->limit(10)
            ->asArray()->all();
        foreach ($list as $i => $item) {
            $item['attr'] = json_decode($item['attr'], true);
            $list[$i] = $item;
            $price_list = [];
            foreach ($item['attr'] as $attr) {
                if ($attr['seckill_price'] <= 0) {
                    $price_list[] = doubleval($item['price']);
                } else {
                    $price_list[] = doubleval($attr['seckill_price']);
                }
            }
            $list[$i]['price'] = number_format($list[$i]['price'], 2, '.', '');
            $list[$i]['seckill_price'] = number_format(min($price_list), 2, '.', '');
            unset($list[$i]['attr']);
        }
        if (count($list) == 0)
            return [
                'name' => '暂无秒杀活动',
                'rest_time' => 0,
                'goods_list' => null,
            ];
        return [
            'name' => intval(date('H')) . '点场',
            'rest_time' => max(intval(strtotime(date('Y-m-d H:59:59')) - time()), 0),
            'goods_list' => $list,
        ];
    }

    public function getPintuanData()
    {
        $num_query = PtOrderDetail::find()->alias('pod')
            ->select('pod.goods_id,SUM(pod.num) AS sale_num')
            ->leftJoin(['po' => PtOrder::tableName()], 'pod.order_id=po.id')
            ->where([
                'AND',
                [
                    'pod.is_delete' => 0,
                    'po.is_delete' => 0,
                    'po.is_pay' => 1,
                ],
            ])->groupBy('pod.goods_id');
        $list = PtGoods::find()->alias('pg')
            ->select('pg.*,pod.sale_num')
            ->leftJoin(['pod' => $num_query], 'pg.id=pod.goods_id')
            ->where([
                'AND',
                [
                    'pg.is_delete' => 0,
                    'pg.status' => 1,
                    'pg.store_id' => $this->store_id,
                ],
            ])->orderBy('pg.is_hot DESC,pg.sort ASC,pg.addtime DESC')
            ->limit(10)
            ->asArray()->all();
        $new_list = [];
        foreach ($list as $item) {
            $new_list[] = [
                'id' => $item['id'],
                'pic' => $item['cover_pic'],
                'name' => $item['name'],
                'price' => number_format($item['price'], 2, '.', ''),
                'sale_num' => intval($item['sale_num'] ? $item['sale_num'] : 0) + intval($item['virtual_sales'] ? $item['virtual_sales'] : 0),
            ];
        }
        return [
            'goods_list' => $new_list,
        ];
    }
}