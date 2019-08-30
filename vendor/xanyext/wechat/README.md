# 微信

##### 1.导入代码，使用composer
```php
composer required xanyext/wechat:dev-master
```

##### 2.开始使用
```php
require 'vender/autoload.php';
$wechat = new \xanyext\wechat\Wechat([
    'appId' => '',
    'appSecret' => '',
    'mchId' => '',
    'apiKey' => '',
    'certPem' => '',
    'keyPem' => '',
    'cachePath' => '',
]);
$wechat->getAccessToken();
```