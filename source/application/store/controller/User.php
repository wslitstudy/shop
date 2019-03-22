<?php

namespace app\store\controller;

use app\store\model\User as UserModel;

/**
 * 用户管理
 * Class User
 * @package app\store\controller
 */
class User extends Controller
{
    /**
     * 用户列表
     * @param string $nickName
     * @param null $gender
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index($nickName = '', $gender = null)
    {
        $model = new UserModel;
        $list = $model->getList($nickName, $gender);
        return $this->fetch('index', compact('list'));
    }

    /**
     * 删除用户
     * @param $user_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($user_id)
    {
        // 商品详情
        $model = UserModel::detail($user_id);
        if (!$model->setDelete()) {
            return $this->renderError('删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

}
