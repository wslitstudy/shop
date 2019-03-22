<?php

namespace app\store\controller\order;

use app\store\controller\Controller;
use app\store\model\Order as OrderModel;
use app\store\model\Express as ExpressModel;

/**
 * 订单操作控制器
 * Class Operate
 * @package app\store\controller\order
 */
class Operate extends Controller
{
    /* @var OrderModel $model */
    private $model;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new OrderModel;
    }

    /**
     * 订单导出
     * @param string $dataType
     * @throws \think\exception\DbException
     */
    public function export($dataType)
    {
        return $this->model->exportList($dataType, $this->request->get());
    }

    /**
     * 批量发货
     * @return array|mixed
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\PDOException
     */
    public function batchDelivery()
    {
        if (!$this->request->isAjax()) {
            return $this->fetch('batchDelivery', [
                'express_list' => ExpressModel::getAll()
            ]);
        }
        if ($this->model->batchDelivery($this->postData('order'))) {
            return $this->renderSuccess('发货成功');
        }
        return $this->renderError($this->model->getError() ?: '发货失败');
    }

    /**
     * 批量发货模板
     */
    public function deliveryTpl()
    {
        return $this->model->deliveryTpl();
    }

    /**
     * 审核：用户取消订单
     * @param $order_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function confirmCancel($order_id)
    {
        $model = OrderModel::detail($order_id);
        if ($model->confirmCancel($this->postData('order'))) {
            return $this->renderSuccess('审核成功');
        }
        return $this->renderError($model->getError() ?: '审核失败');
    }

}
