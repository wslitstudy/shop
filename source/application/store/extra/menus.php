<?php
/**
 * 后台菜单配置
 *    'home' => [
 *       'name' => '首页',                // 菜单名称
 *       'icon' => 'icon-home',          // 图标 (class)
 *       'index' => 'index/index',         // 链接
 *     ],
 */
return [
    'index' => [
        'name' => '首页',
        'icon' => 'icon-home',
        'index' => 'index/index',
    ],
    'store' => [
        'name' => '管理员',
        'icon' => 'icon-guanliyuan',
        'index' => 'store.user/index',
        'submenu' => [
            [
                'name' => '管理员列表',
                'index' => 'store.user/index',
                'uris' => [
                    'store.user/index',
                    'store.user/add',
                    'store.user/edit',
                    'store.user/delete',
                ],
            ],
            [
                'name' => '角色管理',
                'index' => 'store.role/index',
                'uris' => [
                    'store.role/index',
                    'store.role/add',
                    'store.role/edit',
                    'store.role/delete',
                ],
            ],
            [
                'name' => '权限管理',
                'index' => 'store.access/index',
                'uris' => [
                    'store.access/index',
                    'store.access/add',
                    'store.access/edit',
                    'store.access/delete',
                ],
            ],
        ]
    ],
    'goods' => [
        'name' => '商品管理',
        'icon' => 'icon-goods',
        'index' => 'goods/index',
        'submenu' => [
            [
                'name' => '商品列表',
                'index' => 'goods/index',
                'uris' => [
                    'goods/index',
                    'goods/add',
                    'goods/edit',
                    'goods/copy'
                ],
            ],
            [
                'name' => '商品分类',
                'index' => 'goods.category/index',
                'uris' => [
                    'goods.category/index',
                    'goods.category/add',
                    'goods.category/edit',
                ],
            ],
            [
                'name' => '商品评价',
                'index' => 'goods.comment/index',
                'uris' => [
                    'goods.comment/index',
                    'goods.comment/detail',
                ],
            ]
        ],
    ],
    'order' => [
        'name' => '订单管理',
        'icon' => 'icon-order',
        'index' => 'order/delivery_list',
        'submenu' => [
            [
                'name' => '待发货',
                'index' => 'order/delivery_list',
            ],
            [
                'name' => '待收货',
                'index' => 'order/receipt_list',
            ],
            [
                'name' => '待付款',
                'index' => 'order/pay_list',
            ],
            [
                'name' => '已完成',
                'index' => 'order/complete_list',

            ],
            [
                'name' => '已取消',
                'index' => 'order/cancel_list',
            ],
            [
                'name' => '全部订单',
                'index' => 'order/all_list',
            ],
            [
                'name' => '售后管理',
                'index' => 'order.refund/index',
                'uris' => [
                    'order.refund/index',
                    'order.refund/detail',
                ]
            ],
        ]
    ],
    'user' => [
        'name' => '用户管理',
        'icon' => 'icon-user',
        'index' => 'user/index',
    ],
    'market' => [
        'name' => '营销设置',
        'icon' => 'icon-marketing',
        'index' => 'market.coupon/index',
        'submenu' => [
            [
                'name' => '优惠券',
                'active' => true,
                'submenu' => [
                    [
                        'name' => '优惠券列表',
                        'index' => 'market.coupon/index',
                        'uris' => [
                            'market.coupon/index',
                            'market.coupon/add',
                            'market.coupon/edit',
                        ]
                    ],
                    [
                        'name' => '领取记录',
                        'index' => 'market.coupon/receive'
                    ],
                ]
            ],
        ],
    ],
    'apps' => [
        'name' => '应用中心',
        'icon' => 'icon-application',
        'is_svg' => true,   // 多色图标
        'index' => 'apps.dealer.apply/index',
        'submenu' => [
            [
                'name' => '分销中心',
                'submenu' => [
                    [
                        'name' => '入驻申请',
                        'index' => 'apps.dealer.apply/index',
                    ],
                    [
                        'name' => '分销商用户',
                        'index' => 'apps.dealer.user/index',
                    ],
                    [
                        'name' => '分销订单',
                        'index' => 'apps.dealer.order/index',
                    ],
                    [
                        'name' => '提现申请',
                        'index' => 'apps.dealer.withdraw/index',
                    ],
                    [
                        'name' => '分销设置',
                        'index' => 'apps.dealer.setting/index',
                    ],
                    [
                        'name' => '分销海报',
                        'index' => 'apps.dealer.setting/qrcode',
                    ],
                ]
            ],
        ]
    ],
    'setting' => [
        'name' => '设置',
        'icon' => 'icon-setting',
        'index' => 'setting/store',
        'submenu' => [
            [
                'name' => '商城设置',
                'index' => 'setting/store',
            ],
            [
                'name' => '交易设置',
                'index' => 'setting/trade',
            ],
            [
                'name' => '配送设置',
                'index' => 'setting.delivery/index',
                'uris' => [
                    'setting.delivery/index',
                    'setting.delivery/add',
                    'setting.delivery/edit',
                ],
            ],
            [
                'name' => '物流公司',
                'index' => 'setting.express/index',
                'uris' => [
                    'setting.express/index',
                    'setting.express/add',
                    'setting.express/edit',
                ],
            ],
            [
                'name' => '短信通知',
                'index' => 'setting/sms'
            ],
            /*[
                'name' => '模板消息',
                'index' => 'setting/tplmsg',
                'uris' => [
                    'setting/tplmsg',
                    'setting.help/tplmsg'

                ],
            ],*/
            [
                'name' => '退货地址',
                'index' => 'setting.address/index',
                'uris' => [
                    'setting.address/index',
                    'setting.address/add',
                    'setting.address/edit',
                ],
            ],
            [
                'name' => '上传设置',
                'index' => 'setting/storage',
            ],
            [
                'name' => '其他',
                'active' => true,
                'submenu' => [
                    [
                        'name' => '清理缓存',
                        'index' => 'setting.cache/clear'
                    ]
                ]
            ]
        ],
    ],
];
