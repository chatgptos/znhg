<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/3
 * Time: 14:58
 */

namespace app\extensions;


use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Core\Profile\DefaultProfile;
use app\models\SmsRecord;
use app\models\SmsSetting;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

require_once dirname(__DIR__) . '/extensions/aliyun/api_demo/SmsDemo.php';
require_once dirname(__DIR__) . '/extensions/alidayu/TopSdk.php';

class Sms
{
    /**
     * 发送短信
     * @param string $store_id 商铺ID
     * @param string $content 内容，字符串
     * @return array
     */
    public static function send($store_id, $content = null)
    {
//        require \Yii::$app->basePath . '/extensions/aliyun/api_demo/SmsDemo.php';
        $sms_setting = SmsSetting::findOne(['is_delete' => 0, 'store_id' => $store_id]);
        if ($sms_setting->status == 0) {
            return [
                'code' => 1,
                'msg' => '短信通知服务未开启'
            ];
        }
        $content_sms[$sms_setting->msg] = substr($content, -8);
        $res = null;
        $resp = null;

        $a = str_replace("，", ",", $sms_setting->mobile);
        $g = explode(",", $a);
        foreach ($g as $mobile) {
            try {
                $acsClient = new \SmsDemo($sms_setting->AccessKeyId, $sms_setting->AccessKeySecret);
                $res = $acsClient->sendSms($sms_setting->sign, $sms_setting->tpl, $mobile, $content_sms);
            } catch (\Exception $e) {
                \Yii::warning("阿里云短信调用失败：" . $e->getMessage());
                try {
                    $c = new \TopClient();
                    $c->appkey = $sms_setting->AccessKeyId;
                    $c->secretKey = $sms_setting->AccessKeySecret;
                    $req = new \AlibabaAliqinFcSmsNumSendRequest;
                    $req->setSmsType("normal");
                    $req->setSmsFreeSignName($sms_setting->sign);
                    $req->setSmsParam(json_encode($content_sms, JSON_UNESCAPED_UNICODE));
                    $req->setRecNum($mobile);
                    $req->setSmsTemplateCode($sms_setting->tpl);
                    $resp = $c->execute($req);
                } catch (\Exception $e) {
                    \Yii::warning("阿里大鱼调用失败：" . $e->getMessage());

                }
            }
            \Yii::trace("短信发送结果：" . $resp ? json_encode($resp, JSON_UNESCAPED_UNICODE) : json_encode($res, JSON_UNESCAPED_UNICODE));
        }
        if (($res && $res->Code == "OK") || ($resp && $resp->code == 0)) {
            if (is_array($content_sms)) {
                foreach ($content_sms as $k => $v)
                    $content_sms[$k] = strval($v);
                $content_sms = json_encode($content_sms, JSON_UNESCAPED_UNICODE);
            }
            $smsRecord = new SmsRecord();
            $smsRecord->mobile = $sms_setting->mobile;
            $smsRecord->tpl = $sms_setting->tpl;
            $smsRecord->content = $content_sms;
            $smsRecord->ip = \Yii::$app->request->userIP;
            $smsRecord->addtime = time();
            $smsRecord->save();
            return [
                'code' => 0,
                'msg' => '成功'
            ];
        } else {
            return [
                'code' => 1,
                'msg' => $res->Message.$resp->sub_msg
            ];
        }
    }
}