<?php

namespace app\store\controller\apps\sharing;

use app\store\controller\Controller;
use app\store\model\Goods as GoodsModel;
use app\store\model\sharing\Goods as SharingGoodsModel;

/**
 * 拼团商品管理
 * Class Goods
 * @package app\store\controller\apps\sharing
 */
class Goods extends Controller
{
    /**
     * 拼团商品列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new SharingGoodsModel;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 新增拼团商品
     * @param null $goods_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function add($goods_id = null)
    {
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        if ($goods_id <= 0) {
            return $this->renderError('请选择商品');
        }
        $model = new SharingGoodsModel;
        if ($model->add((int)$goods_id)) {
            $url = url('apps.sharing.goods/edit', [
                'sharing_goods_id' => $model['sharing_goods_id']
            ]);
            return $this->renderSuccess('新增成功，请补充拼团商品信息', $url);
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 商品编辑
     * @param $sharing_goods_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($sharing_goods_id)
    {
        // 商品详情
        $model = SharingGoodsModel::detail($sharing_goods_id);
        if (!$this->request->isAjax()) {
            // 商品规格信息
            $GoodsModel = new GoodsModel;
            $specData = $GoodsModel->getManySpecTable($model['goods']);
            return $this->fetch('edit', compact('model', 'specData'));
        }
        // 更新记录
        if ($model->edit($this->postData('goods'))) {
            return $this->renderSuccess('操作成功', url('apps.sharing.goods/index'));
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 修改商品状态
     * @param $sharing_goods_id
     * @param $state
     * @return array
     * @throws \think\exception\DbException
     */
    public function state($sharing_goods_id, $state)
    {
        // 商品详情
        $model = SharingGoodsModel::detail($sharing_goods_id);
        if (!$model->setStatus($state)) {
            return $this->renderError($model->getError() ?: '操作失败');
        }
        return $this->renderSuccess('操作成功');
    }

    /**
     * 删除商品
     * @param $sharing_goods_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($sharing_goods_id)
    {
        // 商品详情
        $model = SharingGoodsModel::detail($sharing_goods_id);
        if (!$model->setDelete()) {
            return $this->renderError('删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

}
