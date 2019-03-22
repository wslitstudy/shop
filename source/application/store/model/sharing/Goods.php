<?php

namespace app\store\model\sharing;

use app\common\model\sharing\Goods as GoodsModel;

/**
 * 拼团商品模型
 * Class Goods
 * @package app\store\model\sharing
 */
class Goods extends GoodsModel
{
    /**
     * 订单列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        // 获取数据列表
        return $this->with(['goods.image.file'])
            ->where('is_delete', '=', 0)
            ->order(['create_time' => 'desc'])
            ->paginate(10, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 检查商品是否已参与拼团
     * @param $goods_id
     * @return bool
     * @throws \think\exception\DbException
     */
    private static function checkGoodsExist($goods_id)
    {
        return !!self::get([
            'goods_id' => $goods_id,
            'is_delete' => 0
        ]);
    }

    /**
     * 新增记录
     * @param $goods_id
     * @return false|int
     * @throws \think\exception\DbException
     */
    public function add($goods_id)
    {
        if (self::checkGoodsExist($goods_id)) {
            $this->error = '该商品已参与拼团，无需重复添加';
            return false;
        }
        return $this->save([
            'goods_id' => (int)$goods_id,
            'people' => 2,
            'group_time' => 2,
            'goods_info' => '',
            'goods_status' => 20,
        ]);
    }

    /**
     * 更新记录
     * @param $data
     * @return bool|int
     */
    public function edit($data)
    {
        return $this->allowField(true)->save($data) !== false;
    }

    /**
     * 修改商品状态
     * @param $state
     * @return false|int
     */
    public function setStatus($state)
    {
        if ($state && empty($this['goods_info'])) {
            $this->error = '请先补充价格信息';
            return false;
        }
        return $this->save(['goods_status' => $state ? 10 : 20]) !== false;
    }

    /**
     * 软删除
     * @return false|int
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]);
    }

}
