<?php

namespace app\store\model;

use think\Request;
use app\common\service\Message;
use app\common\library\wechat\WxPay;
use app\common\exception\BaseException;
use app\common\model\Order as OrderModel;

/**
 * 订单管理
 * Class Order
 * @package app\store\model
 */
class Order extends OrderModel
{
    /**
     * 订单列表
     * @param string $dataType
     * @param array $query
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($dataType, $query = [])
    {
        // 检索查询
        $this->setWhere($query);
        // 获取数据列表
        return $this->with(['goods.image', 'address', 'user'])
            ->where($this->transferDataType($dataType))
            ->order(['create_time' => 'desc'])
            ->paginate(10, false, [
                'query' => Request::instance()->request()
            ]);
    }

    /**
     * 订单列表(全部)
     * @param $dataType
     * @param array $query
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getListAll($dataType, $query = [])
    {
        // 检索查询
        $this->setWhere($query);
        // 获取数据列表
        return $this->with(['goods.image', 'address', 'user'])
            ->where($this->transferDataType($dataType))
            ->order(['create_time' => 'desc'])
            ->select();
    }

    /**
     * 订单导出
     * @param $dataType
     * @param $query
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function exportList($dataType, $query)
    {
        // 获取订单列表
        $list = $this->getListAll($dataType, $query);
        // 表格标题
        $tileArray = ['订单号', '商品名称', '单价', '数量', '付款金额', '运费金额', '下单时间',
            '买家', '买家留言', '收货人姓名', '联系电话', '收货人地址', '物流公司', '物流单号',
            '付款状态', '付款时间', '发货状态', '发货时间', '收货状态', '收货时间', '订单状态',
            '微信支付交易号', '是否已评价'];
        // 表格内容
        $dataArray = [];
        foreach ($list as $order) {
            /* @var OrderAddress $address */
            $address = $order['address'];
            foreach ($order['goods'] as $goods) {
                $dataArray[] = [
                    '订单号' => $this->filterValue($order['order_no']),
                    '商品名称' => $goods['goods_name'],
                    '单价' => $goods['goods_price'],
                    '数量' => $goods['total_num'],
                    '付款金额' => $this->filterValue($order['pay_price']),
                    '运费金额' => $this->filterValue($order['express_price']),
                    '下单时间' => $this->filterValue($order['create_time']),
                    '买家' => $this->filterValue($order['user']['nickName']),
                    '买家留言' => $this->filterValue($order['buyer_remark']),
                    '收货人姓名' => $this->filterValue($order['address']['name']),
                    '联系电话' => $this->filterValue($order['address']['phone']),
                    '收货人地址' => $this->filterValue($address ? $address->getFullAddress() : ''),
                    '物流公司' => $this->filterValue($order['express']['express_name']),
                    '物流单号' => $this->filterValue($order['express_no']),
                    '付款状态' => $this->filterValue($order['pay_status']['text']),
                    '付款时间' => $this->filterTime($order['pay_time']),
                    '发货状态' => $this->filterValue($order['delivery_status']['text']),
                    '发货时间' => $this->filterTime($order['delivery_time']),
                    '收货状态' => $this->filterValue($order['receipt_status']['text']),
                    '收货时间' => $this->filterTime($order['receipt_time']),
                    '订单状态' => $this->filterValue($order['order_status']['text']),
                    '微信支付交易号' => $this->filterValue($order['transaction_id']),
                    '是否已评价' => $this->filterValue($order['is_comment'] ? '是' : '否'),
                ];
            }
        }
        // 导出csv文件
        $filename = 'order-' . date('YmdHis');
        return export_excel($filename . '.csv', $tileArray, $dataArray);
    }

    /**
     * 批量发货模板
     */
    public function deliveryTpl()
    {
        // 导出csv文件
        $filename = 'delivery-' . date('YmdHis');
        return export_excel($filename . '.csv', ['订单号', '物流单号']);
    }

    /**
     * 表格值过滤
     * @param $value
     * @return string
     */
    private function filterValue($value)
    {
        return "\t" . $value . "\t";
    }

    /**
     * 日期值过滤
     * @param $value
     * @return string
     */
    private function filterTime($value)
    {
        if (!$value) return '';
        return $this->filterValue(date('Y-m-d H:i:s', $value));
    }

    /**
     * 设置检索查询条件
     * @param $query
     */
    private function setWhere($query)
    {
        if (isset($query['order_no'])) {
            !empty($query['order_no']) && $this->where('order_no', 'like', '%' . trim($query['order_no']) . '%');
        }
        if (isset($query['start_time'])) {
            !empty($query['start_time']) && $this->where('create_time', '>=', strtotime($query['start_time']));
        }
        if (isset($query['end_time'])) {
            !empty($query['end_time']) && $this->where('create_time', '<', strtotime($query['end_time']) + 86400);
        }
    }

    /**
     * 转义数据类型条件
     * @param $dataType
     * @return array
     */
    private function transferDataType($dataType)
    {
        // 数据类型
        $filter = [];
        switch ($dataType) {
            case 'delivery':
                $filter = [
                    'pay_status' => 20,
                    'delivery_status' => 10,
                    'order_status' => ['in', [10, 21]]
                ];
                break;
            case 'receipt':
                $filter = [
                    'pay_status' => 20,
                    'delivery_status' => 20,
                    'receipt_status' => 10
                ];
                break;
            case 'pay':
                $filter = ['pay_status' => 10, 'order_status' => 10];
                break;
            case 'complete':
                $filter = ['order_status' => 30];
                break;
            case 'cancel':
                $filter = ['order_status' => 20];
                break;
            case 'all':
                $filter = [];
                break;
        }
        return $filter;
    }

    /**
     * 确认发货
     * @param $data
     * @param bool $sendMsg 是否发送消息通知
     * @return bool|false|int
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function delivery($data, $sendMsg = true)
    {
        if ($this['pay_status']['value'] == 10
            || $this['delivery_status']['value'] == 20) {
            $this->error = '该订单不合法';
            return false;
        }
        // 更新订单状态
        $status = $this->save([
            'express_id' => $data['express_id'],
            'express_no' => $data['express_no'],
            'delivery_status' => 20,
            'delivery_time' => time(),
        ]);
        // 发送消息通知
        ($status && $sendMsg) && $this->deliveryMessage($this['order_id']);
        return $status;
    }

    /**
     * 确认发货后发送消息通知
     * @param $order_id
     * @return bool
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    private function deliveryMessage($order_id)
    {
        $orderIds = is_array($order_id) ? $order_id : [$order_id];
        // 发送消息通知
        $Message = new Message;
        foreach ($orderIds as $orderId) {
            $Message->delivery(self::detail($orderId));
        }
        return true;
    }

    /**
     * 确认批量发货
     * @param $data
     * @return bool
     * @throws BaseException
     * @throws \think\exception\PDOException
     */
    public function batchDelivery($data)
    {
        // 获取csv文件中的数据
        $csvData = $this->getCsvData();
        // 批量发货
        $this->startTrans();
        try {
            $orderIds = [];
            foreach ($csvData as $item) {
                if (!isset($item[0])
                    || empty($item[0])
                    || !isset($item[1])
                    || empty($item[1])
                ) {
                    $this->error = '模板文件数据不合法';
                    return false;
                }
                if (!$model = self::detail(['order_no' => trim($item[0])])) {
                    $this->error = '订单号 ' . $item[0] . ' 不存在';
                    return false;
                }
                if (!$status = $model->delivery([
                    'express_id' => $data['express_id'],
                    'express_no' => trim($item[1]),
                ], false)) {
                    $this->error = ' 订单号：' . $item[0] . ' ' . $model->error;
                    return false;
                }
                $orderIds[] = $model['order_id'];
            }
            // 发送消息通知
            $this->deliveryMessage($orderIds);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 获取csv文件中的数据
     * @return array
     * @throws BaseException
     */
    private function getCsvData()
    {
        // 获取表单上传文件 例如上传了001.jpg
        if (!$file = \request()->file('iFile')) {
            throw new BaseException(['msg' => '请上传发货模板']);
        }
        setlocale(LC_ALL, 'zh_CN');
        $data = [];
        $csvFile = fopen($file->getInfo()['tmp_name'], 'r');
        while ($row = fgetcsv($csvFile)) {
            $data[] = $row;
        }
        if (count($data) <= 1) {
            throw new BaseException(['msg' => '模板文件中没有订单数据']);
        }
        // 删除csv标题
        unset($data[0]);
        return $data;
    }

    /**
     * 修改订单价格
     * @param $data
     * @return bool
     */
    public function updatePrice($data)
    {
        if ($this['pay_status']['value'] != 10) {
            $this->error = '该订单不合法';
            return false;
        }
        // 实际付款金额
        $payPrice = bcadd($data['update_price'], $data['update_express_price'], 2);
        if ($payPrice <= 0) {
            $this->error = '订单实付款价格不能为0.00元';
            return false;
        }
        return $this->save([
                'order_no' => $this->orderNo(), // 修改订单号, 否则微信支付提示重复
                'pay_price' => $payPrice,
                'update_price' => $data['update_price'] - ($this['total_price'] - $this['coupon_price']),
                'express_price' => $data['update_express_price']
            ]) !== false;
    }

    /**
     * 审核：用户取消订单
     * @param $data
     * @return bool
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function confirmCancel($data)
    {
        if ($this['pay_status']['value'] != 20) {
            $this->error = '该订单不合法';
            return false;
        }
        // 微信支付原路退款
        if ($data['is_cancel'] == true) {
            $wxConfig = Wxapp::getWxappCache();
            $WxPay = new WxPay($wxConfig);
            $WxPay->refund($this['transaction_id'], $this['pay_price'], $this['pay_price']);
        }
        return $this->save(['order_status' => $data['is_cancel'] ? 20 : 10]) !== false;
    }

    /**
     * 获取已付款订单总数 (可指定某天)
     * @param null $day
     * @return int|string
     */
    public function getPayOrderTotal($day = null)
    {
        $filter = ['pay_status' => 20];
        if (!is_null($day)) {
            $startTime = strtotime($day);
            $filter['pay_time'] = [
                ['>=', $startTime],
                ['<', $startTime + 86400],
            ];
        }
        return $this->getOrderTotal($filter);
    }

    /**
     * 获取订单总数量
     * @param array $filter
     * @return int|string
     */
    public function getOrderTotal($filter = [])
    {
        return $this->where($filter)->count();
    }

    /**
     * 获取某天的总销售额
     * @param $day
     * @return float|int
     */
    public function getOrderTotalPrice($day)
    {
        $startTime = strtotime($day);
        return $this->where('pay_time', '>=', $startTime)
            ->where('pay_time', '<', $startTime + 86400)
            ->where('pay_status', '=', 20)
            ->sum('pay_price');
    }

    /**
     * 获取某天的下单用户数
     * @param $day
     * @return float|int
     */
    public function getPayOrderUserTotal($day)
    {
        $startTime = strtotime($day);
        $userIds = $this->distinct(true)
            ->where('pay_time', '>=', $startTime)
            ->where('pay_time', '<', $startTime + 86400)
            ->where('pay_status', '=', 20)
            ->column('user_id');
        return count($userIds);
    }

}
