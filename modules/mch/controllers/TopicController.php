<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/9/27
 * Time: 9:50
 */

namespace app\modules\mch\controllers;


use app\models\Goods;
use app\models\Topic;
use app\models\Video;
use app\modules\mch\models\TopicEditForm;
use yii\data\Pagination;

class TopicController extends Controller
{
    public function actionIndex()
    {
        $query = Topic::find()->where(['store_id' => $this->store->id, 'is_delete' => 0]);
        $count = $query->count();
        $pagination = new Pagination([
            'totalCount' => $count,
        ]);
        $list = $query->orderBy('sort ASC,addtime DESC')->limit($pagination->limit)->offset($pagination->offset)->all();
        return $this->render('index', [
            'list' => $list,
            'pagination' => $pagination,
        ]);
    }

    public function actionEdit($id = null)
    {
        $model = Topic::findOne([
            'id' => $id,
            'store_id' => $this->store->id,
            'is_delete' => 0,
        ]);
        if (!$model) {
            $model = new Topic();
            $model->store_id = $this->store->id;
        }
        if (\Yii::$app->request->isPost) {
            $form = new TopicEditForm();
            $form->attributes = \Yii::$app->request->post();
            $form->model = $model;
            $this->renderJson($form->save());
        } else {
            return $this->render('edit', [
                'model' => $model,
            ]);
        }
    }

    public function actionDelete($id)
    {
        $model = Topic::findOne([
            'id' => $id,
            'store_id' => $this->store->id,
            'is_delete' => 0,
        ]);
        if ($model) {
            $model->is_delete = 1;
            $model->save();
        }
        return $this->renderJson([
            'code' => 0,
            'msg' => '操作成功！',
        ]);
    }

    public function actionSearchGoods($keyword = null)
    {
        $query = Goods::find()->where([
            'store_id' => $this->store->id,
            'is_delete' => 0,
        ]);
        if ($keyword)
            $query->andWhere(['LIKE', 'name', $keyword]);
        $list = $query->orderBy('sort ASC,addtime DESC')->limit(10)->all();
        $new_list = [];
        foreach ($list as $item) {
            $new_list[] = [
                'id' => $item->id,
                'name' => $item->name,
                'price' => $item->price,
                'cover_pic' => $item->getGoodsCover(),
            ];
        }
        return $this->renderJson([
            'code' => 0,
            'data' => [
                'list' => $new_list,
            ],
        ]);
    }

    public function actionSearchVideo($keyword = null)
    {
        $query = Video::find()->where([
            'store_id' => $this->store->id,
            'is_delete' => 0,
        ]);
        if ($keyword)
            $query->andWhere(['LIKE', 'title', $keyword]);
        $list = $query->orderBy('sort ASC,addtime DESC')->limit(10)->all();
        $new_list = [];
        foreach ($list as $item) {
            $new_list[] = [
                'id' => $item->id,
                'name' => $item->title,
                'src' => $item->url,
                'cover_pic' => $item->pic_url,
            ];
        }
        return $this->renderJson([
            'code' => 0,
            'data' => [
                'list' => $new_list,
            ],
        ]);
    }

}