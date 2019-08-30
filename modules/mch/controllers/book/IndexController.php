<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/22
 * Time: 14:58
 */

namespace app\modules\mch\controllers\book;


use app\models\YySetting;

class IndexController extends Controller
{
    public function actionIndex()
    {
        return $this->redirect(\Yii::$app->urlManager->createUrl(['mch/book/goods/index']));
//        return $this->render('index');
    }

    /**
     * @return string
     *
     */
    public function actionSetting()
    {
        $setting = YySetting::findOne(['store_id'=>$this->store->id]);
        if (!$setting){
            $setting = new YySetting();
        }
        if (\Yii::$app->request->isPost){
            $model = \Yii::$app->request->post('model');
            if ($setting->isNewRecord){
                $setting->store_id = $this->store->id;
            }
            $setting->cat = $model['cat'];
            if ($setting->save()){
                $this->renderJson([
                    'code'  => 0,
                    'msg'   => '保存成功',
                ]);
            }else{
                $this->renderJson([
                    'code'  => 0,
                    'msg'   => '保存失败，请重试',
                ]);
            }
        }
        return $this->render('setting',[
            'setting'  => $setting,
        ]);


    }
}