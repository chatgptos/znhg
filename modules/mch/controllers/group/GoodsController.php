<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/22
 * Time: 9:29
 */

namespace app\modules\mch\controllers\group;


use app\models\Attr;
use app\models\AttrGroup;
use app\models\Goods;
use app\models\GoodsPic;
use app\models\PostageRules;
use app\models\PtCat;
use app\models\PtGoods;
use app\modules\mch\models\group\PtCatForm;
use app\modules\mch\models\group\PtGoodsForm;
use yii\web\HttpException;

class GoodsController extends Controller
{
    /**
     * @return string
     * 商品列表
     */
    public function actionIndex()
    {
        $form = new PtGoodsForm();
        $arr = $form->getList($this->store->id);
        $cat_list = PtCat::find()->select(['id', 'name'])
            ->andWhere(['store_id' => $this->store->id, 'is_delete' => 0])
            ->orderBy('sort ASC')->asArray()->all();
        return $this->render('index', [
            'list' => $arr[0],
            'pagination' => $arr[1],
            'cat_list' => $cat_list,
        ]);
    }

    /**
     * @return string
     * 获取分类列表
     */
    public function actionCat()
    {
        $form = new PtCatForm();
        $arr = $form->getList($this->store->id);
        return $this->render('cat', [
            'list' => $arr[0],
            'pagination' => $arr[1],
        ]);
    }

    /**
     * @param int $id
     * @return mixed|string
     * 修改拼团商品分类
     */
    public function actionCatEdit($id = 0)
    {
        $cat = PtCat::findOne(['id' => $id, 'is_delete' => 0, 'store_id' => $this->store->id]);
        if (!$cat) {
            $cat = new PtCat();
        }
        if (\Yii::$app->request->isPost) {
            $model = \Yii::$app->request->post('model');
            $model['store_id'] = $this->store->id;
            $form = new PtCatForm();
            $form->attributes = $model;
            $form->cat = $cat;
            return json_encode($form->save(), JSON_UNESCAPED_UNICODE);
        }
        return $this->render('cat-edit', [
            'list' => $cat,
        ]);
    }

