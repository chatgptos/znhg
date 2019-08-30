<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/6/26
 * Time: 14:13
 */

namespace app\modules\mch\controllers;

use Yii;
use app\models\Store;
use app\models\WechatApp;
use app\modules\mch\models\MchMenu;
use xanyext\wechat\Wechat;

/**
 * 商城后台控制器基类
 * @property Wechat $wechat
 */
class Controller extends \app\controllers\Controller
{
    /* 登录验证白名单 */
    protected $allowAllAction = [
        // 登录页面
        'public/index',
        'public/login',
        'public/logout',
    ];

    public $store;
    protected $store_id;

    /* @var Wechat $wechat */
    protected $wechat;
    protected $wechat_app;

    protected $version;

    /**
     * 初始化商城后台
     * @param $action
     * @return bool
     * @throws \yii\base\ExitException
     */
    public function beforeAction($action)
    {
        // 获取系统版本
        $this->version = $this->getVersion();

        // 验证登录
        $this->checkLogin($action->getUniqueId());

        if (Yii::$app->store->identity) {
            // 商城id
            $this->store_id = Yii::$app->store->identity->store_id;

            // 获取商城信息
            $this->store = Store::findOne($this->store_id);

            // 初始化小程序信息
            $this->initiaWechatApp();
        }
        return true;
    }

    /**
     * 验证登录状态
     * @param $uniqueId
     * @return bool
     * @throws \yii\base\ExitException
     */
    private function checkLogin($uniqueId)
    {
        // 当前url
        $current_url = str_replace('mch/', '', $uniqueId);

        // 白名单url
        if (in_array($current_url, $this->allowAllAction))
            return true;

        if (Yii::$app->store->isGuest) {
            $this->redirect(['/mch/public/login']);
            Yii::$app->end();
        }
        return true;
    }

    /**
     * 初始化小程序信息
     */
    private function initiaWechatApp()
    {
        $this->wechat_app = WechatApp::findOne($this->store->wechat_app_id);

        if (!is_dir(\Yii::$app->runtimePath . '/pem')) {
            mkdir(\Yii::$app->runtimePath . '/pem');
            file_put_contents(\Yii::$app->runtimePath . '/pem/index.html', '');
        }
        $cert_pem_file = null;
        if ($this->wechat_app->cert_pem) {
            $cert_pem_file = \Yii::$app->runtimePath . '/pem/' . md5($this->wechat_app->cert_pem);
            if (!file_exists($cert_pem_file))
                file_put_contents($cert_pem_file, $this->wechat_app->cert_pem);
        }
        $key_pem_file = null;
        if ($this->wechat_app->key_pem) {
            $key_pem_file = \Yii::$app->runtimePath . '/pem/' . md5($this->wechat_app->key_pem);
            if (!file_exists($key_pem_file))
                file_put_contents($key_pem_file, $this->wechat_app->key_pem);
        }
        $this->wechat = new Wechat([
            'appId' => $this->wechat_app->app_id,
            'appSecret' => $this->wechat_app->app_secret,
            'mchId' => $this->wechat_app->mch_id,
            'apiKey' => $this->wechat_app->key,
            'certPem' => $cert_pem_file,
            'keyPem' => $key_pem_file,
        ]);
    }

    /**
     * 获取后台菜单列表
     * @return array
     */
    public function getMenuList()
    {
        $MchMenu = new MchMenu();
        $res = $MchMenu->getList();
        return $res;
    }

}