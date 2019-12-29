<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/12/27
 * Time: 10:44
 */

namespace app\modules\mch\models;

use yii\helpers\VarDumper;

class MchMenu
{
    public $user_auth;

    public function getList()
    {
        $menu_list = [
            [
                'name' => '商城管理',
                'route' => 'mch/store/wechat-setting',
                'icon' => 'icon-setup',
                'list' => [
                    [
                        'name' => '系统设置',
                        'route' => 'mch/store/wechat-setting',
                        'list' => [
                            [
                                'name' => '微信配置',
                                'route' => 'mch/store/wechat-setting',
                            ],
                            [
                                'name' => '商城设置',
                                'route' => 'mch/store/setting',
                            ],
                            [
                                'name' => '模板消息',
                                'route' => 'mch/store/tpl-msg',
                            ],
                            [
                                'name' => '短信通知',
                                'route' => 'mch/store/sms',
                            ],
                            [
                                'name' => '邮件通知',
                                'route' => 'mch/store/mail',
                            ],
                            [
                                'name' => '运费规则',
                                'route' => 'mch/store/postage-rules',
                                'sub' => [
                                    'mch/store/postage-rules-edit'
                                ],
                            ],
                            [
                                'name' => '快递单打印',
                                'route' => 'mch/store/express',
                                'sub' => [
                                    'mch/store/express-edit',
                                ],
                            ],
                            [
                                'name' => '小票打印',
                                'route' => 'mch/printer/list',
                                'sub' => [
                                    'mch/printer/setting',
                                    'mch/printer/edit',
                                ],
                            ],
//                            [
//                                'name' => '模板',
//                                'route' => 'mch/test/tpl',
//                            ],
                            [
                                'name' => '上传设置',
                                'route' => 'mch/store/upload',
                            ],
                            [
                                'name' => '缓存',
                                'route' => 'mch/cache/index',
                            ],
                        ],
                    ],
                    [
                        'name' => '小程序设置',
                        'route' => 'mch/store/slide',
                        'list' => [
                            [
                                'name' => '轮播图',
                                'route' => 'mch/store/slide',
                                'sub' => [
                                    'mch/store/slide-edit',
                                ],
                            ],
                            [
                                'name' => '导航图标',
                                'route' => 'mch/store/home-nav',
                                'sub' => [
                                    'mch/store/home-nav-edit',
                                ],
                            ],
                            [
                                'name' => '图片魔方',
                                'route' => 'mch/store/home-block',
                                'sub' => [
                                    'mch/store/home-block-edit',
                                ],
                            ],
                            [
                                'name' => '导航栏',
                                'route' => 'mch/store/navbar',
                            ],
                            [
                                'name' => '首页布局',
                                'route' => 'mch/store/home-page',
                            ],
                            [
                                'name' => '用户中心',
                                'route' => 'mch/store/user-center',
                            ],
                            [
                                'name' => '下单表单',
                                'route' => 'mch/store/form',
                            ],
                            [
                                'name' => '小程序发布',
                                'route' => 'mch/store/wxapp',
                            ],
                        ],
                    ],

                ],
            ],
            [
                'name' => '商场管理',
                'route' => 'mch/goods/goods',
                'icon' => 'icon-service',
                'list' => [
                    [
                        'name' => '商品管理',
                        'route' => 'mch/goods/goods',
                        'sub' => [
                            'mch/goods/goods-edit',
                        ],
                    ],
                    [
                        'name' => '分类',
                        'route' => 'mch/store/cat',
                        'sub' => [
                            'mch/store/cat-edit',
                        ],
                    ],
                    [
                        'name' => '订单管理',
                        'route' => 'mch/order/index',
                        'icon' => 'icon-activity',
                        'list' => [
                            [
                                'name' => '订单列表',
                                'route' => 'mch/order/index',
                                'sub' => [
                                    'mch/order/detail'
                                ]
                            ],
                            [
                                'name' => '自提订单',
                                'route' => 'mch/order/offline',
                            ],
                            [
                                'name' => '售后订单',
                                'route' => 'mch/order/refund',
                            ],
                            [
                                'name' => '评价管理',
                                'route' => 'mch/comment/index',
                            ],
                        ],
                    ],
                ],
            ],

//            [
//                'id' => 'book',
//                'name' => '预售管理',
//                'icon' => 'icon-service',
//                'route' => 'mch/book/goods/index',
//                'list' => [
//                    [
//                        'name' => '商品管理',
//                        'route' => 'mch/book/goods/index',
//                        'sub' => [
//                            'mch/book/goods/goods-edit'
//                        ]
//                    ],
//                    [
//                        'name' => '商品分类',
//                        'route' => 'mch/book/goods/cat',
//                        'sub' => [
//                            'mch/book/goods/cat-edit'
//                        ]
//                    ],
//                    [
//                        'name' => '订单管理',
//                        'route' => 'mch/book/order/index',
//                    ],
//                    [
//                        'name' => '基础设置',
//                        'route' => 'mch/book/index/setting',
//                    ],
//                    [
//                        'name' => '模板消息',
//                        'route' => 'mch/book/notice/setting',
//                    ],
//                    [
//                        'name' => '评论管理',
//                        'route' => 'mch/book/comment/index',
//                    ],
//                ],
//            ],

            [
                'name' => '预售管理',
                'route' => 'mch/bookmall/goods/goods',
                'icon' => 'icon-service',
                'list' => [
                    [
                        'name' => '商品管理',
                        'route' => 'mch/bookmall/goods/goods',
                        'sub' => [
                            'mch/bookmall/goods/goods-edit',
                        ],
                    ],
                    [
                        'name' => '分类',
                        'route' => 'mch/bookmall/store/cat',
                        'sub' => [
                            'mch/bookmall/store/cat-edit',
                        ],
                    ],
                    [
                        'name' => '订单管理',
                        'route' => 'mch/bookmall/order/index',
                        'icon' => 'icon-activity',
                        'list' => [
                            [
                                'name' => '订单列表',
                                'route' => 'mch/bookmall/order/index',
                                'sub' => [
                                    'mch/bookmall/order/detail',
                                    'mch/bookmall/order/index',
                                ]
                            ],
                            [
                                'name' => '自提订单',
                                'route' => 'mch/bookmall/order/offline',
                            ],
                            [
                                'name' => '售后订单',
                                'route' => 'mch/bookmall/order/refund',
                            ],
                            [
                                'name' => '评价管理',
                                'route' => 'mch/bookmall/comment/index',
                            ],
                        ],
                    ],
                    [
                        'name' => '整点预售',
                        'route' => 'mch/bookmall/seckill/index',
                        'list' => [
                            [
                                'name' => '开放时间',
                                'route' => 'mch/bookmall/seckill/index',
                            ],
                            [
                                'name' => '商品设置',
                                'route' => 'mch/bookmall/seckill/goods',
                                'sub' => [
                                    'mch/bookmall/seckill/goods-edit',
                                    'mch/bookmall/seckill/goods-detail',
                                    'mch/bookmall/seckill/calendar',
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            [
//                'id' => 'book',
                'name' => '优惠券商场管理',
                'icon' => 'icon-service',
                'route' => 'mch/couponmall/goods/index',
                'list' => [
                    [
                        'name' => '商品管理',
                        'route' => 'mch/couponmall/goods/index',
                        'sub' => [
                            'mch/couponmall/goods/goods-edit'
                        ]
                    ],
                    [
                        'name' => '商品分类',
                        'route' => 'mch/couponmall/goods/cat',
                        'sub' => [
                            'mch/couponmall/goods/cat-edit'
                        ]
                    ],
                    [
                        'name' => '订单管理',
                        'route' => 'mch/couponmall/order/index',
                    ],
                    [
                        'name' => '基础设置',
                        'route' => 'mch/couponmall/index/setting',
                    ],
                    [
                        'name' => '模板消息',
                        'route' => 'mch/couponmall/notice/setting',
                    ],
                    [
                        'name' => '评论管理',
                        'route' => 'mch/couponmall/comment/index',
                    ],
                ],
            ],
            [
                'name' => '众筹C管理',
                'route' => 'mch/crowdc/goods/goods',
                'icon' => 'icon-service',
                'list' => [
                    [
                        'name' => '商品管理',
                        'route' => 'mch/crowdc/goods/goods',
                        'sub' => [
                            'mch/crowdc/goods/goods-edit',
                        ],
                    ],
                    [
                        'name' => '分类',
                        'route' => 'mch/crowdc/store/cat',
                        'sub' => [
                            'mch/crowdc/store/cat-edit',
                        ],
                    ],
                    [
                        'name' => '订单管理',
                        'route' => 'mch/crowdc/order/index',
                        'icon' => 'icon-activity',
                        'list' => [
                            [
                                'name' => '订单列表',
                                'route' => 'mch/crowdc/order/index',
                                'sub' => [
                                    'mch/crowdc/order/detail',
                                    'mch/crowdc/order/index',
                                ]
                            ],
                            [
                                'name' => '自提订单',
                                'route' => 'mch/crowdc/order/offline',
                            ],
                            [
                                'name' => '售后订单',
                                'route' => 'mch/crowdc/order/refund',
                            ],
                            [
                                'name' => '评价管理',
                                'route' => 'mch/crowdc/comment/index',
                            ],
                        ],
                    ],
                    [
                        'name' => '整点众筹',
                        'route' => 'mch/crowdc/seckill/index',
                        'list' => [
                            [
                                'name' => '开放时间',
                                'route' => 'mch/crowdc/seckill/index',
                            ],
                            [
                                'name' => '商品设置',
                                'route' => 'mch/crowdc/seckill/goods',
                                'sub' => [
                                    'mch/crowdc/seckill/goods-edit',
                                    'mch/crowdc/seckill/goods-detail',
                                    'mch/crowdc/seckill/calendar',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => '众筹B管理',
                'route' => 'mch/crowdapply/goods/index',
                'icon' => 'icon-service',
                'list' => [
                    [
                        'name' => '众筹报名管理',
                        'icon' => 'icon-service',
                        'route' => 'mch/crowdapply/goods/index',
                        'list' => [
                            [
                                'name' => '活动管理',
                                'route' => 'mch/crowdapply/goods/index',
                                'sub' => [
                                    'mch/crowdapply/goods/goods-edit'
                                ]
                            ],
                            [
                                'name' => '活动分类',
                                'route' => 'mch/crowdapply/goods/cat',
                                'sub' => [
                                    'mch/crowdapply/goods/cat-edit'
                                ]
                            ],
                            [
                                'name' => '报名记录管理',
                                'route' => 'mch/crowdapply/order/index',
                            ],
                            [
                                'name' => '基础设置',
                                'route' => 'mch/crowdapply/index/setting',
                            ],
                            [
                                'name' => '模板消息',
                                'route' => 'mch/crowdapply/notice/setting',
                            ],
                            [
                                'name' => '报名评论管理',
                                'route' => 'mch/crowdapply/comment/index',
                            ],
                        ],
                    ],
                    [
                        'name' => '众筹资格管理',
                        'icon' => 'icon-service',
                        'route' => 'mch/crowdstockright/goods/index',
                        'list' => [
                            [
                                'name' => '权益管理',
                                'route' => 'mch/crowdstockright/goods/index',
                                'sub' => [
                                    'mch/crowdstockright/goods/goods-edit'
                                ]
                            ],
                            [
                                'name' => '权益分类',
                                'route' => 'mch/crowdstockright/goods/cat',
                                'sub' => [
                                    'mch/crowdstockright/goods/cat-edit'
                                ]
                            ],
                            [
                                'name' => '权益记录管理',
                                'route' => 'mch/crowdstockright/order/index',
                            ],
                            [
                                'name' => '基础设置',
                                'route' => 'mch/crowdstockright/index/setting',
                            ],
                            [
                                'name' => '模板消息',
                                'route' => 'mch/crowdstockright/notice/setting',
                            ],
                            [
                                'name' => '评论管理',
                                'route' => 'mch/crowdstockright/comment/index',
                            ],
                        ],
                    ],
                ],
            ],

            [
                'name' => '奖金结算管理',
                'route' => 'mch/settlementbonus/goods/index',
                'icon' => 'icon-service',
                'list' => [
                    [
                        'name' => '奖金结算管理',
                        'icon' => 'icon-service',
                        'route' => 'mch/settlementbonus/goods/index',
                        'list' => [
                            [
                                'name' => '奖金管理',
                                'route' => 'mch/settlementbonus/goods/index',
                                'sub' => [
                                    'mch/settlementbonus/goods/goods-edit'
                                ]
                            ],
                            [
                                'name' => '奖金分类',
                                'route' => 'mch/settlementbonus/goods/cat',
                                'sub' => [
                                    'mch/settlementbonus/goods/cat-edit'
                                ]
                            ],
                            [
                                'name' => '奖金发放记录',
                                'route' => 'mch/settlementbonus/order/index',
                            ],

//                            [
//                                'name' => '加权分红放记录管理',
//                                'route' => 'mch/settlementbonus/order/offline1',
//                            ],
//
//                            [
//                                'name' => '返点奖金放记录管理',
//                                'route' => 'mch/settlementbonus/order/offline2',
//                            ],
//
//                            [
//                                'name' => '福利分红发放记录管理',
//                                'route' => 'mch/settlementbonus/order/offline3',
//                            ],
//                            [
//                                'name' => '基础设置',
//                                'route' => 'mch/settlementbonus/index/setting',
//                            ],
//                            [
//                                'name' => '模板消息',
//                                'route' => 'mch/settlementbonus/notice/setting',
//                            ],
//                            [
//                                'name' => '奖金评论管理',
//                                'route' => 'mch/settlementbonus/comment/index',
//                            ],
                        ],
                    ],

                    [
                        'name' => '用户结算管理',
                        'icon' => 'icon-service',
                        'route' => 'mch/settlementstatistics/data/user',
                        'list' => [
                            [
                                'name' => '用户推荐用户统计',
                                'route' => 'mch/settlementstatistics/data/user',
                                'sub' => [
                                    'mch/settlementstatistics/data/goods'
                                ]
                            ],
                            [
                                'name' => '总消费和返点结算',
                                'route' => 'mch/settlementstatistics/data/user1',
                                'sub' => [
                                    'mch/settlementstatistics/data/goods'
                                ]
                            ],
                            [
                                'name' => '返点等级',
                                'route' => 'mch/settlementstatistics/choujiang/level',
                                'sub' => [
                                    'mch/settlementstatistics/data/goods',
                                    'mch/settlementstatistics/choujiang/level-edit'
                                ]
                            ],


//
//                            [
////                                'name' => '数据统计',
////                                'route' => 'mch/group/data/goods',
////                                'sub' => [
////                                    'mch/group/data/user'
////                                ]
////                            ],
//                            [
//                                'name' => '数据统计',
//                                'route' => 'mch/group/data/goods',
//                                'sub' => [
//                                    'mch/group/data/user'
//                                ]
//                            ],
//                            [
//                                'name' => '用户积分统计',
//                                'route' => 'mch/settlementstatistics/data/user',
//                                'sub' => [
//                                    'mch/settlementstatistics/data/user'
//                                ]
//                            ],
//                            [
//                                'name' => '用户现金消费统计',
//                                'route' => 'mch/settlementstatistics/data/user',
//                            ],
//                            [
//                                'name' => '用户的用户统计',
//                                'route' => 'mch/settlementstatistics/data/user',
//                            ],
//                            [
//                                'name' => '模板消息',
//                                'route' => 'mch/settlementstatistics/data/user',
//                            ],
//                            [
//                                'name' => '用户评论管理',
//                                'route' => 'mch/settlementstatistics/data/user',
//                            ],
                        ],
                    ],


                ],
            ],

            [
                'name' => '集市管理',
                'route' => 'mch/fair/index',
                'icon' => 'icon-people',
                'list' => [

                    [
                        'name' => '开放时间',
                        'route' => 'mch/fair/opentime',
                    ],
                    [
                        'name' => '集市交易列表',
                        'route' => 'mch/fair/dealindex',
                        'sub' => [
                            'mch/fair/card',
                            'mch/fair/coupon',
                            'mch/fair/rechange-log',
                            'mch/fair/edit',
                        ],
                    ],
                    [
                        'name' => '集市用户列表',
                        'route' => 'mch/fair/index',
                        'sub' => [
//                            'mch/fair/card',
//                            'mch/fair/coupon',
//                            'mch/fair/rechange-log',
//                            'mch/fair/edit',
                        ],
                    ],

//                    [
//                        'name' => '核销员',
//                        'route' => 'mch/fair/clerk',
//                    ],
                    [
                        'name' => '会员等级',
                        'route' => 'mch/fair/level',
                        'sub' => [
                            'mch/fair/level-edit',
                        ]
                    ],

                    [
                        'name' => '优惠券商管理',
                        'route' => 'mch/fair/level',
                        'sub' => [
                            'mch/fair/level-edit1',
                        ]
                    ],

                    [
                        'name' => '经销商兑换列表',
                        'route' => 'mch/fair/level',
                        'sub' => [
                            'mch/fair/level-edit2',
                        ]
                    ],
                    [
                        'name' => '渠道商兑换列表',
                        'route' => 'mch/fair/level',
                        'sub' => [
                            'mch/fair/level-edit3',
                        ]
                    ],
                    [
                        'name' => '服务权兑换列表',
                        'route' => 'mch/fair/level',
                        'sub' => [
                            'mch/fair/level-edit',
                        ]
                    ],
                    [
                        'name' => '分红权兑换列表',
                        'route' => 'mch/fair/level',
                        'sub' => [
                            'mch/fair/level-edit',
                        ]
                    ],
                    [
                        'name' => '福利分红兑换列表',
                        'route' => 'mch/fair/level',
                        'sub' => [
                            'mch/fair/level-edit',
                        ]
                    ],
                    [
                        'name' => '抽奖兑换列表',
                        'route' => 'mch/fair/level',
                        'sub' => [
                            'mch/fair/level-edit',
                        ]
                    ],
                    [
                        'name' => '赠送列表',
                        'route' => 'mch/fair/level',
                        'sub' => [
                            'mch/fair/level-edit',
                        ]
                    ],
                ],
            ],
//            [
//                'name' => '订单管理',
//                'route' => 'mch/order/index',
//                'icon' => 'icon-activity',
//                'list' => [
//                    [
//                        'name' => '订单列表',
//                        'route' => 'mch/order/index',
//                        'sub' => [
//                            'mch/order/detail'
//                        ]
//                    ],
//                    [
//                        'name' => '自提订单',
//                        'route' => 'mch/order/offline',
//                    ],
//                    [
//                        'name' => '售后订单',
//                        'route' => 'mch/order/refund',
//                    ],
//                    [
//                        'name' => '评价管理',
//                        'route' => 'mch/comment/index',
//                    ],
//                ],
//            ],

            [
                'name' => '财务报表',
                'route' => 'mch/financial/index',
                'icon' => 'icon-people',
                'list' => [
                    [
                        'name' => '财务管理',
                        'route' => 'mch/financial/index',
                        'sub' => [
                            'mch/financial/card',
                            'mch/financial/coupon',
                            'mch/financial/rechange-log',
                            'mch/financial/edit',
                        ],
                        'list' => [

                            [
                                'name' => '积分提现',
                                'route' => 'mch/share/cash',
                            ],
                            [
                                'name' => '积分收入',
                                'route' => 'mch/share/cash',
                            ],
                            [
                                'name' => '提现设置',
                                'route' => 'mch/financial/clerk',
                            ],
                            [
                                'name' => '收银台',
                                'route' => 'mch/financial/index',
                            ],
                        ]
                    ],

                    [
                        'name' => '报表查询',
                        'route' => 'mch/financial/report',
                        'list' => [
                            [
                                'name' => '每日营销数据',
                                'route' => 'mch/financial/report',
                                'sub' => [
                                    'mch/share/qrcode'
                                ]
                            ],
                            [
                                'name' => '每日订单数据',
                                'route' => 'mch/financial/reportforms'
                            ]
                        ]
                    ],
                ],
            ],

            [
                'name' => '用户管理',
                'route' => 'mch/user/index',
                'icon' => 'icon-people',
                'list' => [
                    [
                        'name' => '用户列表',
                        'route' => 'mch/user/index',
                        'sub' => [
                            'mch/user/card',
                            'mch/user/coupon',
                            'mch/user/rechange-log',
                            'mch/user/edit',
                        ],
                    ],
                    [
                        'name' => '核销员',
                        'route' => 'mch/user/clerk',
                    ],
                    [
                        'name' => '会员等级',
                        'route' => 'mch/user/level',
                        'sub' => [
                            'mch/user/level-edit',
                        ]
                    ],
                ],
            ],
            [
                'id' => 'share',
                'name' => '券商中心',
                'route' => 'mch/share/index',
                'icon' => 'icon-jiegou',
                'list' => [
                    [
                        'name' => '优惠券商',
                        'route' => 'mch/share/index',
                    ],
                    [
                        'name' => '优惠券订单',
                        'route' => 'mch/share/order',
                    ],
                    [
                        'name' => '优惠券商设置',
                        'route' => 'mch/share/basic',
                        'list' => [
                            [
                                'name' => '基础设置',
                                'route' => 'mch/share/basic',
                                'sub' => [
                                    'mch/share/qrcode'
                                ]
                            ],
                            [
                                'name' => '佣金设置',
                                'route' => 'mch/share/setting'
                            ]
                        ]
                    ],
                ],
            ],
            [
                'name' => '内容管理',
                'route' => 'mch/article/index',
                'icon' => 'icon-barrage',
                'list' => [
                    [
                        'name' => '文章',
                        'route' => 'mch/article/index',
                        'sub' => [
                            'mch/article/edit',
                        ],
                    ],
                    [
                        'id' => 'topic',
                        'name' => '专题',
                        'route' => 'mch/topic/index',
                        'sub' => [
                            'mch/topic/edit',
                        ],
                    ],
                    [
                        'id' => 'video',
                        'name' => '视频',
                        'route' => 'mch/store/video',
                        'sub' => [
                            'mch/store/video-edit',
                        ],
                    ],
                    [
                        'name' => '货柜',
                        'route' => 'mch/store/shop',
                        'sub' => [
                            'mch/store/shop-edit',
                        ],
                    ],
                ],
            ],
            [
                'name' => '营销管理',
                'route' => 'mch/coupon/index',
                'icon' => 'icon-coupons',
                'list' => [
                    [
                        'id' => 'coupon',
                        'name' => '优惠券',
                        'route' => 'mch/coupon/index',
                        'sub' => [
                            'mch/coupon/send',
                            'mch/coupon/edit',
                        ],
                        'list' => [
                            [
                                'name' => '优惠券管理',
                                'route' => 'mch/coupon/index'
                            ],
                            [
                                'name' => '自动发放设置',
                                'route' => 'mch/coupon/auto-send',
                                'sub' => [
                                    'mch/coupon/auto-send-edit'
                                ]
                            ]
                        ]
                    ],
                    [
                        'name' => '卡券',
                        'route' => 'mch/card/index',
                        'sub' => [
                            'mch/card/edit',
                        ],
                    ],
                ],
            ],
            [
                'name' => '应用专区',
                'route' => 'mch/seckill/index',
                'icon' => 'icon-pintu-m',
                'list' => [
                    [
                        'id' => 'jingpin',
                        'name' => '抽奖管理',
                        'route' => 'mch/choujiang/level',
                        'list' => [
                            [
                                'name' => '奖品列表',
                                'route' => 'mch/choujiang/level',
                            ],
                            [
                                'name' => '奖品等级',
                                'route' => 'mch/choujiang/level-edit',
                                'sub' => [
                                    'mch/choujiang/level-edit',
                                ],
                            ],
                        ],
                    ],
                    [
                        'id' => 'seckill',
                        'name' => '整点秒杀',
                        'route' => 'mch/seckill/index',
                        'list' => [
                            [
                                'name' => '开放时间',
                                'route' => 'mch/seckill/index',
                            ],
                            [
                                'name' => '商品设置',
                                'route' => 'mch/seckill/goods',
                                'sub' => [
                                    'mch/seckill/goods-edit',
                                    'mch/seckill/goods-detail',
                                    'mch/seckill/calendar',
                                ],
                            ],
                        ],
                    ],
                    [
                        'id' => 'pintuan',
                        'name' => '拼团管理',
                        'route' => 'mch/group/goods/index',
                        'list' => [
                            [
                                'name' => '商品管理',
                                'route' => 'mch/group/goods/index',
                                'sub' => [
                                    'mch/group/goods/goods-edit',
                                    'mch/group/goods/goods-attr'
                                ]
                            ],
                            [
                                'name' => '商品分类',
                                'route' => 'mch/group/goods/cat',
                                'sub' => [
                                    'mch/group/goods/cat-edit'
                                ]
                            ],
                            [
                                'name' => '订单管理',
                                'route' => 'mch/group/order/index',
                            ],
                            [
                                'name' => '拼团管理',
                                'route' => 'mch/group/order/group',
                                'sub' => [
                                    'mch/group/order/group-list'
                                ]
                            ],
                            [
                                'name' => '轮播图',
                                'route' => 'mch/group/pt/banner',
                                'sub' => [
                                    'mch/group/pt/slide-edit'
                                ]
                            ],
                            [
                                'name' => '模板消息',
                                'route' => 'mch/group/notice/setting',
                            ],
                            [
                                'name' => '拼团规则',
                                'route' => 'mch/group/article/edit',
                            ],
                            [
                                'name' => '评论管理',
                                'route' => 'mch/group/comment/index',
                            ],
                            [
                                'name' => '广告设置',
                                'route' => 'mch/group/ad/setting',
                            ],
                            [
                                'name' => '数据统计',
                                'route' => 'mch/group/data/goods',
                                'sub' => [
                                    'mch/group/data/user'
                                ]
                            ],
                        ],
                    ],
                ],
            ],

        ];


        $menu_list=$this->kefulist($menu_list);


        $menu_list = $this->resetList($menu_list);
        foreach ($menu_list as $i => $item) {
            if (is_array($item['list']) && count($item['list']) == 0) {
                unset($menu_list[$i]);
                continue;
            }
            if (is_array($item['list'])) {
                $menu_list[$i]['route'] = $item['list'][0]['route'];
            }
        }
        $menu_list = array_values($menu_list);

        return $menu_list;

    }

    private function resetList($list)
    {
        foreach ($list as $i => $item) {
            if (isset($item['id']) && $this->user_auth !== null && !in_array($item['id'], $this->user_auth)) {
                unset($list[$i]);
                continue;
            }
            if (isset($item['list']) && is_array($item['list'])) {
                $list[$i]['list'] = $this->resetList($item['list']);
            }
        }
        $list = array_values($list);
        return $list;
    }


    private function kefulist($menu_list)
    {
        $identity = \Yii::$app->store->identity;
        if($identity->user_id!=10){
            $menu_list=[];
            $menu_list = [

                [
                    'name' => '商场管理',
                    'route' => 'mch/goods/goods',
                    'icon' => 'icon-service',
                    'list' => [
                        [
                            'name' => '订单管理',
                            'route' => 'mch/order/index',
                            'icon' => 'icon-activity',
                            'list' => [
                                [
                                    'name' => '订单列表',
                                    'route' => 'mch/order/index',
                                    'sub' => [
                                        'mch/order/detail'
                                    ]
                                ],
                                [
                                    'name' => '自提订单',
                                    'route' => 'mch/order/offline',
                                ],
                                [
                                    'name' => '售后订单',
                                    'route' => 'mch/order/refund',
                                ],
                            ],
                        ],
                    ],
                ],

                [
                    'name' => '优惠券商场管理',
                    'icon' => 'icon-service',
                    'route' => 'mch/couponmall/goods/index',
                    'list' => [
                        [
                            'name' => '订单管理',
                            'route' => 'mch/couponmall/order/index',
                        ],
                    ],
                ],
            ];


        //判断权限
        $menu_list_limit=[];
        foreach ($menu_list as $i => $item) {
            if (is_array($item['list'])) {
                foreach ($item['list']  as $key => $value ){
                    $menu_list_limit[]='/index.php/'.$value['route'];

                    foreach ($value['list']  as $key_1 => $value_1 ){
                        $menu_list_limit[]='/index.php/'.$value_1['route'];
                        foreach ($value_1['sub']  as $key_2 => $value_2 ){
                            $menu_list_limit[]='/index.php/'.$value_2;
                        }
                    }
                }
            }
        }

            if (!in_array(parse_url(\Yii::$app->request->url)['path'], $menu_list_limit)) {
                echo '<h1/>没有权限查看</h1>';
                die;
//            header("Location: /mch/couponmall/order/index");
            }
        }
        return $menu_list;
    }

}