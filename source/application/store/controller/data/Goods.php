<?php

namespace app\store\controller\data;

use app\store\controller\Controller;
use app\store\model\Goods as GoodsModel;

/**
 * 商品数据控制器
 * Class Goods
 * @package app\store\controller\data
 */
class Goods extends Controller
{
    /* @var \app\store\model\Goods $model */
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
        $this->model = new GoodsModel;
        $this->view->engine->layout(false);
    }

    /**
     * 商品列表
     * @param null $status
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function lists($status = null)
    {
        $list = $this->model->getList($status);
        return $this->fetch('list', compact('list'));
    }

}
