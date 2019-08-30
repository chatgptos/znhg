<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/6/28
 * Time: 9:51
 */


namespace app\modules\mch\models;


use app\models\Area;
use yii\data\Pagination;
use yii\helpers\Html;

/**
 * @property \app\models\Area $area
 */
class AreaForm extends Model
{
    public $area;

//    public $pid;
//    public

    public function rules(){
        return [
            [['is_open'],'in','range'=>[0,1]],
        ];
    }

    public function attributeLabels()
    {
        return [
            // 'parent_id1'=>'省份ID',
            // 'parent_id2'=>'城市ID',
            'sort'=>'排序',
            // 'is_open'=>'是否开放',
            // 'name'=>'区域名称'
        ];
    }

    public function getList()
    {
        $provinces = \Yii::$app->request->get('province');


        $query = Area::find()->andWhere(['a.is_delete'=>0,'a.level'=>3,'a.is_open'=>1]);

        $query->select('a.*,b.name AS province_name,')
            ->alias('a')
            ->andWhere(['b.level'=>2,'b.is_delete'=>0])
            ->leftJoin(['b'=>Area::tableName()],'b.id=a.parent_id');

        if ($provinces) {
            $query->andWhere(['b.id'=>$provinces]);
        }

        $count = $query->count();
        $p = new Pagination(['totalCount'=>$count,'pageSize'=>20]);

        $list =  $query->offset($p->offset)
            ->limit($p->limit)
            ->asArray()
            ->all();


        return [$list,$p];


    }

    // 获取城市列表
    public function getCityList()
    {
        $province = Area::find()->andWhere(['is_delete'=>0,'level'=>2,'parent_id'=>1])->asArray()->all();
        // 获取市
        $city = Area::find()->andWhere(['is_delete'=>0,'level'=>3,'parent_id'=>$province[0]['id']])->asArray()->all();

        return [$province,$city];
    }

}