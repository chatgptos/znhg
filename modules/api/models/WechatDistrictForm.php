<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/9/8
 * Time: 11:15
 */

namespace app\modules\api\models;


use app\models\District;

class WechatDistrictForm extends Model
{
    public $national_code;

    public $province_name;
    public $city_name;
    public $county_name;

    public function rules()
    {
        return [
            [['national_code', 'province_name', 'city_name', 'county_name',], 'safe',],
        ];
    }

    public function search()
    {
        if (!$this->validate())
            return $this->getModelError();
        $this->national_code = $this->getCodeIfInMap($this->national_code);
        $district = District::findOne([
            'adcode' => $this->national_code,
            'level' => 'district',
        ]);
        if (!$district) {
            return $this->getDistrictWithoutCode();
        }

        $city = District::findOne(['id' => $district->parent_id]);
        if (!$city)
            return [
                'code' => 1,
                'msg' => '所在地区获取失败，请手动填写地址',
                'data' => [
                    'district' => null,
                ]
            ];
        $province = District::findOne(['id' => $city->parent_id]);
        if (!$province)
            return [
                'code' => 1,
                'msg' => '所在地区获取失败，请手动填写地址',
                'data' => [
                    'district' => null,
                ]
            ];
        return [
            'code' => 0,
            'data' => [
                'district' => [
                    'province' => [
                        'id' => $province->id,
                        'name' => $province->name,
                    ],
                    'city' => [
                        'id' => $city->id,
                        'name' => $city->name,
                    ],
                    'district' => [
                        'id' => $district->id,
                        'name' => $district->name,
                    ],
                ],
            ],
        ];
    }

    private function getDistrictWithoutCode()
    {
        $province = District::findOne(['name' => $this->province_name, 'level' => 'province']);
        if (!$province)
            return [
                'code' => 1,
                'msg' => '所在地区获取失败，请手动填写地址',
                'data' => [
                    'district' => null,
                ]
            ];
        $city = District::findOne(['name' => $this->city_name, 'level' => 'city']);
        if (!$city)
            return [
                'code' => 1,
                'msg' => '所在地区获取失败，请手动填写地址',
                'data' => [
                    'district' => null,
                ]
            ];
        $district = District::findOne(['name' => $this->county_name, 'level' => 'district']);
        if (!$district) {
            $district = District::findOne(['parent_id' => $city->id, 'level' => 'district']);
        }
        if (!$district)
            return [
                'code' => 1,
                'msg' => '所在地区获取失败，请手动填写地址',
                'data' => [
                    'district' => null,
                ]
            ];

        return [
            'code' => 0,
            'data' => [
                'district' => [
                    'province' => [
                        'id' => $province->id,
                        'name' => $province->name,
                    ],
                    'city' => [
                        'id' => $city->id,
                        'name' => $city->name,
                    ],
                    'district' => [
                        'id' => $district->id,
                        'name' => $district->name,
                    ],
                ],
            ],
        ];


    }

    private function getCodeIfInMap($code)
    {
        if (!$code)
            return $code;
        $map = [
            //嘉峪关市
            '620201' => '620200',
            '620202' => '620200',
            '620203' => '620200',

            //东莞市
            '441901' => '441900',
            '441902' => '441900',
            '441903' => '441900',
            '441904' => '441900',
            '441905' => '441900',
            '441906' => '441900',
            '441907' => '441900',
            '441908' => '441900',
            '441909' => '441900',
            '441910' => '441900',
            '441911' => '441900',
            '441912' => '441900',
            '441913' => '441900',
            '441914' => '441900',
            '441915' => '441900',
            '441916' => '441900',
            '441917' => '441900',
            '441918' => '441900',
            '441919' => '441900',
            '441920' => '441900',
            '441921' => '441900',
            '441922' => '441900',
            '441923' => '441900',
            '441924' => '441900',
            '441925' => '441900',
            '441926' => '441900',
            '441927' => '441900',
            '441928' => '441900',
            '441929' => '441900',
            '441930' => '441900',
            '441931' => '441900',
            '441932' => '441900',
            '441933' => '441900',

            //中山市
            '442001' => '442000',
            '442002' => '442000',
            '442003' => '442000',
            '442004' => '442000',
            '442005' => '442000',
            '442006' => '442000',
            '442007' => '442000',
            '442008' => '442000',
            '442009' => '442000',
            '442010' => '442000',
            '442011' => '442000',
            '442012' => '442000',
            '442013' => '442000',
            '442014' => '442000',
            '442015' => '442000',
            '442016' => '442000',
            '442017' => '442000',
            '442018' => '442000',
            '442019' => '442000',
            '442020' => '442000',
            '442021' => '442000',
            '442022' => '442000',
            '442023' => '442000',
            '442024' => '442000',
            '442025' => '442000',

            //三亚市

        ];
        foreach ($map as $k => $item) {
            if ($code == $k) {
                $code = $item;
                break;
            }
        }
        return $code;
    }

}