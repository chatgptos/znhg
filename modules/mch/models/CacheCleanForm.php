<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/11/9
 * Time: 15:15
 */

namespace app\modules\mch\models;


use yii\helpers\VarDumper;

class CacheCleanForm extends Model
{
    public $data;
    public $pic;
    public $update;

    public function rules()
    {
        return [
            [['data', 'pic', 'update',], 'safe'],
        ];
    }

    public function save()
    {
        if (!$this->validate()) {
            return $this->getModelError();
        }
        if ($this->data) {
            $dir = \Yii::$app->runtimePath . '/cache';
            $this->delFileUnderDir($dir);
        }
        if ($this->pic) {
            $dir = \Yii::$app->basePath . '/web/temp';
            $this->delFileUnderDir($dir, false, ['.gitignore', 'index.html']);
        }
//        if ($this->update) {
//            $dir = \Yii::$app->basePath . '/temp/update';
//            $this->delFileUnderDir($dir);
//        }
        return [
            'code' => 0,
            'msg' => '操作成功',
        ];
    }

    /**
     * 循环删除目录下的所有文件
     * @param $dirName
     * @param bool $delDir
     * @param array $ignoreList
     */
    private function delFileUnderDir($dirName, $delDir = false, $ignoreList = [])
    {
        if (is_dir($dirName) && $handle = opendir("$dirName")) {
            while (false !== ($item = readdir($handle))) {
                if ($item != "." && $item != ".." && !in_array($item, $ignoreList)) {
                    if (is_dir("$dirName/$item")) {
                        $this->delFileUnderDir("$dirName/$item", true, $ignoreList);
                    } else {
                        unlink("$dirName/$item");
                    }
                }
            }
            closedir($handle);
            $delDir && rmdir("$dirName");
        }
    }

}