<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/31
 * Time: 18:05
 */
$data = json_decode($block->data, true);

?>
<div class="home-block">
    <div class="block-content">
        <div class="block-name">版块：<?= $block->name ?></div>
    </div>
    <img class="block-img" src="<?= Yii::$app->request->baseUrl ?>/statics/images/block-bg.png">
</div>