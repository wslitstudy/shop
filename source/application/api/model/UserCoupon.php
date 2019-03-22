<?php

namespace app\api\model;

use app\common\model\UserCoupon as UserCouponModel;
use think\Db;

/**
 * 用户优惠券模型
 * Class UserCoupon
 * @package app\api\model
 */
class UserCoupon extends UserCouponModel
{
    /**
     * 获取用户优惠券列表
     * @param $user_id
     * @param false $is_use
     * @param bool $is_expire
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList($user_id, $is_use = false, $is_expire = false)
    {
        return $this->where('user_id', '=', $user_id)
            ->where('is_use', '=', $is_use ? 1 : 0)
            ->where('is_expire', '=', $is_expire ? 1 : 0)
            ->select();
    }

    /**
     * 获取用户优惠券ID集
     * @param $user_id
     * @return array
     */
    public function getUserCouponIds($user_id)
    {
        return $this->where('user_id', '=', $user_id)->column('coupon_id');
    }

    /**
     * 领取优惠券
     * @param $user
     * @param $coupon_id
     * @return bool|false|int
     * @throws \think\exception\DbException
     */
    public function receive($user, $coupon_id)
    {
        // 获取优惠券信息
        $coupon = Coupon::detail($coupon_id);
        // 验证优惠券是否可领取
        if (!$this->checkReceive($user, $coupon)) {
            return false;
        }
        // 添加领取记录
        return $this->add($user, $coupon);
    }

    /**
     * 添加领取记录
     * @param $user
     * @param Coupon $coupon
     * @return bool
     */
    private function add($user, $coupon)
    {
        // 计算有效期
        if ($coupon['expire_type'] == 10) {
            $start_time = time();
            $end_time = $start_time + ($coupon['expire_day'] * 86400);
        } else {
            $start_time = $coupon['start_time']['value'];
            $end_time = $coupon['end_time']['value'];
        }
        Db::startTrans();
        try {
            // 添加领取记录
            $this->save([
                'coupon_id' => $coupon['coupon_id'],
                'name' => $coupon['name'],
                'color' => $coupon['color']['value'],
                'coupon_type' => $coupon['coupon_type']['value'],
                'reduce_price' => $coupon['reduce_price'],
                'discount' => $coupon->getData('discount'),
                'min_price' => $coupon['min_price'],
                'expire_type' => $coupon['expire_type'],
                'expire_day' => $coupon['expire_day'],
                'start_time' => $start_time,
                'end_time' => $end_time,
                'apply_range' => $coupon['apply_range'],
                'user_id' => $user['user_id'],
                'wxapp_id' => self::$wxapp_id
            ]);
            // 更新优惠券领取数量
            $coupon->setIncReceiveNum();
            Db::commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Db::rollback();
            return false;
        }
    }

    /**
     * 验证优惠券是否可领取
     * @param $user
     * @param Coupon $coupon
     * @return bool
     */
    private function checkReceive($user, $coupon)
    {
        if (!$coupon) {
            $this->error = '优惠券不存在';
            return false;
        }
        if (!$coupon->checkReceive()) {
            $this->error = $coupon->getError();
            return false;
        }
        // 验证是否已领取
        $userCouponIds = $this->getUserCouponIds($user['user_id']);
        if (in_array($coupon['coupon_id'], $userCouponIds)) {
            $this->error = '该优惠券已领取';
            return false;
        }
        return true;
    }

    /**
     * 订单结算优惠券列表
     * @param $user_id
     * @param $orderPayPrice
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getUserCouponList($user_id, $orderPayPrice)
    {
        $list = (new self)->getList($user_id);
        $data = [];
        foreach ($list as $coupon) {
            // 最低消费金额
            if ($orderPayPrice < $coupon['min_price']) continue;
            // 有效期范围内
            if ( $coupon['start_time']['value'] > time()) continue;
            $key = $coupon['user_coupon_id'];
            $data[$key] = [
                'user_coupon_id' => $coupon['user_coupon_id'],
                'name' => $coupon['name'],
                'color' => $coupon['color'],
                'coupon_type' => $coupon['coupon_type'],
                'reduce_price' => $coupon['reduce_price'],
                'discount' => $coupon['discount'],
                'min_price' => $coupon['min_price'],
                'expire_type' => $coupon['expire_type'],
                'start_time' => $coupon['start_time'],
                'end_time' => $coupon['end_time'],
            ];
            // 计算打折金额
            if ($coupon['coupon_type']['value'] == 20) {
                $reduce_price = $orderPayPrice * ($coupon['discount'] / 10);
                $data[$key]['reduced_price'] = bcsub($orderPayPrice, $reduce_price, 2);
            } else
                $data[$key]['reduced_price'] = $coupon['reduce_price'];
        }
        return array_sort($data, 'reduced_price', true);
    }

    /**
     * 设置优惠券为已使用
     * @return false|int
     */
    public function setIsUse()
    {
        return $this->save(['is_use' => 1]);
    }

}
