<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/6/14
 * Time: 9:36
 */

namespace app\models;


class Model extends \yii\base\Model
{
    public function getModelError($model = null)
    {
        if (!$model)
            $model = $this;
        foreach ($model->errors as $errors)
            return [
                'code' => 1,
                'msg' => $errors[0],
            ];
    }
}