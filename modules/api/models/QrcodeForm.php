<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/11
 * Time: 16:07
 */

namespace app\modules\api\models;

use app\models\UploadConfig;
use app\models\UploadForm;
use xanyext\wechat\Wechat;

/**
 * @property Wechat $wechat 小程序的
 */
class QrcodeForm extends Model
{
//    public $order_no;
    public $data;//['scene'=>"",'page'=>'','width'=>100]
    public $store;
    public $wechat;


    public function getQrcode()
    {
        $this->wechat = $this->getWechat();
        $this->store = isset(\Yii::$app->controller->store) ? \Yii::$app->controller->store : null;
        //获取微信小程序码
        $access_token = $this->wechat->getAccessToken();
        $api = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token={$access_token}";
        $data = json_encode($this->data, JSON_UNESCAPED_UNICODE);
        $this->wechat->curl->post($api, $data);
        if ($this->wechat->curl->error) {

            return [
                'code' => 1,
                'msg' => '小程序码获取失败',
            ];
        }
        $curl = $this->wechat->curl;
        if (!in_array('Content-Type: image/jpeg', $curl->response_headers)) {
            //返回文字
            $res = json_decode($curl->response, true);
            return [
                'code' => 1,
                'msg' => $res['errmsg'],
            ];
        }
        //保存到本地
        $saveRoot = \Yii::$app->basePath . '/web/temp';
        $saveDir = '/';
        if (!is_dir($saveRoot . $saveDir)) {
            mkdir($saveRoot . $saveDir);
            file_put_contents($saveRoot . $saveDir . '.gitignore', "*\r\n!.gitignore");
        }
        $saveName = md5(uniqid()) . '.jpg';
        $webRoot = str_replace('http://', 'https://', \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/temp/' . $saveName);
        file_put_contents($saveRoot . $saveDir . $saveName, $curl->response);

        return [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'url' => $webRoot,
            ],
        ];
        $form = new UploadForm();
        $upload_config = UploadConfig::findOne(['store_id' =>0]);
        $form->upload_config = $upload_config;
        $form->store = $this->store;
        return $form->saveQrcode($saveRoot . $saveDir . $saveName, $saveName);
    }
}