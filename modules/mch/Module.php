<?php

namespace app\modules\mch;

/**
 * mch module definition class
 */
class Module extends \yii\base\Module
{
    public $layout = 'main';

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\mch\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
