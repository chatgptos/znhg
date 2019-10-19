<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/14
 * Time: 15:33
 */

namespace app\modules\api\models;


use app\models\Setting;
use app\models\Share;
use app\models\User;

class BindForm extends Model
{
    public $store_id;
    public $user_id;

    /**
     * @return array
     * 绑定上下级关系
     */
    public function save()
    {
        $setting = Setting::findOne(['store_id' => $this->store_id]);
        $parent_id = \Yii::$app->request->get('parent_id');
        $user = User::findOne(['id' => $this->user_id, 'is_delete' => 0]);
        $parent = User::findOne(['id' => $parent_id, 'is_delete' => 0]);
        //店铺未开启分销
        $save_root = \Yii::$app->basePath . '/web/temp/';

        if(!$parent){
            file_put_contents($save_root . 'store_qrcodepost.log', "绑定关系\r\n".$this->user_id. $user->nickname .'被绑定上级id' .$parent_id.  '绑定结果' . json_encode([
                    'code' => 1,
                    'msg' => '上级不存在用户'
                ], JSON_UNESCAPED_UNICODE), FILE_APPEND);
        }

        if ($setting->level == 0) {
            file_put_contents($save_root . 'store_qrcodepost.log', "绑定关系\r\n" . $this->user_id.$user->nickname .'被绑定上级id'.$parent_id. '经销商' . $user->is_distributor . '上级' . $user->parent_id . $parent->nickname . '经销商' . $parent->is_distributor . '绑定结果' . json_encode([
                    'code' => 1,
                    'msg' => '未开启分销'
                ], JSON_UNESCAPED_UNICODE), FILE_APPEND);


            return [
                'code' => 1,
                'msg' => '未开启分销'
            ];
        }
        //自身分享的页面
        if ($this->user_id == $parent_id) {
            file_put_contents($save_root . 'store_qrcodepost.log', "绑定关系\r\n" .  $this->user_id.$user->nickname .'被绑定上级id'.$parent_id. '经销商' . $user->is_distributor . '上级' . $user->parent_id . $parent->nickname . '经销商' . $parent->is_distributor . '绑定结果' . json_encode([
                    'code' => 1,
                    'msg' => '自身分销的页面'
                ], JSON_UNESCAPED_UNICODE), FILE_APPEND);
            return [
                'code' => 1,
                'msg' => '自身分销的页面'
            ];
        }
        //父级id是否是分销商
        $exit = Share::find()->andWhere(['user_id' => $parent_id, 'store_id' => $this->store_id, 'is_delete' => 0])->exists();
        if (!$exit) {
            file_put_contents($save_root . 'store_qrcodepost.log', "绑定关系\r\n" .  $this->user_id.$user->nickname .'被绑定上级id'.$parent_id. '经销商' . $user->is_distributor . '上级' . $user->parent_id . $parent->nickname . '经销商' . $parent->is_distributor . '绑定结果' . json_encode([
                    'code' => 1,
                    'msg' => '用户上级是分销商'
                ], JSON_UNESCAPED_UNICODE), FILE_APPEND);
//            return [
//                'code' => 1,
//                'msg' => '用户上级是分销商'
//            ];
        }
        //判断父级id是否在三级分销链中
        $res = self::check($this->user_id, $parent_id, 0);
        if ($res['code'] == 1) {
            file_put_contents($save_root . 'store_qrcodepost.log', "绑定关系\r\n" .  $this->user_id.$user->nickname .'被绑定上级id'.$parent_id. '经销商' . $user->is_distributor . '上级' . $user->parent_id . $parent->nickname . '经销商' . $parent->is_distributor . '绑定结果' . json_encode(
                    [
                        'code' => 1,
                        'msg' => '父级id是否在三级分销链中'
                    ], JSON_UNESCAPED_UNICODE), FILE_APPEND);
            return [
                'code' => 1,
                'msg' => '父级id是否在三级分销链中'
            ];
        }
        //用户是否是分销商
        if ($user->is_distributor == 1) {
            file_put_contents($save_root . 'store_qrcodepost.log', "绑定关系\r\n" .  $this->user_id.$user->nickname .'被绑定上级id'.$parent_id. '经销商' . $user->is_distributor . '上级' . $user->parent_id . $parent->nickname . '经销商' . $parent->is_distributor . '绑定结果' . json_encode(
                    [
                        'code' => 1,
                        'msg' => '已经加入了集市is_distributor为1'
                    ], JSON_UNESCAPED_UNICODE), FILE_APPEND);
//            return [
//                'code' => 1,
//                'msg' => '已经加入了集市is_distributor为1'
//            ];
        }
        //用户是否存在父级
        if ($user->parent_id != 0) {
            file_put_contents($save_root . 'store_qrcodepost.log', "绑定关系\r\n" .  $this->user_id.$user->nickname .'被绑定上级id'.$parent_id. '经销商' . $user->is_distributor . '上级' . $user->parent_id . $parent->nickname . '经销商' . $parent->is_distributor . '绑定结果' . json_encode(
                    [
                        'code' => 1,
                        'msg' => '已经存在parent_id'
                    ], JSON_UNESCAPED_UNICODE), FILE_APPEND);
            return [
                'code' => 1,
                'msg' => '已经存在parent_id'
            ];
        }
        $user->parent_id = $parent_id;
        if ($user->save()) {
            file_put_contents($save_root . 'store_qrcodepost.log', "绑定关系\r\n" .  $this->user_id.$user->nickname .'被绑定上级id'.$parent_id. '经销商' . $user->is_distributor . '上级' . $user->parent_id . $parent->nickname . '经销商' . $parent->is_distributor . '绑定结果' . json_encode(
                    [
                        'code' => 0,
                        'msg' => '成功',
                        'data' => $parent->nickname
                    ], JSON_UNESCAPED_UNICODE), FILE_APPEND);
            return [
                'code' => 0,
                'msg' => '成功',
                'data' => $parent->nickname
            ];
        } else {
            file_put_contents($save_root . 'store_qrcodepost.log', "绑定关系\r\n" .  $this->user_id.$user->nickname .'被绑定上级id'.$parent_id. '经销商' . $user->is_distributor . '上级' . $user->parent_id . $parent->nickname . '经销商' . $parent->is_distributor . '绑定结果' . json_encode(
                    [
                        'code' => 1,
                        'msg' => '网络异常'
                    ], JSON_UNESCAPED_UNICODE), FILE_APPEND);
            return [
                'code' => 1,
                'msg' => '网络异常'
            ];
        }
    }

    public static function check($user_id, $parent_id, $root = 0)
    {
        if ($root == 3) {
            return [
                'code' => 0,
                'msg' => '可以绑定'
            ];
        }
        if ($parent_id == 0) {
            return [
                'code' => 0,
                'msg' => '可以绑定'
            ];
        }
        $user = User::findOne(['id' => $parent_id, 'is_delete' => 0]);
        if ($user_id == $user->parent_id) {
            return [
                'code' => 1,
                'msg' => '不能'
            ];
        }
        return self::check($user_id, $user->parent_id, $root + 1);
    }
}