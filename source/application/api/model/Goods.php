<?php

namespace app\api\model;

use app\common\model\Goods as GoodsModel;

/**
 * 商品模型
 * Class Goods
 * @package app\api\model
 */
class Goods extends GoodsModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'sales_initial',
        'sales_actual',
        'is_delete',
        'wxapp_id',
        'create_time',
        'update_time'
    ];

    /**
     * 商品详情：HTML实体转换回普通字符
     * @param $value
     * @return string
     */
    public function getContentAttr($value)
    {
        return htmlspecialchars_decode($value);
    }

    /**
     * 根据商品id集获取商品列表
     * @param $goodsIds
     * @param null $status
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getListByIds($goodsIds, $status = null)
    {
        $filter = ['goods_id' => ['in', $goodsIds]];
        $status > 0 && $filter['goods_status'] = $status;
        return $this->with(['category', 'image.file', 'sku', 'spec_rel.spec', 'delivery.rule'])
            ->where($filter)
            ->select();
    }

}
