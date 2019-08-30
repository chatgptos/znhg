<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/22
 * Time: 17:35
 */

namespace app\modules\mch\models\group;


use app\models\Attr;
use app\models\AttrGroup;
use app\models\PtGoods;
use app\models\PtGoodsPic;
use app\modules\mch\models\Model;
use yii\data\Pagination;

class PtGoodsForm extends Model
{
    public $goods;

    public $goods_pic_list;

    public $name;
    public $store_id;
    public $original_price;
    public $price;
    public $detail;
    public $cat_id;

    public $grouptime;
    public $attr;
    public $service;
    public $sort;
    public $virtual_sales;
    public $cover_pic;
    public $weight;
    public $freight;
    public $unit;
    public $group_num;
    public $limit_time;
    public $is_only;
    public $is_more;
    public $colonel;
    public $buy_limit;
    public $type;

    public $mall_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['store_id', 'name', 'original_price', 'price', 'detail', 'group_num', 'grouptime', 'cat_id'], 'required'],
            [['store_id', 'cat_id', 'grouptime', 'sort', 'virtual_sales', 'weight', 'freight', 'group_num', 'limit_time', 'is_only', 'is_more', 'buy_limit', 'type'], 'integer'],
            [['original_price', 'price', 'colonel'], 'number'],
            [['detail', 'cover_pic'], 'string'],
            [['attr','goods_pic_list',], 'safe',],
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
            'original_price' => '商品原价',
            'price' => '团购价',
            'detail' => '商品详情，图文',
            'cat_id' => '商品分类',
            'status' => '上架状态【1=> 上架，2=> 下架】',
            'grouptime' => '拼团时间/小时',
            'attr' => '规格的库存及价格',
            'service' => '服务选项',
            'sort' => '商品排序 升序',
            'virtual_sales' => '虚拟销量',
            'cover_pic' => '商品缩略图',
            'weight' => '重量',
            'freight' => '运费模板ID',
            'unit' => '单位',
            'addtime' => '添加时间',
            'is_delete' => '是否删除',
            'group_num' => '商品成团数',
            'goods_pic_list' => '商品图集',
            'is_only' => '是否允许单独购买',
            'limit_time' => '拼团限时',
            'is_more' => '是否允许多件购买',
            'colonel' => '团长优惠',
            'buy_limit' => '限购数量',
            'type' => '商品支持送货类型',
        ];
    }

    /**
     * @param $store_id
     * @return array
     * 商品列表
     */
    public function getList($store_id)
    {
        $query = PtGoods::find()
            ->alias('g')
            ->andWhere(['g.is_delete' => 0, 'g.store_id' => $store_id])
            ->select(['g.*', 'c.name AS cname'])
            ->leftJoin('{{%pt_cat}} c', 'g.cat_id=c.id');
        $cat = \Yii::$app->request->get('cat');
        if ($cat){
            $query->andWhere(['cat_id'=>$cat]);
        }
        $keyword = \Yii::$app->request->get('keyword');
        if (trim($keyword)) {
            $query->andWhere([
                'OR',
                ['LIKE', 'g.name', $keyword],
                ['LIKE', 'c.name', $keyword],
            ]);
        }
        $count = $query->count();
        $p = new Pagination(['totalCount' => $count, 'pageSize' => 20]);

        $list = $query
            ->orderBy('g.sort ASC')
            ->offset($p->offset)
            ->asArray()
            ->limit($p->limit)
            ->all();
        foreach ($list AS $key => $goods){
            $list[$key]['num'] = PtGoods::getNum($goods['id']);
        }
        return [$list, $p];
    }

    /**
     * @return array
     * 商品编辑
     */
    public function save()
    {
        if ($this->validate()) {
            if (!is_array($this->goods_pic_list) || empty($this->goods_pic_list) || count($this->goods_pic_list) == 0)
                return [
                    'code' => 1,
                    'msg' => '商品图片不能为空',
                ];
            if (!$this->original_price)
                $this->original_price = $this->price;

            if (!$this->virtual_sales)
                $this->virtual_sales = 0;

            $goods = $this->goods;
            if ($goods->isNewRecord) {
                $goods->is_delete = 0;
                $goods->is_hot = 0;
                $goods->addtime = time();
                $goods->status = 2;
                $goods->attr = json_encode([], JSON_UNESCAPED_UNICODE);
            }

//            $this->full_cut = json_encode($this->full_cut,JSON_UNESCAPED_UNICODE);
//            if (!isset($this->integral['more'])){
//                $this->integral['more'] = '';
//            }

//            $this->integral = json_encode($this->integral,JSON_UNESCAPED_UNICODE);

            $_this_attributes = $this->attributes;

            if (empty($this->mall_id)){
                unset($_this_attributes['attr']);
            }
            $goods->attributes = $_this_attributes;
            if ($goods->save()) {
                PtGoodsPic::updateAll(['is_delete' => 1], ['goods_id' => $goods->id]);
                foreach ($this->goods_pic_list as $pic_url) {
                    $goods_pic = new PtGoodsPic();
                    $goods_pic->goods_id = $goods->id;
                    $goods_pic->pic_url = $pic_url;
                    $goods_pic->is_delete = 0;
                    $goods_pic->save();
                }
                if (empty($this->mall_id)){
                    $this->setAttr($goods);
                }

                return [
                    'code' => 0,
                    'msg' => '保存成功',
                ];
            } else {
                return $this->getModelError($goods);
            }
        } else {
            return $this->getModelError();
        }
    }

    /**
     * @param Goods $goods
     * 商品设置规格
     */
    private function setAttr($goods)
    {
        if (empty($this->attr) || !is_array($this->attr))
            return;
        $new_attr = [];
        foreach ($this->attr as $i => $item) {
            $new_attr_item = [
                'attr_list' => [],
                'num' => intval($item['num']),
                'price' => doubleval($item['price']),
                'no' => doubleval($item['no']),
            ];
            foreach ($item['attr_list'] as $a) {
                $attr_group_model = AttrGroup::findOne(['store_id' => $this->store_id, 'attr_group_name' => $a['attr_group_name'], 'is_delete' => 0]);
                if (!$attr_group_model) {
                    $attr_group_model = new AttrGroup();
                    $attr_group_model->attr_group_name = $a['attr_group_name'];
                    $attr_group_model->store_id = $this->store_id;
                    $attr_group_model->is_delete = 0;
                    $attr_group_model->save();
                }
                $attr_model = Attr::findOne(['attr_group_id' => $attr_group_model->id, 'attr_name' => $a['attr_name'], 'is_delete' => 0]);
                if (!$attr_model) {
                    $attr_model = new Attr();
                    $attr_model->attr_name = $a['attr_name'];
                    $attr_model->attr_group_id = $attr_group_model->id;
                    $attr_model->is_delete = 0;
                    $attr_model->save();
                }
                $new_attr_item['attr_list'][] = [
                    'attr_id' => $attr_model->id,
                    'attr_name' => $attr_model->attr_name,
                ];
            }
            $new_attr[] = $new_attr_item;
        }
        $goods->attr = json_encode($new_attr, JSON_UNESCAPED_UNICODE);
        $goods->save();
    }



}