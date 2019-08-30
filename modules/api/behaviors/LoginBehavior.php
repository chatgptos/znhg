<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/6/27
 * Time: 1:05
 */

namespace app\modules\api\behaviors;


use yii\base\Behavior;
use yii\web\Controller;

class LoginBehavior extends Behavior
{
    public $actions;

    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction',
        ];
    }

    public function beforeAction($e)
    {
        $e->action->id;

        $access_token = \Yii::$app->request->get('access_token');
        if (!$access_token) {
            $access_token = \Yii::$app->request->post('access_token');
        }
        if (!$access_token) {
            $e->action->controller->renderJson([
                'code' => -1,
                'msg' => 'access_token 不能为空',
            ]);
        }
        if (\Yii::$app->user->loginByAccessToken($access_token))
            return true;
        else
            $e->action->controller->renderJson([
                'code' => -1,
                'msg' => '登录失败',
            ]);
    }
}