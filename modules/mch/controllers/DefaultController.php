<?php

namespace app\modules\mch\controllers;

use app\extensions\Sms;


/**
 * Default controller for the `mch` module
 */
class DefaultController extends Controller
{

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->redirect(['store/index']);
    }
}
