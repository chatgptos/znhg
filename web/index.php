<?php
// 只显示致命错误（生产模式下使用）
error_reporting(E_ERROR);

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
//// 调试模式
//defined('YII_DEBUG') or define('YII_DEBUG', true);
//defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');


$config = require(__DIR__ . '/../config/web.php');
$app = new yii\web\Application($config);
try {
    $app->db->createCommand("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'")->execute();
} catch (Exception $e) {
}


$app->run();
         
if (!YII_DEBUG) {
    /**
     * 调试函数
     * @param $content
     * @param bool $is_die
     */
    function pre($content, $is_die = true)
    {
        header("Content-type: text/html; charset=utf-8");
        echo '<pre>';
        print_r($content);
        echo '</pre>';
        if ($is_die) die();
    }
}
 
      
