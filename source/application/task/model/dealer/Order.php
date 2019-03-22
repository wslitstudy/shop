<?php

namespace app\task\model\dealer;

use app\common\model\dealer\Order as OrderModel;

/**
 * 分销商订单模型
 * Class Apply
 * @package app\task\model\dealer
 */
class Order extends OrderModel
{
    /**
     * 获取未结算的优惠券ID集
     * @return array
     */
    public function getUnSettledOrderIds()
    {
        return $this->where('is_settled', '=', 0)->column('order_id');
    }

}