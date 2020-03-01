<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/8/8
 * Time: 14:57
 */
/* @var $pagination yii\data\Pagination */
//
use yii\helpers\Html;


//?>

<?= Html::tag('p', Html::encode($user->name), ['class' => 'username']) ?>
<!---->
<?= \leandrogehlen\treegrid\TreeGrid::widget([
    'dataProvider' => $dataProvider,
    'keyColumnName' => 'id',
    'parentColumnName' => 'parent_id',
    'parentRootValue' => '0', //first parentId value

    'columns' => [
        'parent_id',
        'username',
        'type',
        'password',
        'avatar_url',
        'total_price',
        'price',
        'integral',
        'coupon',
        'hld',
        'id',
        'id',
        'nickname',
        ['class' => 'yii\grid\ActionColumn',
        ]
    ],



]); ?>
