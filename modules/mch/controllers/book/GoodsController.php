<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/12/12
 * Time: 11:06
 */

namespace app\modules\mch\controllers\book;


use app\models\PostageRules;
use app\models\YyCat;
use app\models\YyForm;
use app\models\YyGoods;
use app\modules\mch\models\book\YyCatForm;
use app\modules\mch\models\book\YyGoodsForm;

class GoodsController extends Controller
{
    /**
     * @return string
     * 预约分类列表
     */
    public function actionCat()
    {
        $form = new YyCatForm();
        $arr = $form->getList($this->store->id);
        return $this->render('cat',[
            'list'      => $arr[0],
            'pagination'=> $arr[1],
        ]);
    }
    /**
     * @param int $id
     * @return mixed|string
     * 预约分类编辑
     */
    public function actionCatEdit($id = 0)
    {
        $cat = YyCat::findOne(['id'=>$id,'is_delete'=>0,'store_id'=>$this->store->id]);
        if (!$cat){
            $cat = new YyCat();
        }
        if (\Yii::$app->request->isPost){
            $model = \Yii::$app->request->post('model');
            $model['store_id'] = $this->store->id;
            $form = new YyCatForm();
            $form->attributes = $model;
            $form->cat = $cat;
            return json_encode($form->save(),JSON_UNESCAPED_UNICODE);
        }
        return $this->render('cat-edit',[
            'list'  => $cat,
        ]);
    }

    /**
     * @param int $id
     * @return mixed|string
     * 预约分类删除
     */
    public function actionCatDel($id = 0)
    {
        $cat = YyCat::findOne(['id'=>$id,'is_delete'=>0,'store_id'=>$this->store->id]);
        if (!$cat){
            return json_encode([
                'code'  => 1,
                'msg'   => '分类不存在或已删除'
            ],JSON_UNESCAPED_UNICODE);
        }

        $cat->is_delete = 1;
        if ($cat->save()){
            return json_encode([
                'code'  => 0,
                'msg'   => '删除成功'
            ],JSON_UNESCAPED_UNICODE);
        }else{
            return json_encode([
                'code'   =>    1,
                'msg'    =>    '删除失败'
            ],JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * @return string
     * 商品列表
     */
    public function actionIndex()
    {
        $form = new YyGoodsForm();
        $arr = $form->getList($this->store->id);
        $cat_list = YyCat::find()->select('id,name')->andWhere(['store_id'=>$this->store->id,'is_delete'=>0])->orderBy('sort ASC')->asArray()->all();
        return $this->render('index',[
            'list'      => $arr[0],
            'pagination'=> $arr[1],
            'cat_list'  => $cat_list,
        ]);
    }

    /**
     * @param int $id
     * @return mixed|string
     * 编辑预约商品
     */
    public function actionGoodsEdit($id = 0)
    {
        $goods = YyGoods::findOne(['id'=>$id,'is_delete'=>0,'store_id'=>$this->store->id]);
        $form_list = YyForm::find()->where(['store_id'=>$this->store->id,'goods_id'=>$id,'is_delete'=>0])->orderBy(['sort'=>SORT_ASC])->asArray()->all();

        if (!$goods){
            $goods = new YyGoods();
        }
        if (\Yii::$app->request->isPost){
            $model = \Yii::$app->request->post('model');
//            var_dump($model);die();
            $model['store_id'] = $this->store->id;
            $form = new YyGoodsForm();
            $form->attributes = $model;
            $form->goods = $goods;
//            var_dump($model['form_list']);
//            $form->$form_list = $model['form_list'];
            return json_encode($form->save(),JSON_UNESCAPED_UNICODE);
        }
        $ptCat = YyCat::find()
            ->andWhere(['is_delete'=>0,'store_id'=>$this->store->id])
            ->asArray()
            ->orderBy('sort ASC')
            ->all();
        return $this->render('goods-edit',[
            'goods'  => $goods,
            'cat'   => $ptCat,
            'form_list' => json_encode($form_list,JSON_UNESCAPED_UNICODE),
        ]);
    }

    /**
     * @param int $id
     * @param string $type
     * 上架、下架
     */
    public function actionGoodsUpDown($id = 0, $type = 'down')
    {
        if ($type == 'down') {
            $goods = YyGoods::findOne(['id' => $id, 'is_delete' => 0, 'status' => 1, 'store_id' => $this->store->id]);
            if (!$goods) {
                $this->renderJson([
                    'code' => 1,
                    'msg' => '商品已删除或已下架'
                ]);
            }
            $goods->status = 2;
        } elseif ($type == 'up') {
            $goods = YyGoods::findOne(['id' => $id, 'is_delete' => 0, 'status' => 2, 'store_id' => $this->store->id]);

            if (!$goods) {
                $this->renderJson([
                    'code' => 1,
                    'msg' => '商品已删除或已上架'
                ]);
            }
            $goods->status = 1;
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
     * 拼团商品批量操作
     */
    public function actionBatch()
    {
        $get = \Yii::$app->request->get();
        $res = 0;
        $goods_group = $get['goods_group'];
        $goods_id_group = [];
        foreach ($goods_group as $index => $value) {
                array_push($goods_id_group,$value);
        }

        $condition = ['and', ['in', 'id', $goods_id_group], ['store_id' => $this->store->id]];
        if ($get['type'] == 0) { //批量上架
            $res = YyGoods::updateAll(['status' => 1], $condition);
        } elseif ($get['type'] == 1) {//批量下架
            $res = YyGoods::updateAll(['status' => 0], $condition);
        } elseif ($get['type'] == 2) {//批量删除
            $res = YyGoods::updateAll(['is_delete' => 1], $condition);
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
        $goods = YyGoods::findOne(['id' => $id, 'is_delete' => 0, 'store_id' => $this->store->id]);
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



}