    /**
     * @param int $id
     * @return mixed|string
     * 拼团商品分类删除
     */
    public function actionCatDel($id = 0)
    {
        $cat = PtCat::findOne(['id' => $id, 'is_delete' => 0, 'store_id' => $this->store->id]);
        if (!$cat) {
            return json_encode([
                'code' => 1,
                'msg' => '分类不存在或已删除'
            ], JSON_UNESCAPED_UNICODE);
        }

        $cat->is_delete = 1;
        if ($cat->save()) {
            return json_encode([
                'code' => 0,
                'msg' => '删除成功'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode([
                'code' => 1,
                'msg' => '删除失败'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * @param int $id
     * @return mixed|string
     * 编辑拼团商品
     */
    public function actionGoodsEdit($id = 0)
    {
        $goods = PtGoods::findOne(['id' => $id, 'is_delete' => 0, 'store_id' => $this->store->id]);
        if (!$goods) {
            $goods = new PtGoods();
        }
        if (\Yii::$app->request->isPost) {
            $model = \Yii::$app->request->post('model');
            $model['store_id'] = $this->store->id;
            $model['limit_time'] = $model['limit_time'] ? strtotime($model['limit_time']) : 0;
            $form = new PtGoodsForm();
            $form->attributes = $model;
            $mall_id = \Yii::$app->request->post('mall_id');
            if (empty($mall_id)) {
                $form->attr = \Yii::$app->request->post('attr');
            } else {
                $form->attr = \Yii::$app->request->post('mall_attr');
            }
            $form->mall_id = \Yii::$app->request->post('mall_id');
            $form->goods = $goods;
            return json_encode($form->save(), JSON_UNESCAPED_UNICODE);
        }

        $cat_list = PtCat::find()->select(['id', 'name'])
            ->andWhere(['store_id' => $this->store->id, 'is_delete' => 0])
            ->orderBy('sort ASC')->asArray()->all();

        $postageRiles = PostageRules::find()->where(['store_id' => $this->store->id, 'is_delete' => 0])->all();
        return $this->render('goods-edit', [
            'goods' => $goods,
            'cat_list' => $cat_list,
            'postageRiles' => $postageRiles,
        ]);
    }


    /**
     * @param int $id
     * @param string $type
     * 上架、下架、设置热销、取消热销
     */
    public function actionGoodsUpDown($id = 0, $type = 'down')
    {
        if ($type == 'down') {
            $goods = PtGoods::findOne(['id' => $id, 'is_delete' => 0, 'status' => 1, 'store_id' => $this->store->id]);
            if (!$goods) {
                $this->renderJson([
                    'code' => 1,
                    'msg' => '商品已删除或已下架'
                ]);
            }
            $goods->status = 2;
        } elseif ($type == 'up') {
            $goods = PtGoods::findOne(['id' => $id, 'is_delete' => 0, 'status' => 2, 'store_id' => $this->store->id]);

            if (!$goods) {
                $this->renderJson([
                    'code' => 1,
                    'msg' => '商品已删除或已上架'
                ]);
            }
            if (!PtGoods::getNum($goods->id)) {
                $this->renderJson([
                    'code' => 1,
                    'msg' => '商品库存不足，请先完善商品库存',
                    'return_url' => \Yii::$app->urlManager->createUrl(['mch/group/goods/goods-attr', 'id' => $goods->id]),
                ]);
            }
            $goods->status = 1;
        } elseif ($type == 'hot') { // 设置热销
            $goods = PtGoods::findOne(['id' => $id, 'is_delete' => 0, 'is_hot' => 0, 'store_id' => $this->store->id]);
            if (!$goods) {
                $this->renderJson([
                    'code' => 1,
                    'msg' => '商品已删除或已设为热销'
                ]);
            }
            if (!PtGoods::getNum($goods->id)) {
                $this->renderJson([
                    'code' => 1,
                    'msg' => '商品库存不足，请先完善商品库存',
                    'return_url' => \Yii::$app->urlManager->createUrl(['mch/group/goods/goods-attr', 'id' => $goods->id]),
                ]);
            }
            $goods->is_hot = 1;
        } elseif ($type == 'nohot') {   // 取消热销
            $goods = PtGoods::findOne(['id' => $id, 'is_delete' => 0, 'is_hot' => 1, 'store_id' => $this->store->id]);
            if (!$goods) {
                $this->renderJson([
                    'code' => 1,
                    'msg' => '商品已删除或已取消热销'
                ]);
            }
            $goods->is_hot = 0;
        } else {
            $this->renderJson([
                'code' => 1,
                'msg' => '参数错误',
            ]);
        }
        if ($goods->save()) {
            $this->renderJson([
                'code' => 0,
                'msg' => '成功'
            ]);
        } else {
            foreach ($goods->errors as $errors) {
                $this->renderJson([
                    'code' => 1,
                    'msg' => $errors[0],
                ]);
            }
        }
    }


    /**
     * @param int $id 商品id
     *  商品规格库存管理
     */
    public function actionGoodsAttr($id)
    {
        $goods = PtGoods::findOne([
            'store_id' => $this->store->id,
            'is_delete' => 0,
            'id' => $id,
        ]);
        if (!$goods)
            throw new HttpException(404);
        if (\Yii::$app->request->isPost) {
            $goods->attr = json_encode(\Yii::$app->request->post('attr', []), JSON_UNESCAPED_UNICODE);
            if ($goods->save()) {
                $this->renderJson([
                    'code' => 0,
                    'msg' => '保存成功',
                ]);
            } else {
                $this->renderJson([
                    'code' => 1,
                    'msg' => '保存失败',
                ]);
            }
        } else {
            $attr_group_list = AttrGroup::find()
                ->select('id attr_group_id,attr_group_name')
                ->where(['store_id' => $this->store->id, 'is_delete' => 0])
                ->asArray()->all();
            foreach ($attr_group_list as $i => $g) {
                $attr_list = Attr::find()
                    ->select('id attr_id,attr_name')
                    ->where(['attr_group_id' => $g['attr_group_id'], 'is_delete' => 0])
                    ->asArray()->all();
                if (empty($attr_list))
                    unset($attr_group_list[$i]);
                else {
                    $goods_attr_list = json_decode($goods->attr, true);
                    if (!$goods_attr_list)
                        $goods_attr_list = [];
                    foreach ($attr_list as $j => $attr) {
                        $checked = false;
                        foreach ($goods_attr_list as $goods_attr) {
                            foreach ($goods_attr['attr_list'] as $g_attr) {
                                if ($attr['attr_id'] == $g_attr['attr_id']) {
                                    $checked = true;
                                    break;
                                }
                            }
                            if ($checked)
                                break;
                        }
                        $attr_list[$j]['checked'] = $checked;
                    }
                    $attr_group_list[$i]['attr_list'] = $attr_list;
                }
            }
            $new_attr_group_list = [];
            foreach ($attr_group_list as $item)
                $new_attr_group_list[] = $item;
            return $this->render('goods-attr', [
                'goods' => $goods,
                'attr_group_list' => $new_attr_group_list,
                'checked_attr_list' => $goods->attr,
            ]);
        }
    }

    /**
     * 拼团商品批量操作
     */
    public function actionBatch()
    {
        $get = \Yii::$app->request->get();
        $res = 0;
        $goods_group = $get['goods_group'];
        $goods_id_group = [];
        foreach ($goods_group as $index => $value) {
            if ($get['type'] == 0) {
                if ($value['num'] != 0) {
                    array_push($goods_id_group, $value['id']);
                }
            } else {
                array_push($goods_id_group, $value['id']);
            }
        }


        $condition = ['and', ['in', 'id', $goods_id_group], ['store_id' => $this->store->id]];
        if ($get['type'] == 0) { //批量上架
            $res = PtGoods::updateAll(['status' => 1], $condition);
        } elseif ($get['type'] == 1) {//批量下架
            $res = PtGoods::updateAll(['status' => 0], $condition);
        } elseif ($get['type'] == 2) {//批量删除
            $res = PtGoods::updateAll(['is_delete' => 1], $condition);
        } elseif ($get['type'] == 3) {//批量设置热销
            $res = PtGoods::updateAll(['is_hot' => 1], $condition);
        } elseif ($get['type'] == 4) {//批量取消热销
            $res = PtGoods::updateAll(['is_hot' => 0], $condition);
        }
        if ($res > 0) {
            $this->renderJson([
                'code' => 0,
                'msg' => 'success'
            ]);
        } else {
            $this->renderJson([
                'code' => 1,
                'msg' => 'fail'
            ]);
        }
    }

    /**
     * @param int $id
     * 拼团商品删除（逻辑删除）
     */
    public function actionGoodsDel($id = 0)
    {
        $goods = PtGoods::findOne(['id' => $id, 'is_delete' => 0, 'store_id' => $this->store->id]);
        if (!$goods) {
            $this->renderJson([
                'code' => 1,
                'msg' => '商品删除失败或已删除'
            ]);
        }
        $goods->is_delete = 1;
        if ($goods->save()) {
            $this->renderJson([
                'code' => 0,
                'msg' => '成功'
            ]);
        } else {
            foreach ($goods->errors as $errors) {
                $this->renderJson([
                    'code' => 1,
                    'msg' => $errors[0],
                ]);
            }
        }
    }

    /**
     * @param int $mall_id
     * 拉取商城商品数据
     */
    public function actionCopy($mall_id = 0)
    {
        $goods = Goods::findOne(['id' => $mall_id, 'is_delete' => 0, 'store_id' => $this->store->id]);

//        $goods->getAttrGroupList();
        if (!$goods) {
            $this->renderJson([
                'code' => 1,
                'msg' => '商品不存在,或已删除',
            ]);
        }

        $goodsPic = GoodsPic::find()->select('pic_url')->andWhere(['goods_id' => $goods->id, 'is_delete' => 0])->asArray()->column();

        $this->renderJson([
            'code' => 0,
            'msg' => '成功',
            'data' => [
                'name' => $goods->name,
                'virtual_sales' => $goods->virtual_sales,
                'original_price' => $goods->original_price,
                'price' => $goods->price,
                'pic' => $goodsPic,
                'cover_pic' => $goods->cover_pic,
                'unit' => $goods->unit,
                'weight' => $goods->weight,
                'detail' => $goods->detail,
                'service' => $goods->service,
                'sort' => $goods->sort,
                'freight' => $goods->freight,
                'attr_group_list' => $goods->getAttrGroupList(),
                'checked_attr_list' => json_decode($goods->attr),
                'attr' => $goods->attr,
            ],
        ]);

    }

}