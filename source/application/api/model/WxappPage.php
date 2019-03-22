<?php

namespace app\api\model;

use app\common\model\WxappPage as WxappPageModel;

/**
 * 微信小程序diy页面模型
 * Class WxappPage
 * @package app\api\model
 */
class WxappPage extends WxappPageModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
        'create_time',
        'update_time'
    ];

    /**
     * DIY页面详情
     * @param User $user
     * @param int $page_id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getPageItems($user, $page_id = null)
    {
        $model = new self;
        $detail = $page_id > 0 ? parent::detail($page_id) : parent::getHomePage();
        $items = $detail['page_data']['array']['items'];
        foreach ($items as $key => $item) {
            if ($item['type'] === 'window') {
                $items[$key]['data'] = array_values($item['data']);
            } else if ($item['type'] === 'goods') {
                $items[$key]['data'] = $model->getGoodsList($item);
            } else if ($item['type'] === 'coupon') {
                $items[$key]['data'] = $model->getCouponList($user, $item);
            }
        }
        return $items;
    }

    /**
     * 商品组件：获取商品列表
     * @param $item
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getGoodsList($item)
    {
        // 获取商品数据
        $Goods = new Goods;
        if ($item['params']['source'] === 'choice') {
            // 数据来源：手动
            $goodsIds = array_column($item['data'], 'goods_id');
            $goodsList = $Goods->getListByIds($goodsIds, 10);
        } else {
            // 数据来源：自动
            $goodsList = $Goods->getList(10, $item['params']['auto']['category'], '',
                $item['params']['auto']['goodsSort'], false, $item['params']['auto']['showNum']);
        }
        // 格式化商品列表
        $data = [];
        foreach ($goodsList as $goods) {
            $data[] = [
                'goods_id' => $goods['goods_id'],
                'goods_name' => $goods['goods_name'],
                'image' => $goods['image'][0]['file_path'],
                'goods_price' => $goods['sku'][0]['goods_price'],
            ];
        }
        return $data;
    }

    /**
     * 优惠券组件：获取优惠券列表
     * @param $user
     * @param $item
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getCouponList($user, $item)
    {
        // 获取优惠券数据
        return (new Coupon)->getList($user, $item['params']['limit'], true);
    }

}
