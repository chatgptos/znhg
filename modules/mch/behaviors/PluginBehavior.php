<?php


namespace app\modules\mch\behaviors;

/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/10/11
 * Time: 9:44
 */

use yii\base\Behavior;
use yii\web\Controller;

class PluginBehavior extends Behavior
{
    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction',
        ];
    }

    public function beforeAction($e)
    {

    }
}