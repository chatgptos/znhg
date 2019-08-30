<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/7/1
 * Time: 16:52
 */

namespace app\modules\api\models;


use app\models\Setting;
use app\models\Share;
use app\models\User;
use app\modules\mch\models\Model;
use Curl\Curl;

class LoginForm extends Model
{
    public $wechat_app;

    public $code;
    public $user_info;
    public $encrypted_data;
    public $iv;
    public $signature;

    public $store_id;

    public function rules()
    {
        return [
            [['wechat_app', 'code', 'user_info', 'encrypted_data', 'iv', 'signature',], 'required'],
        ];
    }

    public function login()
    {
        if (!$this->validate())
            return $this->getModelError();
        $res = $this->getOpenid($this->code);
        if (!$res || empty($res['openid'])) {
            return [
                'code' => 1,
                'msg' => '获取用户OpenId失败',
                'data' => $res,
            ];
        }
        $session_key = $res['session_key'];
        require __DIR__ . '/wxbdc/WXBizDataCrypt.php';
        $pc = new \WXBizDataCrypt($this->wechat_app->app_id, $session_key);
        $errCode = $pc->decryptData($this->encrypted_data, $this->iv, $data);
        if ($errCode == 0) {
            $data = json_decode($data, true);
            $user = User::findOne(['wechat_open_id' => $data['openId'], 'store_id' => $this->store_id]);
            if (!$user) {
                $user = new User();
                $user->type = 1;
                $user->username = $data['openId'];
                $user->password = \Yii::$app->security->generatePasswordHash(\Yii::$app->security->generateRandomString());
                $user->auth_key = \Yii::$app->security->generateRandomString();
                $user->access_token = \Yii::$app->security->generateRandomString();
                $user->addtime = time();
                $user->is_delete = 0;
                $user->wechat_open_id = $data['openId'];
                $user->wechat_union_id = isset($data['unionId']) ? $data['unionId'] : '';
                //$user->nickname = $data['nickName'];
                $user->nickname = preg_replace('/[\xf0-\xf7].{3}/', '', $data['nickName']);
                $user->avatar_url = $data['avatarUrl'];
                $user->store_id = $this->store_id;
                $user->save();
                $same_user = User::find()->select('id')->where([
                    'AND',
                    [
                        'store_id' => $this->store_id,
                        'wechat_open_id' => $data['openId'],
                        'is_delete' => 0,
                    ],
                    ['<', 'id', $user->id],
                ])->one();
                if ($same_user) {
                    $user->delete();
                    $user = null;
                    $user = $same_user;
                }
            }

            // 分销商
            $share = Share::findOne(['user_id' => $user->parent_id]);
            $parent = '总店';
            if (!empty($share)) {
                $share_user = User::findOne(['id' => $share->user_id]);
                $parent = $share->name ? $share->name : $share_user->nickname;
            }

            return [
                'code' => 0,
                'msg' => 'success',
                'data' => (object)[
                    'access_token' => $user->access_token,
                    'nickname' => $user->nickname,
                    'avatar_url' => $user->avatar_url,
                    'is_distributor' => $user->is_distributor ? $user->is_distributor : 0,
                    'parent' => $parent,
                    'id' => $user->id,
                    'is_clerk' => $user->is_clerk,
                    'integral' => $user->integral,
                ],
            ];
        } else {
            return [
                'code' => 1,
                'msg' => '登录失败',
            ];
        }
    }


    private function getOpenid($code)
    {
        $api = "https://api.weixin.qq.com/sns/jscode2session?appid={$this->wechat_app->app_id}&secret={$this->wechat_app->app_secret}&js_code={$code}&grant_type=authorization_code";
        $curl = new Curl();
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        $curl->get($api);
        $res = $curl->response;
        $res = json_decode($res, true);
        return $res;
    }
}