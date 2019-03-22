<?php

namespace app\task\model;

use app\common\model\User as UserModel;

/**
 * 用户模型
 * Class User
 * @package app\task\model
 */
class User extends UserModel
{
    /**
     * 获取用户信息
     * @param $user_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($user_id)
    {
        return self::get($user_id);
    }

    /**
     * 累积用户总消费金额
     * @param $money
     * @return int|true
     * @throws \think\Exception
     */
    public function cumulateMoney($money)
    {
        return $this->setInc('money', $money);
    }

}
