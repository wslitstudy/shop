<?php

namespace app\common\model\sharing;

use app\common\model\BaseModel;

/**
 * 拼团商品模型
 * Class Goods
 * @package app\common\model\sharing
 */
class Goods extends BaseModel
{
    protected $name = 'sharing_goods';

    /**
     * 格式化拼团商品信息
     * @param $json
     * @return array
     */
    public function getGoodsInfoAttr($json)
    {
        return json_decode($json, true);
    }

    /**
     * 自动转换data为json格式
     * @param $value
     * @return string
     */
    public function setGoodsInfoAttr($value)
    {
        return json_encode($value ?: []);
    }

    /**
     * 拼团商品详情
     * @param $where
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($where)
    {
        return static::get($where, [
            'goods' => ['image.file', 'sku', 'spec_rel.spec']
        ]);
    }

    /**
     * 订单商品列表
     * @return \think\model\relation\BelongsTo
     */
    public function goods()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\Goods", 'goods_id');
    }

}
