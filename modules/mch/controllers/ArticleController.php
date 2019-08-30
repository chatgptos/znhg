<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/18
 * Time: 14:17
 */

namespace app\modules\mch\controllers;

use app\models\Article;
use app\modules\mch\models\Model;

class ArticleController extends Controller
{
    public function actionIndex($cat_id = 1)
    {
        $list = Article::find()->where([
            'store_id' => $this->store->id,
            'article_cat_id' => $cat_id,
            'is_delete' => 0,
        ])->orderBy('sort ASC,addtime DESC')->all();
        if (empty($list) && $cat_id == 1) {
            $item = new Article();
            $item->article_cat_id = 1;
            $item->title = '关于我们';
            $list[] = $item;
        }
        return $this->render('index', [
            'list' => $list,
            'cat_id' => $cat_id,
        ]);
    }

    public function actionEdit($cat_id, $id = null)
    {
        $model = Article::findOne([
            'store_id' => $this->store->id,
            'id' => $id,
            'article_cat_id' => $cat_id,
        ]);
        if (!$model) {
            $model = new Article();
            $model->article_cat_id = $cat_id;
            $model->store_id = $this->store->id;
        }
        if (\Yii::$app->request->isPost) {
            $model->attributes = \Yii::$app->request->post();
            $model->article_cat_id = $cat_id;
            $model->store_id = $this->store->id;
            if ($model->isNewRecord) {
                $model->addtime = time();
            }
            if ($model->save()) {
                $this->renderJson([
                    'code' => 0,
                    'msg' => '保存成功',
                ]);
            } else {
                $this->renderJson((new Model())->getModelError($model));
            }
        } else {
            return $this->render('edit', [
                'model' => $model,
            ]);
        }
    }

    public function actionDelete($id)
    {
        $model = Article::findOne([
            'id' => $id,
            'store_id' => $this->store->id,
        ]);
        if ($model) {
            $model->is_delete = 1;
            if (!$model->save()) {
                $this->renderJson((new Model())->getModelError($model));
            }
        }
        $this->renderJson([
            'code' => 0,
            'msg' => '删除成功',
        ]);
    }

}