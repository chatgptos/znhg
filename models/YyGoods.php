<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%yy_goods}}".
 *
 * @property string $id
 * @property string $name
 * @property string $price
 * @property string $original_price
 * @property string $detail
 * @property string $cat_id
 * @property integer $status
 * @property string $service
 * @property string $sort
 * @property string $virtual_sales
 * @property string $cover_pic
 * @property string $addtime
 * @property integer $is_delete
 * @property string $sales
 * @property string $shop_id
 * @property string $store_id
 */
class YyGoods extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%yy_goods}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'price', 'original_price', 'detail', 'service', 'store_id'], 'required'],
            [['price', 'original_price'], 'number'],
            [['detail', 'cover_pic'], 'string'],
            [['cat_id', 'status', 'sort', 'virtual_sales', 'addtime', 'is_delete', 'sales', 'store_id'], 'integer'],
            [['name','shop_id'], 'string', 'max' => 255],
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
            'name' => '商品名称',
            'price' => '预约金额',
            'original_price' => '原价',
            'detail' => '商品详情，图文',
            'cat_id' => '商品分类',
            'status' => '上架状态【1=> 上架，2=> 下架】',
            'service' => '服务选项',
            'sort' => '商品排序 升序',
            'virtual_sales' => '虚拟销量',
            'cover_pic' => '商品缩略图',
            'addtime' => '添加时间',
            'is_delete' => '是否删除',
            'sales' => '实际销量',
            'shop_id' => '门店id',
            'store_id' => 'Store ID',
        ];
    }

    /**
     * @return static[]
     * 商品图集
     */
    public function goodsPicList()
    {
        return YyGoodsPic::findAll(['goods_id'=>$this->id,'is_delete'=>0]);
    }
}
