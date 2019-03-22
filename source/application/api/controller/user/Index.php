<?php

namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\model\Order as OrderModel;

/**
 * 个人中心主页
 * Class Index
 * @package app\api\controller\user
 */
class Index extends Controller
{
    /**
     * 获取当前用户信息
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function detail()
    {
        // 当前用户信息
        $userInfo = $this->getUser();
        // 订单总数
        $model = new OrderModel;
        return $this->renderSuccess([
            'userInfo' => $userInfo,
            'orderCount' => [
                'payment' => $model->getCount($userInfo['user_id'], 'payment'),
                'received' => $model->getCount($userInfo['user_id'], 'received'),
                'comment' => $model->getCount($userInfo['user_id'], 'comment'),
            ],
            'menus' => $userInfo->getMenus()   // 个人中心菜单列表
        ]);
    }

}
