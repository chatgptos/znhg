<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/9/5
 * Time: 17:38
 */

namespace app\modules\mch\models;

use app\models\UploadConfig;
use OSS\Core\OssException;
use OSS\OssClient;

require_once '../extensions/aliyun-oss-php-sdk-2.2.4/autoload.php';
/**
 * @property UploadConfig $model
 */
class StoreUploadForm extends Model
{
    public $model;

    public $store_id;
    public $storage_type;
    public $aliyun;
    public $qcloud;
    public $qiniu;

    public function rules()
    {
        return [
            [['storage_type',], 'string',],
            [['aliyun', 'qcloud', 'qiniu'], 'default', 'value' => (object)[],],
        ];
    }

    public function save()
    {
        $this->model->storage_type = $this->storage_type;
//        $this->model->aliyun = json_encode($this->aliyun, JSON_UNESCAPED_UNICODE);
        $this->model->qcloud = json_encode($this->qcloud, JSON_UNESCAPED_UNICODE);

        foreach ($this->qiniu as $k => $v) {
            $this->qiniu[$k] = trim($v);
        }
        $this->qiniu['domain'] = trim($this->qiniu['domain'], '/');

        $this->model->qiniu = json_encode($this->qiniu, JSON_UNESCAPED_UNICODE);


        foreach ($this->aliyun as $k => $v) {
            $this->aliyun[$k] = trim($v);
        }
        $this->aliyun['domain'] = trim($this->aliyun['domain'], '/');
        if($this->storage_type == 'aliyun'){
            if($this->aliyun['CNAME'] == 0){
                try{
                    $ossClient = new OssClient($this->aliyun['access_key'],$this->aliyun['secret_key'],$this->aliyun['domain']);

                }catch(OssException $e){
                    return [
                        'code'=>1,
                        'msg'=>$e->getMessage()
                    ];
                }
            }else{
                try{
                    $ossClient = new OssClient($this->aliyun['access_key'],$this->aliyun['secret_key'],$this->aliyun['domain'],true);
                }catch(OssException $e){
                    return [
                        'code'=>1,
                        'msg'=>$e->getMessage()
                    ];
                }
            }
            try{
                $res = $ossClient->doesBucketExist($this->aliyun['bucket']);
            }catch(OssException $e){
                return [
                    'code'=>1,
                    'msg'=>'绑定域名不正确'
                ];
            }
            if(!$res){
                return [
                    'code'=>1,
                    'msg'=>'存储空间不存在'
                ];
            }
        }


        $this->model->aliyun = json_encode($this->aliyun, JSON_UNESCAPED_UNICODE);
        if ($this->model->isNewRecord) {
            $this->model->store_id = 0;
            $this->model->is_delete = 0;
            $this->model->addtime = time();
        }
        if ($this->model->save())
            return [
                'code' => 0,
                'msg' => '保存成功',
            ];
        else
            return $this->getModelError($this->model);
    }
}