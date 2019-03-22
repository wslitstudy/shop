<?php

namespace app\store\model\dealer;

use app\common\model\dealer\Order as OrderModel;

/**
 * 分销商订单模型
 * Class Apply
 * @package app\store\model\dealer
 */
class Order extends OrderModel
{
    /**
     * 订单列表
     * @param null $user_id
     * @param int $is_settled
     * @param string $search
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($user_id = null, $is_settled = -1, $search = '')
    {
        // 构建查询规则
        $this->alias('master')->field('master.*')
            ->with([
                'orderMaster' => ['goods.image', 'address', 'user'],
                'dealer_first.user',
                'dealer_second.user',
                'dealer_third.user'
            ])
            ->join('order', 'order.order_id = master.order_id')
            ->order(['create_time' => 'desc']);
        // 查询条件
        $user_id > 1 && $this->where('master.first_user_id|master.second_user_id|master.third_user_id', '=', (int)$user_id);
        $is_settled > -1 && $is_settled !== '' && $this->where('master.is_settled', '=', (int)$is_settled);
        !empty($search) && $this->where('order.order_no', 'like', "%$search%");
        // 获取列表数据
        return $this->paginate(10, false, [
            'query' => \request()->request()
        ]);
    }

}