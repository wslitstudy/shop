<?php

namespace app\task\behavior;

use think\Cache;
use app\task\model\Order as OrderModel;
use app\task\model\dealer\Order as DealerOrderModel;

/**
 * 分销商订单行为管理
 * Class DealerOrder
 * @package app\task\behavior
 */
class DealerOrder
{
    /* @var DealerOrderModel $model */
    private $model;

    /**
     * 执行函数
     * @param $model
     * @return bool
     * @throws \think\exception\PDOException
     */
    public function run($model)
    {
        if (!$model instanceof DealerOrderModel) {
            return new DealerOrderModel and false;
        }
        $this->model = $model;
        if (!Cache::has('__task_space__DealerOrder')) {
            $this->model->startTrans();
            try {
                // 发放分销订单佣金
                $this->grantMoney();
                $this->model->commit();
            } catch (\Exception $e) {
                $this->model->rollback();
                return false;
            }
            Cache::set('__task_space__DealerOrder', time(), 3600);
        }
        return true;
    }

    /**
     * 发放分销订单佣金
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function grantMoney()
    {
        // 获取未结算佣金的订单id集
        $orderIds = $this->model->getUnSettledOrderIds();
        // 获取订单列表
        $model = new OrderModel;
        $list = $model->getList(['order_id' => ['in', $orderIds], 'order_status' => 30]);
        if ($list->isEmpty()) {
            return false;
        }
        // 记录日志
        $this->dologs('grantMoney', [
            'orderIds' => array_column($list->toArray(), 'order_id'),
        ]);
        foreach ($list as &$order) {
            DealerOrderModel::grantMoney($order);
        }
        return true;
    }

    /**
     * 记录日志
     * @param $method
     * @param array $params
     * @return bool|int
     */
    private function dologs($method, $params = [])
    {
        $value = 'behavior UserCoupon --' . $method;
        foreach ($params as $key => $val) {
            $value .= ' --' . $key . ' ' . (is_array($val) ? json_encode($val) : $val);
        }
        return log_write($value);
    }

}