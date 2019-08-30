<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/10/11
 * Time: 9:38
 */

namespace app\modules\mch\controllers;


use app\models\Goods;
use app\models\GoodsSearchForm;
use app\models\Seckill;
use app\models\SeckillGoods;
use app\modules\mch\behaviors\PluginBehavior;
use app\modules\mch\models\SeckillCalendar;
use app\modules\mch\models\SeckillDateForm;
use app\modules\mch\models\SeckillGoodsEditForm;
use yii\helpers\VarDumper;

class SeckillController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'plugin' => [
                'class' => PluginBehavior::className(),
            ],
        ]);
    }

    public function actionIndex()
    {
        $model = Seckill::findOne([
            'store_id' => $this->store->id,
        ]);
        if (!$model) {
            $model = new Seckill();
            $model->store_id = $this->store->id;
        }
        if (\Yii::$app->request->isPost) {
            $model->open_time = json_encode((array)\Yii::$app->request->post('open_time', []), JSON_UNESCAPED_UNICODE);
            $model->save();
            $this->renderJson([
                'code' => 0,
                'msg' => '保存成功',
            ]);
        } else {
            return $this->render('index', [
                'model' => $model,
            ]);
        }
    }

    public function actionGoods()
    {
        $list = SeckillGoods::find()->alias('mg')
            ->leftJoin(['g' => Goods::tableName()], 'g.id=mg.goods_id')
            ->where(['mg.store_id' => $this->store->id, 'mg.is_delete' => 0, 'g.is_delete' => 0,])->groupBy('mg.goods_id')
            ->select('g.name,mg.*,COUNT(mg.goods_id) seckill_count')->asArray()->all();
        return $this->render('goods', [
            'list' => $list,
        ]);
    }

    public function actionGoodsEdit()
    {
        $model = new SeckillGoods();
        $seckill = Seckill::findOne([
            'store_id' => $this->store->id,
        ]);
        if (!$seckill) {
            $seckill = new Seckill();
            $seckill->store_id = $this->store->id;
            $seckill->open_time = "[]";
        }
        if (\Yii::$app->request->isPost) {
            $form = new SeckillGoodsEditForm();
            $form->attributes = \Yii::$app->request->post();
            $form->store_id = $this->store->id;
            return $this->renderJson($form->save());
        } else {
            return $this->render('goods-edit', [
                'model' => $model,
                'seckill' => $seckill,
            ]);
        }
    }

    public function actionGoodsSearch($keyword = null, $page = 1)
    {
        $form = new GoodsSearchForm();
        $form->keyword = $keyword;
        $form->page = $page;
        $form->store_id = $this->store->id;
        $this->renderJson($form->search());
    }

    public function actionGoodsDetail($goods_id)
    {
        $date_begin = \Yii::$app->request->get('date_begin', date('Y-m-d', strtotime('-30 days')));
        $date_end = \Yii::$app->request->get('date_end', date('Y-m-d'));
        $query = SeckillGoods::find()->alias('mg')->leftJoin(['g' => Goods::tableName()], 'mg.goods_id=g.id')
            ->where(['mg.goods_id' => $goods_id, 'mg.is_delete' => 0])->asArray()->select('mg.*,g.name')->orderBy('mg.open_date ASC,mg.start_time ASC');

        $query->andWhere([
            'AND',
            ['>=', 'mg.open_date', $date_begin],
            ['<=', 'mg.open_date', $date_end],
        ]);
        $count = $query->count();
        $list = $query->all();
        return $this->render('goods-detail', [
            'list' => $list,
            'count' => $count ? $count : 0,
            'date_begin' => $date_begin,
            'date_end' => $date_end,
        ]);
    }

    //删除单个秒杀记录
    public function actionSeckillDelete($id)
    {
        SeckillGoods::updateAll(['is_delete' => 1], [
            'id' => $id,
            'store_id' => $this->store->id,
        ]);
        $this->renderJson([
            'code' => 0,
            'msg' => '操作成功',
        ]);
    }

    //删除该商品的所有秒杀记录
    public function actionGoodsDelete($goods_id)
    {
        SeckillGoods::updateAll(['is_delete' => 1], [
            'goods_id' => $goods_id,
            'store_id' => $this->store->id,
        ]);
        $this->renderJson([
            'code' => 0,
            'msg' => '操作成功',
        ]);
    }

    //秒杀商品（日历视图）
    public function actionCalendar()
    {
        if (\Yii::$app->request->isAjax) {
            $form = new SeckillCalendar();
            $form->attributes = \Yii::$app->request->get();
            $form->store_id = $this->store->id;
            $res = $form->search();
            $this->renderJson($res);
        } else {
            return $this->render('calendar', [
            ]);
        }
    }

    //秒杀日期商品列表
    public function actionDate()
    {
        $form = new SeckillDateForm();
        $form->attributes = \Yii::$app->request->get();
        $form->store_id = $this->store->id;
        $res = $form->search();
        $this->layout = false;
        $this->renderJson([
            'code' => 0,
            'data' => [
                'title' => $res['data']['date'] . '秒杀安排表',
                'content' => $this->render('date', $res['data']),
            ],
        ]);
    }
}