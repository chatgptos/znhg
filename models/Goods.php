<?php

namespace app\models;

use Yii;
use yii\helpers\VarDumper;


/**
 * This is the model class for table "ushop_goods".
 *
 * @property integer $id
 * @property integer $store_id
 * @property string $name
 * @property string $price
 * @property string $original_price
 * @property string $detail
 * @property string $cat_id
 * @property integer $status
 * @property integer $addtime
 * @property integer $is_delete
 * @property string $attr
 * @property string $service
 * @property integer $sort
 * @property integer $virtual_sales
 * @property string $cover_pic
 * @property string $video_url
 * @property string $unit
 * @property integer $individual_share
 * @property string $share_commission_first
 * @property string $share_commission_second
 * @property string $share_commission_third
 * @property double $weight
 * @property string $freight
 * @property string $full_cut
 * @property string $integral
 * @property integer $use_attr
 * @property integer $share_type
 */
class Goods extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%goods}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id', 'name', 'detail', 'attr'], 'required'],
            [['store_id', 'cat_id', 'status', 'addtime', 'is_delete', 'sort', 'virtual_sales', 'individual_share', 'freight', 'use_attr', 'share_type'], 'integer'],
            [['price', 'original_price', 'share_commission_first', 'share_commission_second', 'share_commission_third', 'weight'], 'number'],
            [['detail', 'attr', 'cover_pic', 'video_url', 'full_cut', 'integral'], 'string'],
            [['name', 'unit'], 'string', 'max' => 255],
            [['service'], 'string', 'max' => 2000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'store_id' => 'Store ID',
            'name' => '商品名称',
            'price' => '售价',
            'original_price' => '原价（只做显示用）',
            'detail' => '商品详情，图文',
            'cat_id' => '商品类别',
            'status' => '上架状态：0=下架，1=上架',
            'addtime' => 'Addtime',
            'is_delete' => 'Is Delete',
            'attr' => '规格的库存及价格',
            'service' => '商品服务选项',
            'sort' => '排序  升序',
            'virtual_sales' => '虚拟销量',
            'cover_pic' => '商品缩略图',
            'video_url' => '视频',
            'unit' => '单位',
            'individual_share' => '是否单独分销设置：0=否，1=是',
            'share_commission_first' => '一级分销佣金比例',
            'share_commission_second' => '二级分销佣金比例',
            'share_commission_third' => '三级分销佣金比例',
            'weight' => '重量',
            'freight' => '运费模板ID',
            'full_cut' => '满减',
            'integral' => '积分设置',
            'use_attr' => '是否使用规格：0=不使用，1=使用',
            'share_type' => '佣金配比 0--百分比 1--固定金额',
        ];
    }

    /**
     * 修改商品
     * @return array
     */
    public function saveGoods()
    {
        if ($this->validate()) {
            if ($this->save()) {
                return [
                    'code' => 0,
                    'msg' => '成功'
                ];
            } else {
                return [
                    'code' => 1,
                    'msg' => '失败'
                ];
            }
        } else {
            return (new Model())->getModelError($this);
        }
    }

    public function getGoodsPicList()
    {
        return $this->hasMany(GoodsPic::className(), ['goods_id' => 'id'])->where(['is_delete' => 0]);
    }

    public function getGoodsPic($index = 0)
    {
        $list = $this->goodsPicList;
        if (!$list)
            return null;
        return isset($list[$index]) ? $list[$index] : null;
    }

    public static function getGoodsPicStatic($goods_id, $index = 0)
    {
        $goods = Goods::findOne($goods_id);
        if (!$goods)
            return null;
        return $goods->getGoodsPic($index);
    }

    public function getGoodsCover()
    {
        if ($this->cover_pic)
            return $this->cover_pic;
        $pic = $this->getGoodsPic(0);
        if ($pic)
            return $pic->pic_url;
        return null;
    }

    public function getGoodsCoverStatic($goods_id)
    {
        $goods = Goods::findOne($goods_id);
        if ($goods)
            return $goods->getGoodsCover();
        return null;
    }

    public function getCat()
    {
        return $this->hasOne(Cat::className(), ['id' => 'cat_id']);
    }

    /**
     * 获取商品总库存
     * @param int $id 商品id
     */
    public function getNum($id = null)
    {
        $goods = null;
        if (!$id)
            $goods = $this;
        else {
            $goods = static::findOne($id);
            if (!$goods)
                return 0;
        }
        if (!$goods->attr)
            return 0;
        $num = 0;
        $attr_rows = json_decode($goods->attr, true);
        foreach ($attr_rows as $attr_row) {
            $num += intval($attr_row['num']);
        }
        return $num;
    }

    /**
     * 根据规格获取商品的库存及规格价格信息
     * @param array $attr_id_list 规格id列表 eg. [1,4,9]
     * @return array|null eg.
     */
    public function getAttrInfo($attr_id_list)
    {
        sort($attr_id_list);
        $attr_rows = json_decode($this->attr, true);
        if (empty($attr_rows))
            return null;
        foreach ($attr_rows as $i => $attr_row) {
            $key = [];
            foreach ($attr_row['attr_list'] as $j => $attr) {
                $key[] = $attr['attr_id'];
            }
            sort($key);
            if (!array_diff($attr_id_list, $key)) {
                if (!$attr_rows[$i]['price'])
                    $attr_rows[$i]['price'] = $this->price;
                return $attr_rows[$i];
            }
        }
        return null;
    }

    /**
     * 获取商品可选的规格列表
     */
    public function getAttrGroupList()
    {
        $attr_rows = json_decode($this->attr, true);
        if (empty($attr_rows))
            return [];
        $attr_group_list = [];
        foreach ($attr_rows as $attr_row) {
            foreach ($attr_row['attr_list'] as $i => $attr) {
                $attr_id = $attr['attr_id'];
                $attr = Attr::findOne(['id' => $attr_id, 'is_delete' => 0]);
                if (!$attr)
                    continue;
                $in_list = false;
                foreach ($attr_group_list as $j => $attr_group) {
                    if ($attr_group->attr_group_id == $attr->attr_group_id) {
                        $attr_obj = (object)[
                            'attr_id' => $attr->id,
                            'attr_name' => $attr->attr_name,
                        ];
                        if (!in_array($attr_obj, $attr_group_list[$j]->attr_list)) {
                            $attr_group_list[$j]->attr_list[] = $attr_obj;
                        }
                        $in_list = true;
                        continue;
                    }
                }
                if (!$in_list) {
                    $attr_group = AttrGroup::findOne(['is_delete' => 0, 'id' => $attr->attr_group_id]);
                    if ($attr_group) {
                        $attr_group_list[] = (object)[
                            'attr_group_id' => $attr_group->id,
                            'attr_group_name' => $attr_group->attr_group_name,
                            'attr_list' => [
                                (object)[
                                    'attr_id' => $attr->id,
                                    'attr_name' => $attr->attr_name,
                                ],
                            ],
                        ];
                    }
                }
            }
        }
        return $attr_group_list;
    }

    /**
     * 库存增加操作
     */
    public function numAdd($attr_id_list, $num)
    {
        sort($attr_id_list);
        $attr_group_list = json_decode($this->attr);
        $add_attr_num = false;
        foreach ($attr_group_list as $i => $attr_group) {
            $group_attr_id_list = [];
            foreach ($attr_group->attr_list as $attr)
                array_push($group_attr_id_list, $attr->attr_id);
            sort($group_attr_id_list);
            if (!array_diff($attr_id_list, $group_attr_id_list)) {
                $attr_group_list[$i]->num = intval($attr_group_list[$i]->num) + $num;
                $add_attr_num = true;
                break;
            }
        }
        if (!$add_attr_num)
            return false;
        $this->attr = json_encode($attr_group_list, JSON_UNESCAPED_UNICODE);
        $this->save();
        return true;
    }

    /**
     * 库存减少操作
     * @param array $attr_id_list eg. [1,4,2]
     */
    public function numSub($attr_id_list, $num)
    {
        sort($attr_id_list);
        $attr_group_list = json_decode($this->attr);
        $sub_attr_num = false;
        foreach ($attr_group_list as $i => $attr_group) {
            $group_attr_id_list = [];
            foreach ($attr_group->attr_list as $attr)
                array_push($group_attr_id_list, $attr->attr_id);
            sort($group_attr_id_list);
            if (!array_diff($attr_id_list, $group_attr_id_list)) {
                if ($num > intval($attr_group_list[$i]->num))
                    return false;
                $attr_group_list[$i]->num = intval($attr_group_list[$i]->num) - $num;
                $sub_attr_num = true;
                break;
            }
        }
        if (!$sub_attr_num)
            return false;
        $this->attr = json_encode($attr_group_list, JSON_UNESCAPED_UNICODE);
        $this->save();
        return true;
    }

    /**
     * 库存增加操作
     */
    public static function numAddStatic($goods_id, $attr_id_list, $num)
    {
        $goods = Goods::findOne($goods_id);
        if (!$goods)
            return false;
        return $goods->numAdd($attr_id_list, $num);
    }

    /**
     * 库存减少操作
     */
    public static function numSubStatic($goods_id, $attr_id_list, $num)
    {
        $goods = Goods::findOne($goods_id);
        if (!$goods)
            return false;
        return $goods->numSub($attr_id_list, $num);
    }

    /**
     * 获取商品销量
     */
    public function getSalesVolume()
    {
        $res = OrderDetail::find()->alias('od')
            ->select('SUM(od.num) AS sales_volume')
            ->leftJoin(['o' => Order::tableName()], 'od.order_id=o.id')
            ->where(['od.is_delete' => 0, 'od.goods_id' => $this->id, 'o.is_delete' => 0, 'o.is_pay' => 1,])
            ->asArray()->one();
        return empty($res['sales_volume']) ? 0 : intval($res['sales_volume']);
    }

    /**
     * 减满
     */
    public function cutFull($goodsList)
    {
        // 将商品ID相同的商品合并
        $newGoodsList = [];
        foreach ($goodsList AS $row) {
            if (isset($newGoodsList[$row['goods_id']])) {
                $newGoodsList[$row['goods_id']]['num'] += $row['num'];
                $newGoodsList[$row['goods_id']]['weight'] += $row['weight'] * $row['num'];
                $newGoodsList[$row['goods_id']]['price'] += $row['price'] * $row['num'];
            } else {
                $newGoodsList[$row['goods_id']] = $row;
            }
        }
        $resGoodsList = [];
        foreach ($newGoodsList AS $val) {
            if ($val['full_cut']) {
                $full_cut = json_decode($val['full_cut'], true);
            } else {
                $full_cut = json_decode([
                    'pieces' => 0,
                    'forehead' => 0,
                ], true);
            }

            if ((empty($full_cut['pieces']) || $val['num'] < ($full_cut['pieces'] ?: 0)) && (empty($full_cut['forehead']) || $val['price'] < ($full_cut['forehead'] ?: 0))) {
                $resGoodsList[] = $val;
            }


//            if (empty($full_cut['pieces']) && empty($full_cut['forehead'])){
//                $resGoodsList[] = $val;
//                continue;
//            }
//            var_dump($val['num'] < ($full_cut['pieces']?:0));
//            var_dump($val['price'] < ($full_cut['forehead']?:0));
//            var_dump($val['price']);
//            var_dump($full_cut);
//            if ($val['num'] < ($full_cut['pieces']?:0) && $val['price'] < ($full_cut['forehead']?:0)){
//                $resGoodsList[] = $val;
//            }
        }
        return $resGoodsList;
    }

    public static function getGoodsCard($id = null)
    {
        if (!$id) {
            return [];
        }
        //商品卡券
        $goods_card_list = GoodsCard::find()->alias('gc')->where(['gc.is_delete' => 0, 'goods_id' => $id])
            ->leftJoin(Card::tableName() . ' c', 'c.id=gc.card_id')->select([
                'gc.card_id', 'c.name', 'c.pic_url', 'gc.goods_id', 'c.content'
            ])->asArray()->all();
        foreach ($goods_card_list as $index => $value) {
            $goods_card_list[$index]['id'] = $value['card_id'];
        }
        return $goods_card_list;
    }

    public function getAttrData()
    {
        if ($this->isNewRecord)
            return [];
        if (!$this->use_attr) {
            return [];
        }
        if (!$this->attr)
            return [];
        $attr_group_list = [];

        $attr_data = json_decode($this->attr, true);
        foreach ($attr_data as $i => $attr_data_item) {
            foreach ($attr_data[$i]['attr_list'] as $j => $attr_list) {
                $attr_group = $this->getAttrGroupByAttId($attr_data[$i]['attr_list'][$j]['attr_id']);
                if ($attr_group) {
                    $in_list = false;
                    foreach ($attr_group_list as $k => $exist_attr_group) {
                        if ($exist_attr_group['attr_group_name'] == $attr_group->attr_group_name) {
                            $attr_item = [
                                'attr_name' => $attr_data[$i]['attr_list'][$j]['attr_name'],
                            ];
                            if (!in_array($attr_item, $attr_group_list[$k]['attr_list']))
                                $attr_group_list[$k]['attr_list'][] = $attr_item;
                            $in_list = true;
                        }
                    }
                    if (!$in_list) {
                        $attr_group_list[] = [
                            'attr_group_name' => $attr_group->attr_group_name,
                            'attr_list' => [
                                [
                                    'attr_name' => $attr_data[$i]['attr_list'][$j]['attr_name'],
                                ],
                            ],
                        ];
                    }
                }
            }
        }
        return $attr_group_list;
    }

    public function getCheckedAttrData()
    {
        if ($this->isNewRecord)
            return [];
        if (!$this->use_attr) {
            return [];
        }
        if (!$this->attr)
            return [];
        $attr_data = json_decode($this->attr, true);
        foreach ($attr_data as $i => $attr_data_item) {
            if (!isset($attr_data[$i]['no']))
                $attr_data[$i]['no'] = '';
            if (!isset($attr_data[$i]['pic']))
                $attr_data[$i]['pic'] = '';
            foreach ($attr_data[$i]['attr_list'] as $j => $attr_list) {
                $attr_group = $this->getAttrGroupByAttId($attr_data[$i]['attr_list'][$j]['attr_id']);
                $attr_data[$i]['attr_list'][$j]['attr_group_name'] = $attr_group ? $attr_group->attr_group_name : null;
            }
        }
        return $attr_data;
    }

    private function getAttrGroupByAttId($att_id)
    {
        $cache_key = 'get_attr_group_by_attr_id_' . $att_id;
        $attr_group = Yii::$app->cache->get($cache_key);
        if ($attr_group)
            return $attr_group;
        $attr_group = AttrGroup::find()->alias('ag')
            ->leftJoin(['a' => Attr::tableName()], 'a.attr_group_id=ag.id')
            ->where(['a.id' => $att_id])
            ->one();
        if (!$attr_group)
            return $attr_group;
        Yii::$app->cache->set($cache_key, $attr_group, 10);
        return $attr_group;
    }

}
