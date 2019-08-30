<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'language' => 'zh-CN',
    'timeZone' => 'Asia/Shanghai',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'defaultRoute' => 'index',
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'RpjHpoPkP88RwcCXBQsKL3z5XKASWg0m',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'store' => [
            'class' => 'yii\web\User',
            'identityClass' => 'app\models\StoreUser',
            'enableAutoLogin' => true,
            'identityCookie' => [
                'name' => '_identity-store',
                'httpOnly' => true
            ],
        ],
        'mailer' => [
            // 'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            // 'useFileTransport' => true,

            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@app/mail',
            'useFileTransport' => false,    //这里一定要改成false，不然邮件不会发送
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.qq.com',
                'port' => '465',
                'encryption' => 'ssl',
            ],

            'messageConfig' => [
                'charset' => 'UTF-8',
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning',],
                    'logVars' => [],
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
        'urlManager' => [
            // 美化URL
            'enablePrettyUrl' => true,
            // 如需隐藏index.php需要'showScriptName' => false,
//            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                "<controller:\\w+>/<action:\\w+>/<id:\\d+>" => "<controller>/<action>",
                "<controller:\\w+>/<action:\\w+>" => "<controller>/<action>",
            ],
        ],
    ],
    'modules' => [
        'api' => [
            'class' => 'app\modules\api\Module',
        ],
        'mch' => [
            'class' => 'app\modules\mch\Module',
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {

    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
//        'allowedIPs' => ['127.0.0.1', '::1','180.102.104.160','192.168.3.10','172.17.127.65'],
    ];

    $config['components']['errorHandler'] = [
//        'errorAction' => 'index/error',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1','180.102.104.160','192.168.3.10'],
    ];
var_dump($config['modules']['debug']);
}

return $config;
