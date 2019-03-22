<?php

namespace app\api\model;

use app\common\model\Order as OrderModel;
use app\api\model\dealer\Order as DealerOrderModel;
use app\common\exception\BaseException;

/**
 * 订单模型
 * Class Order
 * @package app\api\model
 */
class Order extends OrderModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
        'update_time'
    ];

    /**
     * 订单确认-立即购买
     * @param User $user
     * @param $goods_id
     * @param $goods_num
     * @param $goods_sku_id
     * @return array
     * @throws \think\exception\DbException
     * @throws \app\common\exception\BaseException
     */
    public function getBuyNow($user, $goods_id, $goods_num, $goods_sku_id)
    {
        // 商品信息
        /* @var Goods $goods */
        $goods = Goods::detail($goods_id);
        // 判断商品是否下架
        if (!$goods || $goods['is_delete'] || $goods['goods_status']['value'] != 10) {
            throw new BaseException(['msg' => '很抱歉，商品信息不存在或已下架']);
        }
        // 商品sku信息
        $goods['goods_sku'] = $goods->getGoodsSku($goods_sku_id);
        // 判断商品库存
        if ($goods_num > $goods['goods_sku']['stock_num']) {
            $this->setError('很抱歉，商品库存不足');
        }
        // 商品单价
        $goods['goods_price'] = $goods['goods_sku']['goods_price'];
        // 商品总价
        $goods['total_num'] = $goods_num;
        $goods['total_price'] = $totalPrice = bcmul($goods['goods_price'], $goods_num, 2);
        // 商品总重量
        $goods_total_weight = bcmul($goods['goods_sku']['goods_weight'], $goods_num, 2);
        // 当前用户收货城市id
        $cityId = $user['address_default'] ? $user['address_default']['city_id'] : null;
        // 是否存在收货地址
        $exist_address = !$user['address']->isEmpty();
        // 验证用户收货地址是否存在运费规则中
        if (!$intraRegion = $goods['delivery']->checkAddress($cityId)) {
            $exist_address && $this->setError('很抱歉，您的收货地址不在配送范围内');
        }
        // 计算配送费用
        $expressPrice = $intraRegion ?
            $goods['delivery']->calcTotalFee($goods_num, $goods_total_weight, $cityId) : 0;
        // 订单总金额 (含运费)
        $orderPayPrice = bcadd($totalPrice, $expressPrice, 2);
        // 可用优惠券列表
        $couponList = UserCoupon::getUserCouponList($user['user_id'], $totalPrice);

        return [
            'goods_list' => [$goods],               // 商品详情
            'order_total_num' => $goods_num,        // 商品总数量
            'order_total_price' => $totalPrice,     // 商品总金额 (不含运费)
            'order_pay_price' => $orderPayPrice,    // 订单总金额 (含运费)
            'coupon_list' => array_values($couponList),   // 优惠券列表
            'address' => $user['address_default'],  // 默认地址
            'exist_address' => $exist_address,      // 是否存在收货地址
            'express_price' => $expressPrice,       // 配送费用
            'intra_region' => $intraRegion,         // 当前用户收货城市是否存在配送规则中
            'has_error' => $this->hasError(),
            'error_msg' => $this->getError(),
        ];
    }

    /**
     * 创建新订单
     * @param $user_id
     * @param $order
     * @param $coupon_id
     * @param string $remark
     * @return bool
     * @throws \Exception
     */
    public function createOrder($user_id, $order, $coupon_id = null, $remark = '')
    {
        if (empty($order['address'])) {
            $this->error = '请先选择收货地址';
            return false;
        }
        $this->startTrans();
        try {
            // 设置订单优惠券信息
            $this->setCouponPrice($order, $coupon_id);
            // 记录订单信息
            $this->add($user_id, $order, $remark);
            // 保存订单商品信息
            $this->saveOrderGoods($user_id, $order);
            // 更新商品库存 (针对下单减库存的商品)
            $this->updateGoodsStockNum($order['goods_list']);
            // 记录收货地址
            $this->saveOrderAddress($user_id, $order['address']);
            // 获取订单详情
            $detail = self::getUserOrderDetail($this['order_id'], $user_id);
            // 记录分销商订单
            DealerOrderModel::createOrder($detail);
            // 事务提交
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 设置订单优惠券信息
     * @param $order
     * @param $coupon_id
     * @return bool
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    private function setCouponPrice(&$order, $coupon_id)
    {
        if ($coupon_id > 0 && !empty($order['coupon_list'])) {
            // 获取优惠券信息
            $couponInfo = [];
            foreach ($order['coupon_list'] as $coupon)
                $coupon['user_coupon_id'] == $coupon_id && $couponInfo = $coupon;
            if (empty($couponInfo)) throw new BaseException(['msg' => '未找到优惠券信息']);
            // 计算订单金额 (抵扣后)
            $orderTotalPrice = bcsub($order['order_total_price'], $couponInfo['reduced_price'], 2);
            $orderTotalPrice <= 0 && $orderTotalPrice = '0.01';
            // 记录订单信息
            $order['coupon_id'] = $coupon_id;
            $order['coupon_price'] = $couponInfo['reduced_price'];
            $order['order_pay_price'] = bcadd($orderTotalPrice, $order['express_price'], 2);
            // 设置优惠券使用状态
            $model = UserCoupon::detail($coupon_id);
            $model->setIsUse();
            return true;
        }
        $order['coupon_id'] = 0;
        $order['coupon_price'] = 0.00;
        return true;
    }

    /**
     * 新增订单记录
     * @param $user_id
     * @param $order
     * @param string $remark
     * @return false|int
     */
    private function add($user_id, &$order, $remark = '')
    {
        return $this->save([
            'user_id' => $user_id,
            'wxapp_id' => self::$wxapp_id,
            'order_no' => $this->orderNo(),
            'total_price' => $order['order_total_price'],
            'coupon_id' => $order['coupon_id'],
            'coupon_price' => $order['coupon_price'],
            'pay_price' => $order['order_pay_price'],
            'express_price' => $order['express_price'],
            'buyer_remark' => trim($remark),
        ]);
    }

    /**
     * 保存订单商品信息
     * @param $user_id
     * @param $order
     * @return int
     */
    private function saveOrderGoods($user_id, &$order)
    {
        // 订单商品列表
        $goodsList = [];
        // 订单商品实付款金额 (不包含运费)
        $realTotalPrice = bcsub($order['order_pay_price'], $order['express_price'], 2);
        foreach ($order['goods_list'] as $goods) {
            /* @var Goods $goods */
            // 计算商品实际付款价
            $total_pay_price = $realTotalPrice * $goods['total_price'] / $order['order_total_price'];
            $goodsList[] = [
                'user_id' => $user_id,
                'wxapp_id' => self::$wxapp_id,
                'goods_id' => $goods['goods_id'],
                'goods_name' => $goods['goods_name'],
                'image_id' => $goods['image'][0]['image_id'],
                'deduct_stock_type' => $goods['deduct_stock_type'],
                'spec_type' => $goods['spec_type'],
                'spec_sku_id' => $goods['goods_sku']['spec_sku_id'],
                'goods_sku_id' => $goods['goods_sku']['goods_sku_id'],
                'goods_attr' => $goods['goods_sku']['goods_attr'],
                'content' => $goods['content'],
                'goods_no' => $goods['goods_sku']['goods_no'],
                'goods_price' => $goods['goods_sku']['goods_price'],
                'line_price' => $goods['goods_sku']['line_price'],
                'goods_weight' => $goods['goods_sku']['goods_weight'],
                'total_num' => $goods['total_num'],
                'total_price' => $goods['total_price'],
                'total_pay_price' => sprintf('%.2f', $total_pay_price),
            ];
        }
        return $this->goods()->saveAll($goodsList);
    }

    /**
     * 更新商品库存 (针对下单减库存的商品)
     * @param $goods_list
     * @throws \Exception
     */
    private function updateGoodsStockNum($goods_list)
    {
        $deductStockData = [];
        foreach ($goods_list as $goods) {
            // 下单减库存
            $goods['deduct_stock_type'] == 10 && $deductStockData[] = [
                'goods_sku_id' => $goods['goods_sku']['goods_sku_id'],
                'stock_num' => ['dec', $goods['total_num']]
            ];
        }
        !empty($deductStockData) && (new GoodsSku)->isUpdate()->saveAll($deductStockData);
    }

    /**
     * 记录收货地址
     * @param $user_id
     * @param $address
     * @return false|\think\Model
     */
    private function saveOrderAddress($user_id, $address)
    {
        if ($address['region_id'] == 0 && !empty($address['district'])) {
            $address['detail'] = $address['district'] . ' ' . $address['detail'];
        }
        return $this->address()->save([
            'user_id' => $user_id,
            'wxapp_id' => self::$wxapp_id,
            'name' => $address['name'],
            'phone' => $address['phone'],
            'province_id' => $address['province_id'],
            'city_id' => $address['city_id'],
            'region_id' => $address['region_id'],
            'detail' => $address['detail'],
        ]);
    }

    /**
     * 用户中心订单列表
     * @param $user_id
     * @param string $type
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList($user_id, $type = 'all')
    {
        // 筛选条件
        $filter = [];
        // 订单数据类型
        switch ($type) {
            case 'all':
                break;
            case 'payment';
                $filter['pay_status'] = 10;
                break;
            case 'delivery';
                $filter['pay_status'] = 20;
                $filter['delivery_status'] = 10;
                break;
            case 'received';
                $filter['pay_status'] = 20;
                $filter['delivery_status'] = 20;
                $filter['receipt_status'] = 10;
                break;
            case 'comment';
                $filter['order_status'] = 30;
                $filter['is_comment'] = 0;
                break;
        }
        return $this->with(['goods.image'])
            ->where('user_id', '=', $user_id)
            ->where('order_status', '<>', 20)
            ->where($filter)
            ->order(['create_time' => 'desc'])
            ->select();
    }

    /**
     * 取消订单
     * @return bool|false|int
     * @throws \Exception
     */
    public function cancel()
    {
        if ($this['delivery_status']['value'] == 20) {
            $this->error = '已发货订单不可取消';
            return false;
        }
        try {
            // 回退商品库存
            $this->backGoodsStock($this['goods']);
            // 已付款订单
            if ($this['pay_status']['value'] == 20) {
                return $this->save(['order_status' => 21]);
            }
            return $this->save(['order_status' => 20]);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 回退商品库存
     * @param $goodsList
     * @return array|false
     * @throws \Exception
     */
    private function backGoodsStock(&$goodsList)
    {
        $goodsSpecSave = [];
        foreach ($goodsList as $goods) {
            // 下单减库存
            if ($goods['deduct_stock_type'] == 10) {
                $goodsSpecSave[] = [
                    'goods_sku_id' => $goods['goods_sku_id'],
                    'stock_num' => ['inc', $goods['total_num']]
                ];
            }
        }
        // 更新商品规格库存
        return !empty($goodsSpecSave) && (new GoodsSku)->isUpdate()->saveAll($goodsSpecSave);
    }

    /**
     * 确认收货
     * @return bool
     * @throws \think\exception\PDOException
     */
    public function receipt()
    {
        // 验证订单是否合法
        if ($this['delivery_status']['value'] == 10 || $this['receipt_status']['value'] == 20) {
            $this->error = '该订单不合法';
            return false;
        }
        $this->startTrans();
        try {
            // 更新订单状态
            $this->save([
                'receipt_status' => 20,
                'receipt_time' => time(),
                'order_status' => 30
            ]);
            // 发放分销商佣金
            DealerOrderModel::grantMoney($this);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 获取订单总数
     * @param $user_id
     * @param string $type
     * @return int|string
     */
    public function getCount($user_id, $type = 'all')
    {
        // 筛选条件
        $filter = [];
        // 订单数据类型
        switch ($type) {
            case 'all':
                break;
            case 'payment';
                $filter['pay_status'] = 10;
                break;
            case 'received';
                $filter['pay_status'] = 20;
                $filter['delivery_status'] = 20;
                $filter['receipt_status'] = 10;
                break;
            case 'comment';
                $filter['order_status'] = 30;
                $filter['is_comment'] = 0;
                break;
        }
        return $this->where('user_id', '=', $user_id)
            ->where('order_status', '<>', 20)
            ->where($filter)
            ->count();
    }

    /**
     * 订单详情
     * @param $order_id
     * @param null $user_id
     * @return null|static
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    public static function getUserOrderDetail($order_id, $user_id)
    {
        if (!$order = self::get([
            'order_id' => $order_id,
            'user_id' => $user_id,
            'order_status' => ['<>', 20]
        ], ['goods' => ['image', 'sku', 'goods', 'refund'], 'address', 'express'])) {
            throw new BaseException(['msg' => '订单不存在']);
        }
        return $order;
    }

    /**
     * 判断商品库存不足 (未付款订单)
     * @param $goodsList
     * @return bool
     */
    public function checkGoodsStatusFromOrder(&$goodsList)
    {
        foreach ($goodsList as $goods) {
            // 判断商品是否下架
            if (!$goods['goods'] || $goods['goods']['goods_status']['value'] != 10) {
                $this->setError('很抱歉，商品 [' . $goods['goods_name'] . '] 已下架');
                return false;
            }
            // 付款减库存
            if ($goods['deduct_stock_type'] == 20 && $goods['sku']['stock_num'] < 1) {
                $this->setError('很抱歉，商品 [' . $goods['goods_name'] . '] 库存不足');
                return false;
            }
        }
        return true;
    }

    /**
     * 设置错误信息
     * @param $error
     */
    private function setError($error)
    {
        empty($this->error) && $this->error = $error;
    }

    /**
     * 是否存在错误
     * @return bool
     */
    public function hasError()
    {
        return !empty($this->error);
    }

    /**
     * 当前订单是否允许申请售后
     * @return bool
     */
    public function isAllowRefund()
    {
        // 允许申请售后期限
        $refund_days = Setting::getItem('trade')['order']['refund_days'];
        if ($refund_days == 0) {
            return false;
        }
        if (time() > $this['receipt_time'] + ((int)$refund_days * 86400)) {
            return false;
        }
        if ($this['receipt_status']['value'] != 20) {
            return false;
        }
        return true;
    }

}
