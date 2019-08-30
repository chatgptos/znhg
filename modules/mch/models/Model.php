<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/6/26
 * Time: 14:37
 */

namespace app\modules\mch\models;


use xanyext\wechat\Wechat;

class Model extends \app\models\Model
{
    /**
     * @return Wechat
     */
    public function getWechat()
    {
        return empty(\Yii::$app->controller->wechat) ? null : \Yii::$app->controller->wechat;
    }
}