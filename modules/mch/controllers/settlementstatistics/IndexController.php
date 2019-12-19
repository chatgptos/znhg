<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/22
 * Time: 14:58
 */

namespace app\modules\mch\controllers\settlementstatistics;


class IndexController extends Controller
{
    public function actionIndex()
    {
        return $this->redirect(\Yii::$app->urlManager->createUrl(['mch/settlementstatistics/goods/index']));
//        return $this->render('index');
    }
}