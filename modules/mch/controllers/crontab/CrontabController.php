<?php

namespace app\modules\mch\controllers\crontab;

use app\models\StoreUser;
use app\modules\mch\models\BusinessListForm;
use app\modules\mch\models\crontab\DailyData;
use app\modules\mch\models\crontab\Stock;
use app\modules\mch\models\StoreDataForm;
use Yii;
use app\modules\mch\models\StoreUserForm;

/**
 * 商城后台账户
 * Class AccountController
 * @package app\modules\mch\controllers
 */
class CrontabController extends Controller
{
    /**
     * 账户设置
     * @return array|bool|string
     * @throws \yii\base\Exception
     */
    public function actionIndex()
    {
        $identity = Yii::$app->store->identity;
        if (Yii::$app->request->isPost) {
            $form = new StoreUserForm;
            $form->user_id = $identity->user_id;
            return $this->renderJson($form->update(Yii::$app->request->post()));
        } else {
            return $this->render('index', ['model' => $identity]);
        }
    }


    /**
     * 账户设置
     * @return array|bool|string
     * @throws \yii\base\Exception
     */
    public function actionAdd()
    {

        $StoreDataForm = new StoreDataForm();
        $StoreDataForm->store_id = $this->store->id;
        $store_data = $StoreDataForm->search();
        $DailyData = new DailyData();
        $DailyData->store_id =$this->store->id;
        $DailyData->statistics_date = date("Y-m-d");
        $DailyData->addtime = time();
        $data = DailyData::findOne(['statistics_date'=>date("Y-m-d")]);
        if($data){
            if($data->addtime && $data->addtime > strtotime(date("Y-m-d"),time())){
                //如果大于时间戳
            echo '已经存在';
            echo ( date('Y-m-d ',$data->addtime ));;
//            echo (strtotime(date("Y-m-d"),time()));
//            echo '<pre/>';
            echo ($data->statistics_date."\n");
//            var_dump($data);
            die;
            }
        }
        $DailyData->is_delete =0;
        $DailyData->user_count = $store_data['data']['panel_1']['user_count'];
        $DailyData->coupon_count = $store_data['data']['panel_1']['coupon_count'];
        $DailyData->integral_count =$store_data['data']['panel_1']['integral_count'];
        $DailyData->hld_count =$store_data['data']['panel_1']['hld_count'];
        $DailyData->jrintegral_count = $store_data['data']['panel_1']['jrintegral_count'];
        $DailyData->jrhld_count = $store_data['data']['panel_1']['jrhld_count'];
        $DailyData->jrcoupon_count =$store_data['data']['panel_1']['jrcoupon_count'];

        $BusinessListForm = new BusinessListForm();
        $BusinessListForm->store_id = $this->store->id;
        $data = $BusinessListForm->searchforcron();

        $DailyData->peoplesellcount_huanledou1 =$data['peoplesellcount_huanledou1'];
        $DailyData->peoplesellcount_huanledou_charge1 =$data['peoplesellcount_huanledou_charge1'];
        $DailyData->peoplesellcount_xtjl1 =$data['peoplesellcount_xtjl1'];
        $DailyData->peoplesellcount_num1 =$data['peoplesellcount_num1'];
        $DailyData->peoplesellcount1 =$data['peoplesellcount1'];
        $DailyData->peoplebuyercount1 =$data['peoplebuyercount1'];
        $DailyData->peoplesellcount_huanledou2 =$data['peoplesellcount_huanledou2'];
        $DailyData->peoplesellcount_huanledou_charge2 =$data['peoplesellcount_huanledou_charge2'];
        $DailyData->peoplesellcount_xtjl2 =$data['peoplesellcount_xtjl2'];
        $DailyData->peoplesellcount_num2 =$data['peoplesellcount_num2'];
        $DailyData->peoplesellcount2 =$data['peoplesellcount2'];
        $DailyData->peoplebuyercount2 =$data['peoplebuyercount2'];
        $DailyData->peoplesellcount_huanledou3 =$data['peoplesellcount_huanledou3'];
        $DailyData->peoplesellcount_huanledou_charge3 =$data['peoplesellcount_huanledou_charge3'];
        $DailyData->peoplesellcount_xtjl3 =$data['peoplesellcount_xtjl3'];
        $DailyData->peoplesellcount_num3 =$data['peoplesellcount_num3'];
        $DailyData->peoplesellcount3 =$data['peoplesellcount3'];
        $DailyData->peoplebuyercount3 =$data['peoplebuyercount3'];

        $this->renderJson($DailyData->add());
    }
}