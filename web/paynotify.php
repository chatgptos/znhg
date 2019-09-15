<?php
echo 2;
die(1);
error_reporting(3);
$_GET['r'] = 'pay-notify/index';



// 只显示致命错误（生产模式下使用）
error_reporting(E_ERROR);

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
var_dump(1);
die;
// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../config/web.php');

var_dump(11);
die;
(new yii\web\Application($config))->run();