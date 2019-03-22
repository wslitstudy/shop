<?php

namespace app\task\model;

use think\Db;
use app\common\service\Message;
use app\common\model\Order as OrderModel;

/**
 * 订单模型
 * Class Order
 * @package app\common\model
 */
class Order extends OrderModel
{
    /**
     * 待支付订单详情
     * @param $order_no
     * @return null|static
     * @throws \think\exception\DbException
     */
    public function payDetail($order_no)
    {
        return self::get(['order_no' => $order_no, 'pay_status' => 10], ['goods', 'user']);
    }

    /**
     * 订单支付成功业务处理
     * @param $transaction_id
     * @throws \Exception
     * @throws \think\Exception
     */
    public function paySuccess($transaction_id)
    {
        // 更新付款状态
        $this->updatePayStatus($transaction_id);
        // 发送消息通知
        $Message = new Message;
        $Message->payment($this);
    }

    /**
     * 更新付款状态
     * @param $transaction_id
     * @return false|int
     * @throws \Exception
     */
    private function updatePayStatus($transaction_id)
    {
        Db::startTrans();
        try {
            // 更新商品库存、销量
            $GoodsModel = new Goods;
            $GoodsModel->updateStockSales($this['goods']);
            // 更新订单状态
            $this->save([
                'pay_status' => 20,
                'pay_time' => time(),
                'transaction_id' => $transaction_id
            ]);
            // 累积用户总消费金额
            $user = User::detail($this['user_id']);
            $user->cumulateMoney($this['pay_price']);
            // 更新prepay_id记录
            //$prepayId = WxappPrepayIdModel::detail($this['order_id']);
            //$prepayId->updatePayStatus();
            // 事务提交
            Db::commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Db::rollback();
            return false;
        }
    }

    /**
     * 获取订单列表
     * @param array $filter
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList($filter = [])
    {
        return $this->with(['goods' => ['refund']])->where($filter)->select();
    }

}
