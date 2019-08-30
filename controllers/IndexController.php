<?php

namespace app\controllers;

use yii\web\Controller;

class IndexController extends Controller
{

    /**
     * Displays homepage.
     * @return string
     */
    public function actionIndex()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];
    }

}
